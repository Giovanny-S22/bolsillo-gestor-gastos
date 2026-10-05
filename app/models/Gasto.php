<?php
declare(strict_types=1);

class Gasto extends Model
{
    public function listar(int $idUsuario): array
    {
        $st = $this->db->prepare(
            'SELECT g.*, p.nombre AS presupuesto_nombre
             FROM gastos g
             LEFT JOIN presupuestos p
                ON p.id = g.id_presupuesto
                AND p.id_usuario = g.id_usuario
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
        string $tipoGasto,
        ?string $descripcion
    ): void {
        $st = $this->db->prepare(
            'INSERT INTO gastos
                (id_usuario, id_presupuesto, fecha, monto, tipo_gasto, descripcion)
             VALUES (?, ?, ?, ?, ?, ?)'
        );

        $st->execute([
            $idUsuario,
            $idPresupuesto,
            $fecha,
            $monto,
            $tipoGasto,
            $descripcion
        ]);
    }

    public function presupuestos(int $idUsuario): array
    {
        $st = $this->db->prepare(
            'SELECT id, nombre, fecha_inicio, fecha_fin, limite
             FROM presupuestos
             WHERE id_usuario = ?
             ORDER BY fecha_inicio DESC'
        );

        $st->execute([$idUsuario]);

        return $st->fetchAll();
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

        $st->execute([
            $idPresupuesto,
            $idUsuario
        ]);

        return (bool) $st->fetch();
    }

    public function buscarPorId(int $id, int $idUsuario): ?array{
        $st = $this->db->prepare(
            'SELECT g.*, p.nombre AS presupuesto_nombre
            FROM gastos g
            LEFT JOIN presupuestos p
                ON p.id = g.id_presupuesto
                AND p.id_usuario = g.id_usuario
            WHERE g.id = ?
            AND g.id_usuario = ?
            LIMIT 1'
        );

        $st->execute([
            $id,
            $idUsuario
        ]);

        $gasto = $st->fetch();

        return $gasto ?: null;
    }

    public function actualizar(
        int $id,
        int $idUsuario,
        int $idPresupuesto,
        string $fecha,
        float $monto,
        string $tipoGasto,
        ?string $descripcion
    ): void {
        $st = $this->db->prepare(
            'UPDATE gastos
            SET id_presupuesto = ?,
                fecha = ?,
                monto = ?,
                tipo_gasto = ?,
                descripcion = ?
            WHERE id = ?
            AND id_usuario = ?'
        );

        $st->execute([
            $idPresupuesto,
            $fecha,
            $monto,
            $tipoGasto,
            $descripcion,
            $id,
            $idUsuario
        ]);
    }

    public function eliminar(int $id, int $idUsuario): void{
        $st = $this->db->prepare(
            'DELETE FROM gastos
            WHERE id = ?
            AND id_usuario = ?'
        );

        $st->execute([
            $id,
            $idUsuario
        ]);
    }


}