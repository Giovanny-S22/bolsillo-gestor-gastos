<?php
declare(strict_types=1);

class DashboardController extends Controller
{
    public function index(): void
    {
        $idUsuario = $this->requireAuth();
        $resumen   = new Resumen();
        $hoy       = date('Y-m-d');
        $nombre    = explode(' ', (string) $_SESSION['usuario_nombre'])[0];

        $presupuestoBd = $resumen->presupuestoActivo($idUsuario);

        if (!$presupuestoBd) {
            $this->view('dashboard/index', ['nombre' => $nombre, 'presupuesto' => null]);
            return;
        }

        // Una sola consulta: todos los gastos del presupuesto activo
        $gastos = $resumen->gastosDelPresupuesto($idUsuario, (int) $presupuestoBd['id']);

        // Un solo recorrido: aquí se hacen las sumas y conteos
        $gastado   = 0.0;
        $numGastos = 0;
        $porTipo   = [];   
        $porDia    = [];   

        foreach ($gastos as $g) {
            $monto = (float) $g['monto'];

            $gastado += $monto;
            $numGastos++;

            $porTipo[$g['tipo_gasto']] = ($porTipo[$g['tipo_gasto']] ?? 0.0) + $monto;
            $porDia[$g['fecha']]       = ($porDia[$g['fecha']] ?? 0.0) + $monto;
        }

        arsort($porTipo); // de mayor a menor

        // Últimos 7 días (incluye los días sin gastos con total 0)
        $dias    = [];
        $hayDias = false;
        for ($i = 6; $i >= 0; $i--) {
            $fecha = date('Y-m-d', strtotime("-$i days"));
            $total = $porDia[$fecha] ?? 0.0;
            if ($total > 0) {
                $hayDias = true;
            }
            $dias[] = [
                'fecha'    => $fecha,
                'etiqueta' => nombre_dia_corto($fecha),
                'total'    => $total,
                'hoy'      => $fecha === $hoy,
            ];
        }

        $this->view('dashboard/index', [
            'nombre'      => $nombre,
            'hoy'         => $hoy,
            'totalHoy'    => $porDia[$hoy] ?? 0.0,
            'porTipo'     => $porTipo,
            'ultimos'     => array_slice($gastos, 0, 6), // ya vienen ordenados del más reciente
            'dias'        => $dias,
            'hayDias'     => $hayDias,
            'presupuesto' => $this->calcularPresupuesto($presupuestoBd, $gastado, $numGastos, $hoy),
        ]);
    }

    private function calcularPresupuesto(array $p, float $gastado, int $numGastos, string $hoy): array
    {
        $limite     = (float) $p['limite'];
        $restante   = $limite - $gastado;
        $porcentaje = $limite > 0 ? (int) round($gastado / $limite * 100) : 0;

        $inicio = $p['fecha_inicio'];
        $fin    = $p['fecha_fin'];

        // Estado del plazo y días (los 'Y-m-d' se pueden comparar como texto)
        if ($hoy > $fin) {
            $plazo         = 'vencido';
            $diasRestantes = 0;
            $transcurridos = $this->diasEntre($inicio, $fin);
        } elseif ($hoy < $inicio) {
            $plazo         = 'proximo';
            $diasRestantes = $this->diasEntre($inicio, $fin);
            $transcurridos = 0;
        } else {
            $plazo         = 'vigente';
            $diasRestantes = $this->diasEntre($hoy, $fin);     
            $transcurridos = $this->diasEntre($inicio, $hoy);  
        }

        return $p + [
            'limite_num'     => $limite,
            'gastado_num'    => $gastado,
            'restante'       => $restante,
            'porcentaje'     => $porcentaje,
            'plazo'          => $plazo,
            'dias_restantes' => $diasRestantes,
            'num_gastos'     => $numGastos,
            'promedio'       => $transcurridos > 0 ? $gastado / $transcurridos : 0,
            'por_dia'        => ($restante > 0 && $diasRestantes > 0) ? $restante / $diasRestantes : 0,
            'estado'         => $porcentaje >= 100 ? 'excedido' : ($porcentaje >= 80 ? 'alerta' : 'normal'),
        ];
    }

    /** Cantidad de días entre dos fechas, contando ambos extremos. Requiere $desde <= $hasta. */
    private function diasEntre(string $desde, string $hasta): int
    {
        return (new DateTimeImmutable($desde))->diff(new DateTimeImmutable($hasta))->days + 1;
    }
}