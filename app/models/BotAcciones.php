<?php
declare(strict_types=1);

/**
 * Ejecuta en Bolsillo lo que el usuario pidió por chat.
 *
 * Reutiliza los modelos Gasto, Presupuesto y Categoria (así se respetan las mismas
 * reglas que en la web) y SIEMPRE trabaja con el id del usuario de la sesión PHP:
 * ese id nunca viene del texto del usuario ni de Rasa.
 */
class BotAcciones extends Model
{
    public function ejecutar(int $idUsuario, array $datos): string
    {
        try {
            return match ($datos['accion'] ?? '') {
                'registrar_gasto'      => $this->registrarGasto($idUsuario, $datos),
                'crear_presupuesto'    => $this->crearPresupuesto($idUsuario, $datos),
                'consultar_gastos'     => $this->consultarGastos($idUsuario, $datos),
                'listar_presupuestos'  => $this->listarPresupuestos($idUsuario),
                'estado_presupuesto'   => $this->estadoPresupuesto($idUsuario, $datos),
                default                => 'No pude procesar esa acción.',
            };
        } catch (Throwable $e) {
            error_log('BotAcciones: ' . $e->getMessage());
            return 'Ocurrió un error al guardar o consultar tus datos. Intenta de nuevo en un momento.';
        }
    }

    /* ---------------- Gastos ---------------- */

    private function registrarGasto(int $idUsuario, array $d): string
    {
        $monto = BotParser::monto($this->dato($d, 'monto'));
        if ($monto === null) {
            return 'No pude entender el monto. Vuelve a empezar con un valor como 20000, 30.000 o 45 mil.';
        }

        $hoy = new DateTimeImmutable('today');
        $fecha = BotParser::fecha($this->dato($d, 'fecha'), $hoy, true);
        if ($fecha === null) {
            return 'No pude entender la fecha. Usa hoy, ayer o AAAA-MM-DD (por ejemplo 2026-11-19).';
        }
        if ($fecha > $hoy) {
            return 'No puedes registrar un gasto con una fecha futura.';
        }

        $categoria = $this->categoriaPorNombre($this->dato($d, 'categoria'), true);
        if ($categoria === null) {
            return 'Esa categoría no está disponible. Usa: comida, transporte, mercado, servicios, ocio, salud, educación u otros.';
        }

        $descripcion = mb_substr(trim($this->dato($d, 'descripcion') ?? ''), 0, 150);

        $gastos = new Gasto();
        $presupuesto = $gastos->presupuestoActivo($idUsuario);
        if (!$presupuesto) {
            return 'Para registrar un gasto necesitas un presupuesto activo. Crea uno diciéndome "quiero crear un presupuesto".';
        }

        // Misma regla de la web: si el gasto alcanza o supera el límite, requiere confirmación.
        $resumen = $gastos->resumenPresupuesto($idUsuario, (int) $presupuesto['id']);
        if ($resumen && (float) $resumen['limite'] > 0) {
            $despues = (float) $resumen['total_gastado'] + $monto;
            if ($despues >= (float) $resumen['limite']) {
                return 'No lo registré: con este gasto llegarías a ' . money($despues) . ' y el límite de "'
                    . $resumen['nombre'] . '" es ' . money($resumen['limite'])
                    . '. Regístralo desde la sección Gastos para confirmarlo.';
            }
        }

        $gastos->crear($idUsuario, (int) $presupuesto['id'], $fecha->format('Y-m-d'), (float) $monto,
            (int) $categoria['id'], $descripcion !== '' ? $descripcion : null);

        $msg = 'Listo, registré un gasto de ' . money($monto) . ' en ' . $categoria['nombre']
            . ' (' . $fecha->format('Y-m-d') . ') en el presupuesto "' . $presupuesto['nombre'] . '".';
        if ($fecha->format('Y-m-d') < $presupuesto['fecha_inicio'] || $fecha->format('Y-m-d') > $presupuesto['fecha_fin']) {
            $msg .= ' Ojo: esa fecha está fuera del rango del presupuesto, así que no cuenta en su total.';
        }
        return $msg;
    }

