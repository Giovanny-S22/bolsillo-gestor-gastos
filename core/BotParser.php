<?php
declare(strict_types=1);

/**
 * Convierte el texto "tal cual" que guarda Rasa (30mil, 2 millones, ayer, este mes...)
 * en valores que MySQL entiende. Son funciones puras: no tocan la base de datos.
 */
final class BotParser
{
    private const MULT = ['mil' => 1000, 'k' => 1000, 'millon' => 1000000, 'millones' => 1000000];

    private const MESES = [
        'enero' => 1, 'febrero' => 2, 'marzo' => 3, 'abril' => 4, 'mayo' => 5, 'junio' => 6,
        'julio' => 7, 'agosto' => 8, 'septiembre' => 9, 'setiembre' => 9, 'octubre' => 10,
        'noviembre' => 11, 'diciembre' => 12,
    ];

    private const DIAS = ['lunes' => 0, 'martes' => 1, 'miercoles' => 2, 'jueves' => 3,
                          'viernes' => 4, 'sabado' => 5, 'domingo' => 6];

    /** Minúsculas, sin tildes y sin espacios repetidos. */
    public static function normalizar(string $t): string
    {
        $t = mb_strtolower(trim($t), 'UTF-8');
        $t = strtr($t, ['á' => 'a', 'é' => 'e', 'í' => 'i', 'ó' => 'o', 'ú' => 'u', 'ü' => 'u', 'ñ' => 'n']);
        return (string) preg_replace('/\s+/u', ' ', $t);
    }

    /** '30000', '30.000', '45mil', '2 millones', '1,5 millones' => pesos enteros. null si no se entiende. */
    public static function monto(?string $texto): ?int
    {
        if ($texto === null) {
            return null;
        }
        $n = str_replace('$', ' ', self::normalizar($texto));
        $n = trim((string) preg_replace('/\s+/', ' ', (string) preg_replace('/\b(pesos|peso|cop)\b/', ' ', $n)));

        $valor = null;
        if (preg_match('/^(\d+(?:[.,]\d+)?)\s*(mil|k|millon|millones)$/', $n, $m)) {
            $valor = (int) round((float) str_replace(',', '.', $m[1]) * self::MULT[$m[2]]);
        } elseif (preg_match('/^\d{1,3}(?:[.,]\d{3})+$/', $n)) {
            $valor = (int) preg_replace('/[.,]/', '', $n);
        } elseif (preg_match('/^\d+$/', $n)) {
            $valor = (int) $n;
        }

        // DECIMAL(12,2) admite hasta 10 dígitos enteros
        return ($valor !== null && $valor > 0 && $valor < 10000000000) ? $valor : null;
    }

    /** 'hoy', 'ayer', '3 de noviembre', '2026-12-01', '30/11/2026', 'viernes pasado'. */
    public static function fecha(?string $texto, DateTimeImmutable $hoy, bool $preferirPasado = true): ?DateTimeImmutable
    {
        if ($texto === null) {
            return null;
        }
        $n = (string) preg_replace('/^(el|del|para el|desde el|hasta el|al)\s+/', '', self::normalizar($texto));

        if ($n === 'hoy') {
            return $hoy;
        }
        if ($n === 'ayer') {
            return $hoy->modify('-1 day');
        }
        if (in_array($n, ['antier', 'anteayer', 'antes de ayer'], true)) {
            return $hoy->modify('-2 days');
        }
        if ($n === 'manana') {
            return $hoy->modify('+1 day');
        }
        if (preg_match('/^(\d{4})-(\d{1,2})-(\d{1,2})$/', $n, $m)) {
            return self::crear((int) $m[1], (int) $m[2], (int) $m[3]);
        }
        if (preg_match('/^(\d{1,2})[\/-](\d{1,2})[\/-](\d{4})$/', $n, $m)) { // día/mes/año
            return self::crear((int) $m[3], (int) $m[2], (int) $m[1]);
        }
        if (preg_match('/^(\d{1,2})\s+(?:de\s+)?([a-z]+)(?:\s+(?:de\s+)?(\d{4}))?$/', $n, $m) && isset(self::MESES[$m[2]])) {
            $dia = (int) $m[1];
            $mes = self::MESES[$m[2]];
            if (!empty($m[3])) {
                return self::crear((int) $m[3], $mes, $dia);
            }
            $f = self::crear((int) $hoy->format('Y'), $mes, $dia);
            if ($f !== null && $preferirPasado && $f > $hoy) {
                $f = self::crear((int) $hoy->format('Y') - 1, $mes, $dia);
            }
            return $f;
        }
        if ($preferirPasado && preg_match('/^([a-z]+)( pasado)?$/', $n, $m) && isset(self::DIAS[$m[1]])) {
            $delta = ((((int) $hoy->format('N')) - 1 - self::DIAS[$m[1]]) % 7 + 7) % 7;
            if ($delta === 0 && !empty($m[2])) {
                $delta = 7;
            }
            return $hoy->modify("-{$delta} days");
        }
        return null;
    }

