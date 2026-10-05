<?php
declare(strict_types=1);

class Resumen extends Model
{
    /** Suma de gastos del usuario entre dos fechas (inclusive). */
    public function totalEntre(int $idUsuario, string $desde, string $hasta): float
    {
        $st = $this->db->prepare(
            'SELECT COALESCE(SUM(monto), 0) FROM gastos
             WHERE id_usuario = ? AND fecha BETWEEN ? AND ?'
        );
        $st->execute([$idUsuario, $desde, $hasta]);

        return (float) $st->fetchColumn();
    }

    /** Total por tipo de gasto, de mayor a menor. */
    public function porTipo(int $idUsuario, string $desde, string $hasta): array
    {
        $st = $this->db->prepare(
            'SELECT tipo_gasto, SUM(monto) AS total FROM gastos
             WHERE id_usuario = ? AND fecha BETWEEN ? AND ?
             GROUP BY tipo_gasto ORDER BY total DESC'
        );
        $st->execute([$idUsuario, $desde, $hasta]);

        return $st->fetchAll();
    }

    /** Total por día: devuelve ['2026-10-01' => 18000.00, ...] (solo días con gastos). */
    public function totalesPorDia(int $idUsuario, string $desde, string $hasta): array
    {
        $st = $this->db->prepare(
            'SELECT fecha, SUM(monto) FROM gastos
             WHERE id_usuario = ? AND fecha BETWEEN ? AND ?
             GROUP BY fecha'
        );
        $st->execute([$idUsuario, $desde, $hasta]);

        return $st->fetchAll(PDO::FETCH_KEY_PAIR);
    }

    public function ultimos(int $idUsuario, int $limite = 6): array
    {
        $st = $this->db->prepare(
            'SELECT id, fecha, monto, tipo_gasto, descripcion FROM gastos
             WHERE id_usuario = ?
             ORDER BY fecha DESC, id DESC
             LIMIT ' . (int) $limite
        );
        $st->execute([$idUsuario]);

        return $st->fetchAll();
    }

    /**
     * Presupuesto vigente hoy (el que termina primero si hay varios),
     * con lo gastado en los gastos ligados a él (gastos.id_presupuesto).
     */
    public function presupuestoActivo(int $idUsuario, string $hoy): ?array
    {
        $st = $this->db->prepare(
            'SELECT p.id, p.nombre, p.fecha_inicio, p.fecha_fin, p.limite,
                    COALESCE(SUM(g.monto), 0) AS gastado
             FROM presupuestos p
             LEFT JOIN gastos g ON g.id_presupuesto = p.id
             WHERE p.id_usuario = ? AND ? BETWEEN p.fecha_inicio AND p.fecha_fin
             GROUP BY p.id, p.nombre, p.fecha_inicio, p.fecha_fin, p.limite
             ORDER BY p.fecha_fin ASC
             LIMIT 1'
        );
        $st->execute([$idUsuario, $hoy]);

        return $st->fetch() ?: null;
    }
}
