<?php
declare(strict_types=1);

class Gasto extends Model
{
    public function listar(int $idUsuario): array
    {
        $st = $this->db->prepare(
            'SELECT g.*,
                    p.nombre AS presupuesto_nombre,
                    c.nombre AS categoria_nombre
             FROM gastos g
             LEFT JOIN presupuestos p
                ON p.id = g.id_presupuesto
                AND p.id_usuario = g.id_usuario
             INNER JOIN categorias c
                ON c.id = g.id_categoria
             WHERE g.id_usuario = ?
             ORDER BY g.fecha DESC, g.id DESC'
        );

        $st->execute([$idUsuario]);

        return $st->fetchAll();
    }

    public function crear(
        int $idUsuario,
        int $idPresupuesto,
        string $fecha,
        float $monto,
        int $idCategoria,
        ?string $descripcion
    ): void {
        $st = $this->db->prepare(
            'INSERT INTO gastos
                (id_usuario, id_presupuesto, fecha, monto, id_categoria, descripcion)
             VALUES (?, ?, ?, ?, ?, ?)'
        );

        $st->execute([
            $idUsuario,
            $idPresupuesto,
            $fecha,
            $monto,
            $idCategoria,
            $descripcion
        ]);
    }

    public function presupuestos(int $idUsuario): array
    {
        $st = $this->db->prepare(
            'SELECT id, nombre, fecha_inicio, fecha_fin, limite, activo
             FROM presupuestos
             WHERE id_usuario = ?
             ORDER BY fecha_inicio DESC'
        );

        $st->execute([$idUsuario]);

        return $st->fetchAll();
    }

    public function presupuestoActivo(int $idUsuario): ?array
    {
        $st = $this->db->prepare(
            'SELECT id, nombre, fecha_inicio, fecha_fin, limite, activo
             FROM presupuestos
             WHERE id_usuario = ?
               AND activo = 1
             ORDER BY id DESC
             LIMIT 1'
        );

        $st->execute([$idUsuario]);

        return $st->fetch() ?: null;
    }

    public function resumenPresupuesto(
        int $idUsuario,
        int $idPresupuesto
    ): ?array {
        $st = $this->db->prepare(
            'SELECT
                p.id,
                p.nombre,
                p.limite,
                p.fecha_inicio,
                p.fecha_fin,
                COALESCE(SUM(g.monto), 0) AS total_gastado
             FROM presupuestos p
             LEFT JOIN gastos g
                ON g.id_presupuesto = p.id
                AND g.id_usuario = p.id_usuario
                AND g.fecha BETWEEN p.fecha_inicio AND p.fecha_fin
             WHERE p.id = ?
               AND p.id_usuario = ?
             GROUP BY
                p.id,
                p.nombre,
                p.limite,
                p.fecha_inicio,
                p.fecha_fin
             LIMIT 1'
        );

        $st->execute([$idPresupuesto, $idUsuario]);

        return $st->fetch() ?: null;
    }


    public function presupuestoPerteneceUsuario(
        int $idPresupuesto,
        int $idUsuario
    ): bool {
        $st = $this->db->prepare(
            'SELECT id
             FROM presupuestos
             WHERE id = ?
               AND id_usuario = ?
             LIMIT 1'
        );

        $st->execute([$idPresupuesto, $idUsuario]);

        return (bool) $st->fetch();
    }

    public function buscarPorId(int $id, int $idUsuario): ?array
    {
        $st = $this->db->prepare(
            'SELECT g.*,
                    p.nombre AS presupuesto_nombre,
                    c.nombre AS categoria_nombre,
                    c.activo AS categoria_activa
             FROM gastos g
             LEFT JOIN presupuestos p
                ON p.id = g.id_presupuesto
                AND p.id_usuario = g.id_usuario
             INNER JOIN categorias c
                ON c.id = g.id_categoria
             WHERE g.id = ?
               AND g.id_usuario = ?
             LIMIT 1'
        );

        $st->execute([$id, $idUsuario]);

        $gasto = $st->fetch();

        return $gasto ?: null;
    }

    public function actualizar(
        int $id,
        int $idUsuario,
        int $idPresupuesto,
        string $fecha,
        float $monto,
        int $idCategoria,
        ?string $descripcion
    ): void {
        $st = $this->db->prepare(
            'UPDATE gastos
             SET id_presupuesto = ?,
                 fecha = ?,
                 monto = ?,
                 id_categoria = ?,
                 descripcion = ?
             WHERE id = ?
               AND id_usuario = ?'
        );

        $st->execute([
            $idPresupuesto,
            $fecha,
            $monto,
            $idCategoria,
            $descripcion,
            $id,
            $idUsuario
        ]);
    }

    public function eliminar(int $id, int $idUsuario): void
    {
        $st = $this->db->prepare(
            'DELETE FROM gastos
             WHERE id = ?
               AND id_usuario = ?'
        );

        $st->execute([$id, $idUsuario]);
    }
}