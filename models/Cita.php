<?php
 
    require_once __DIR__ . '/../config/config.php';

    
    class Cita {

        private $db;

    public function __construct() {

        $this->db = Database;
    }


    public function consultaCita() {

        $this->db->query("SELECT * FROM citas");
    }

    public function consultaPaciente() {

        $db = "SELECT * FROM `pacientes`";
        return $this->conexion->consultaTabla($sql);
    }
}