    private static function crear(int $y, int $m, int $d): ?DateTimeImmutable
    {
        if (!checkdate($m, $d, $y)) {
            return null;
        }
        return new DateTimeImmutable(sprintf('%04d-%02d-%02d', $y, $m, $d));
    }

    /**
     * Periodo de consulta => ['desde' => 'Y-m-d', 'hasta' => 'Y-m-d', 'etiqueta' => '...'] o null.
     * Acepta: hoy, ayer, esta semana, la semana pasada, este mes, el mes pasado,
     * este año, el año pasado, un mes ("noviembre") y "últimos N días".
     */
    public static function periodo(?string $texto, DateTimeImmutable $hoy): ?array
    {
        if ($texto === null) {
            return null;
        }
        $n = (string) preg_replace('/^(en|de|durante|del)\s+/', '', self::normalizar($texto));
        $f = 'Y-m-d';
        $y = (int) $hoy->format('Y');
        $lunes = $hoy->modify('-' . ((int) $hoy->format('N') - 1) . ' days');

        $r = match ($n) {
            'hoy' => [$hoy, $hoy, 'hoy'],
            'ayer' => [$hoy->modify('-1 day'), $hoy->modify('-1 day'), 'ayer'],
            'esta semana', 'semana actual' => [$lunes, $lunes->modify('+6 days'), 'esta semana'],
            'la semana pasada', 'semana pasada' => [$lunes->modify('-7 days'), $lunes->modify('-1 day'), 'la semana pasada'],
            'este mes', 'mes actual' => [$hoy->modify('first day of this month'), $hoy->modify('last day of this month'), 'este mes'],
            'el mes pasado', 'mes pasado' => [$hoy->modify('first day of last month'), $hoy->modify('last day of last month'), 'el mes pasado'],
            'este ano' => [self::crear($y, 1, 1), self::crear($y, 12, 31), 'este año'],
            'el ano pasado', 'ano pasado' => [self::crear($y - 1, 1, 1), self::crear($y - 1, 12, 31), 'el año pasado'],
            default => null,
        };
        if ($r !== null) {
            return ['desde' => $r[0]->format($f), 'hasta' => $r[1]->format($f), 'etiqueta' => $r[2]];
        }
        if (preg_match('/^(?:el mes de |mes de |el mes )?([a-z]+)$/', $n, $m) && isset(self::MESES[$m[1]])) {
            $mes = self::MESES[$m[1]];
            $ini = self::crear($y, $mes, 1);
            return ['desde' => $ini->format($f), 'hasta' => $ini->modify('last day of this month')->format($f), 'etiqueta' => $m[1]];
        }
        if (preg_match('/^(?:en )?(?:los )?ultimos (\d{1,3}) dias$/', $n, $m) && (int) $m[1] > 0) {
            $dias = (int) $m[1];
            return ['desde' => $hoy->modify('-' . ($dias - 1) . ' days')->format($f), 'hasta' => $hoy->format($f),
                    'etiqueta' => "los últimos {$dias} días"];
        }
        return null;
    }
}
