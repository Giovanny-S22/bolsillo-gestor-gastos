<?php
declare(strict_types=1);

/** Clase base de los modelos: ya trae la conexión en $this->db */
abstract class Model
{
    protected PDO $db;

    public function __construct()
    {
        $this->db = Database::connect();
    }
}
