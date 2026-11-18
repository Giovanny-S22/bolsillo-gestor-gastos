<?php
declare(strict_types=1);

class Resumen extends Model
{

    public function presupuestoActivo(int $idUsuario): ?array
    {
        $st = $this->db->prepare(
            'SELECT id, nombre, fecha_inicio, fecha_fin, limite
             FROM presupuestos
             WHERE id_usuario = ? AND activo = 1
             ORDER BY id DESC
             LIMIT 1'
        );
        $st->execute([$idUsuario]);

        return $st->fetch() ?: null;
    }

    /** Todos los gastos del presupuesto, del más reciente al más antiguo. */
    public function gastosDelPresupuesto(int $idUsuario, int $idPresupuesto): array
    {
        $st = $this->db->prepare(
            'SELECT g.id,
                    g.fecha,
                    g.monto,
                    c.nombre AS tipo_gasto,
                    g.descripcion
            FROM gastos g
            INNER JOIN categorias c ON c.id = g.id_categoria
            WHERE g.id_usuario = ?
              AND g.id_presupuesto = ?
            ORDER BY g.fecha DESC, g.id DESC'
        );
        $st->execute([$idUsuario, $idPresupuesto]);

        return $st->fetchAll();
    }
}