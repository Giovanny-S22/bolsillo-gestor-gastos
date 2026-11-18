<?php
declare(strict_types=1);

class Categoria extends Model
{
    // Categorías disponibles para registrar nuevos gastos.
    public function listarActivas(): array
    {
        $st = $this->db->prepare(
            'SELECT id, nombre
             FROM categorias
             WHERE activo = 1
             ORDER BY id ASC'
        );

        $st->execute();

        return $st->fetchAll();
    }

    // Comprueba que una categoría esté activa.
    public function estaActiva(int $idCategoria): bool
    {
        $st = $this->db->prepare(
            'SELECT id
             FROM categorias
             WHERE id = ?
               AND activo = 1
             LIMIT 1'
        );

        $st->execute([$idCategoria]);

        return (bool) $st->fetch();
    }
}