    private function consultarGastos(int $idUsuario, array $d): string
    {
        $hoy = new DateTimeImmutable('today');
        $rango = BotParser::periodo($this->dato($d, 'periodo'), $hoy);
        if ($rango === null) {
            return 'No entendí el periodo. Prueba con: hoy, esta semana, este mes, el mes pasado o un mes como noviembre.';
        }

        $textoCat = $this->dato($d, 'categoria');
        $idCategoria = null;
        $etiquetaCat = 'todas las categorías';
        if ($textoCat !== null && BotParser::normalizar($textoCat) !== 'todas') {
            $cat = $this->categoriaPorNombre($textoCat, false);
            if ($cat === null) {
                return 'No encontré esa categoría. Usa una de las categorías de Bolsillo o escribe "todas".';
            }
            $idCategoria = (int) $cat['id'];
            $etiquetaCat = $cat['nombre'];
        }

        $filtro = ' WHERE g.id_usuario = ? AND g.fecha BETWEEN ? AND ?' . ($idCategoria ? ' AND g.id_categoria = ?' : '');
        $params = [$idUsuario, $rango['desde'], $rango['hasta']];
        if ($idCategoria) {
            $params[] = $idCategoria;
        }

        $st = $this->db->prepare('SELECT COUNT(*) AS n, COALESCE(SUM(g.monto), 0) AS total FROM gastos g' . $filtro);
        $st->execute($params);
        $tot = $st->fetch();

        if ((int) $tot['n'] === 0) {
            return "No tienes gastos registrados en {$etiquetaCat} para {$rango['etiqueta']}.";
        }

        $st = $this->db->prepare(
            'SELECT g.fecha, g.monto, c.nombre AS categoria, g.descripcion
             FROM gastos g INNER JOIN categorias c ON c.id = g.id_categoria' . $filtro .
            ' ORDER BY g.fecha DESC, g.id DESC LIMIT 5'
        );
        $st->execute($params);

        $lineas = [];
        foreach ($st->fetchAll() as $g) {
            $lineas[] = '- ' . $g['fecha'] . ' · ' . money($g['monto']) . ' · ' . $g['categoria']
                . ($g['descripcion'] ? ' · ' . $g['descripcion'] : '');
        }

        return "Gastos en {$etiquetaCat} para {$rango['etiqueta']}: {$tot['n']} registro(s), total " . money($tot['total'])
            . ".\nÚltimos movimientos:\n" . implode("\n", $lineas);
    }

    /* ---------------- Presupuestos ---------------- */

    private function crearPresupuesto(int $idUsuario, array $d): string
    {
        $nombre = trim($this->dato($d, 'nombre_presupuesto') ?? '');
        if (mb_strlen($nombre) < 2 || mb_strlen($nombre) > 100) {
            return 'El nombre del presupuesto debe tener entre 2 y 100 caracteres.';
        }
        $limite = BotParser::monto($this->dato($d, 'limite'));
        if ($limite === null) {
            return 'No pude entender el límite. Usa un valor como 500000, 800 mil o 2 millones.';
        }

        $hoy = new DateTimeImmutable('today');
        $inicio = BotParser::fecha($this->dato($d, 'fecha_inicio'), $hoy, false);
        $fin = BotParser::fecha($this->dato($d, 'fecha_fin'), $hoy, false);
        if ($inicio === null || $fin === null) {
            return 'No pude entender las fechas. Usa el formato AAAA-MM-DD, por ejemplo 2026-12-01.';
        }
        if ($fin < $inicio) {
            return 'La fecha de fin no puede ser anterior a la fecha de inicio.';
        }

        $presupuestos = new Presupuesto();
        if ($presupuestos->existeNombre($nombre, $idUsuario)) {
            return 'Ya tienes un presupuesto con ese nombre. Elige otro.';
        }

        // Si todavía no tiene uno activo, este queda activo para poder registrar gastos.
        $activo = (new Gasto())->presupuestoActivo($idUsuario) ? 0 : 1;
        $presupuestos->crear($idUsuario, $nombre, $inicio->format('Y-m-d'), $fin->format('Y-m-d'), (float) $limite, $activo);

        return 'Listo, creé el presupuesto "' . $nombre . '" con límite de ' . money($limite) . ' del '
            . $inicio->format('Y-m-d') . ' al ' . $fin->format('Y-m-d') . '. '
            . ($activo ? 'Quedó como tu presupuesto activo.' : 'Quedó inactivo; puedes activarlo desde Gestionar presupuesto.');
    }

