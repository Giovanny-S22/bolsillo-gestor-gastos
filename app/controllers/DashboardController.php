<?php
declare(strict_types=1);

class DashboardController extends Controller
{
    public function index(): void
    {
        $idUsuario = $this->requireAuth();
        $resumen   = new Resumen();

        $hoy       = date('Y-m-d');
        $inicioMes = date('Y-m-01');
        $finMes    = date('Y-m-t');

        $totalMes       = $resumen->totalEntre($idUsuario, $inicioMes, $finMes);
        $totalHoy       = $resumen->totalEntre($idUsuario, $hoy, $hoy);
        $promedioDiario = $totalMes / (int) date('j');

        $porTipo  = $resumen->porTipo($idUsuario, $inicioMes, $finMes);
        $ultimos  = $resumen->ultimos($idUsuario, 6);

        // Últimos 7 días (incluye los días sin gastos con total 0)
        $totalesDia = $resumen->totalesPorDia($idUsuario, date('Y-m-d', strtotime('-6 days')), $hoy);
        $dias = [];
        for ($i = 6; $i >= 0; $i--) {
            $fecha  = date('Y-m-d', strtotime("-$i days"));
            $dias[] = [
                'fecha'    => $fecha,
                'etiqueta' => nombre_dia_corto($fecha),
                'total'    => (float) ($totalesDia[$fecha] ?? 0),
                'hoy'      => $fecha === $hoy,
            ];
        }

        $presupuesto = $this->calcularPresupuesto($resumen->presupuestoActivo($idUsuario, $hoy), $hoy);

        $this->view('dashboard/index', [
            'nombre'         => explode(' ', (string) $_SESSION['usuario_nombre'])[0],
            'hoy'            => $hoy,
            'totalMes'       => $totalMes,
            'totalHoy'       => $totalHoy,
            'promedioDiario' => $promedioDiario,
            'porTipo'        => $porTipo,
            'ultimos'        => $ultimos,
            'dias'           => $dias,
            'presupuesto'    => $presupuesto,
        ]);
    }

    /** Agrega al presupuesto los cálculos que muestra el dashboard. */
    private function calcularPresupuesto(?array $p, string $hoy): ?array
    {
        if (!$p) {
            return null;
        }

        $limite    = (float) $p['limite'];
        $gastado   = (float) $p['gastado'];
        $restante  = $limite - $gastado;
        $porcentaje = $limite > 0 ? (int) round($gastado / $limite * 100) : 0;

        $diferencia     = (new DateTime($hoy))->diff(new DateTime($p['fecha_fin']));
        $diasRestantes  = max(1, $diferencia->days + 1); // cuenta hoy

        return $p + [
            'limite_num'     => $limite,
            'gastado_num'    => $gastado,
            'restante'       => $restante,
            'porcentaje'     => $porcentaje,
            'dias_restantes' => $diasRestantes,
            'por_dia'        => $restante > 0 ? $restante / $diasRestantes : 0,
            'estado'         => $porcentaje >= 100 ? 'excedido' : ($porcentaje >= 80 ? 'alerta' : 'normal'),
        ];
    }
}
