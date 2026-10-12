<?php

class Presupuesto extends Model {

    //lista presupuestos del usuario
    public function listar(int $idUsuario): array {
        $st = $this->db->prepare('SELECT * FROM presupuestos WHERE id_usuario = ? ORDER BY creado_en DESC');
        $st->execute([$idUsuario]);
        return $st->fetchAll();
    }

    
    public function buscar(int $id, int $idUsuario): ?array {
        $st = $this->db->prepare('SELECT * FROM presupuestos WHERE id = ? AND id_usuario = ?');
        $st->execute([$id, $idUsuario]);
        $res = $st->fetch();
        return $res ?: null;
    }

    
    //crea nuevo presupuesto, si está activo es 1
    public function crear(int $idUsuario, string $nombre, string $fechaInicio, string $fechaFin, float $limite, int $activo): bool {
        if ($activo === 1) {
            $this->desactivarTodos($idUsuario);
        }

        $st = $this->db->prepare(
            'INSERT INTO presupuestos (id_usuario, nombre, fecha_inicio, fecha_fin, limite, activo) VALUES (?, ?, ?, ?, ?, ?)'
        );
        return $st->execute([$idUsuario, $nombre, $fechaInicio, $fechaFin, $limite, $activo]);
    }

    //esta función activa solo un presupuesto
    public function activar(int $id, int $idUsuario): bool {
        $this->desactivarTodos($idUsuario);

        $st = $this->db->prepare('UPDATE presupuestos SET activo = 1 WHERE id = ? AND id_usuario = ?');
        return $st->execute([$id, $idUsuario]);
    }

    
    public function actualizar(int $id, int $idUsuario, string $nombre, string $fechaInicio, string $fechaFin, float $limite, int $activo): bool {
        if ($activo === 1) {
            $this->desactivarTodos($idUsuario);
        }

        $st = $this->db->prepare(
            'UPDATE presupuestos SET nombre = ?, fecha_inicio = ?, fecha_fin = ?, limite = ?, activo = ? WHERE id = ? AND id_usuario = ?'
        );
        return $st->execute([$nombre, $fechaInicio, $fechaFin, $limite, $activo, $id, $idUsuario]);
    }

    // Elimina un presupuesto
    public function eliminar(int $id, int $idUsuario): bool {
        $st = $this->db->prepare('DELETE FROM presupuestos WHERE id = ? AND id_usuario = ?');
        return $st->execute([$id, $idUsuario]);
    }

    // Método auxiliar para poner en 0 el campo activo de todos los presupuestos de un usuario
     
    private function desactivarTodos(int $idUsuario): void {
        $st = $this->db->prepare('UPDATE presupuestos SET activo = 0 WHERE id_usuario = ?');
        $st->execute([$idUsuario]);
    }

//Función para comprobar si existen dos presupuestos con nombre igual para evitar duplicados
    public function existeNombre(string $nombre, int $idUsuario, int $excluirId = 0): bool {
        $st = $this->db->prepare('SELECT COUNT(*) FROM presupuestos WHERE id_usuario = ? AND nombre = ? AND id != ?');
        $st->execute([$idUsuario, $nombre, $excluirId]);
        return $st->fetchColumn() > 0;
    }
}