    private function listarPresupuestos(int $idUsuario): string
    {
        $lista = (new Presupuesto())->listar($idUsuario);
        if (!$lista) {
            return 'Todavía no tienes presupuestos. Puedes crear uno diciéndome "quiero crear un presupuesto".';
        }
        $lineas = [];
        foreach (array_slice($lista, 0, 10) as $p) {
            $lineas[] = '- ' . $p['nombre'] . ': límite ' . money($p['limite']) . ', del ' . $p['fecha_inicio']
                . ' al ' . $p['fecha_fin'] . ($p['activo'] ? ' (activo)' : '');
        }
        return "Tus presupuestos:\n" . implode("\n", $lineas);
    }

    private function estadoPresupuesto(int $idUsuario, array $d): string
    {
        $nombre = $this->dato($d, 'nombre_presupuesto');
        $elegido = null;

        if ($nombre !== null) {
            foreach ((new Presupuesto())->listar($idUsuario) as $p) {
                if (BotParser::normalizar($p['nombre']) === BotParser::normalizar($nombre)) {
                    $elegido = $p;
                    break;
                }
            }
            if ($elegido === null) {
                return 'No encontré un presupuesto llamado "' . $nombre . '". Pídeme "mis presupuestos" para ver la lista.';
            }
        } else {
            $elegido = (new Gasto())->presupuestoActivo($idUsuario);
            if ($elegido === null) {
                return 'No tienes un presupuesto activo. Crea uno o actívalo desde Gestionar presupuesto.';
            }
        }

        $r = (new Gasto())->resumenPresupuesto($idUsuario, (int) $elegido['id']);
        if (!$r) {
            return 'No pude calcular el estado de ese presupuesto.';
        }

        $gastado = (float) $r['total_gastado'];
        $limite = (float) $r['limite'];
        $restante = $limite - $gastado;
        $pct = $limite > 0 ? round($gastado / $limite * 100) : 0;

        $msg = 'Presupuesto "' . $r['nombre'] . '": llevas ' . money($gastado) . ' de ' . money($limite) . " ({$pct}%). ";
        $msg .= $restante >= 0 ? 'Te quedan ' . money($restante) . '.' : 'Superaste el límite por ' . money(-$restante) . '.';

        $hoy = new DateTimeImmutable('today');
        $fin = new DateTimeImmutable($r['fecha_fin']);
        if ($restante > 0 && $hoy <= $fin) {
            $dias = (int) $hoy->diff($fin)->days + 1;
            $msg .= " Quedan {$dias} día(s); puedes gastar unos " . money($restante / $dias) . ' por día.';
        }
        return $msg;
    }

    /* ---------------- Auxiliares ---------------- */

    /** Valor de Rasa: vacío, "None" o "null" significan que ese dato no llegó. */
    private function dato(array $d, string $clave): ?string
    {
        $v = isset($d[$clave]) ? trim((string) $d[$clave]) : '';
        return ($v === '' || in_array(strtolower($v), ['none', 'null'], true)) ? null : $v;
    }

    /** Busca la categoría sin importar mayúsculas ni tildes. Devuelve ['id', 'nombre'] o null. */
    private function categoriaPorNombre(?string $texto, bool $soloActivas): ?array
    {
        if ($texto === null) {
            return null;
        }
        $sql = 'SELECT id, nombre FROM categorias' . ($soloActivas ? ' WHERE activo = 1' : '');
        $mapa = [];
        foreach ($this->db->query($sql)->fetchAll() as $c) {
            $mapa[BotParser::normalizar($c['nombre'])] = $c;
        }

        $n = BotParser::normalizar($texto);
        if (isset($mapa[$n])) {
            return $mapa[$n];
        }
        // Frases como "en comida": acepta si aparece exactamente una categoría
        $halladas = [];
        foreach (preg_split('/[^a-z]+/', $n, -1, PREG_SPLIT_NO_EMPTY) as $palabra) {
            if (isset($mapa[$palabra])) {
                $halladas[$palabra] = $mapa[$palabra];
            }
        }
        return count($halladas) === 1 ? array_values($halladas)[0] : null;
    }
}
