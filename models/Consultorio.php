<?php
class Consultorio {
    private $db;

    public function __construct() {
        $this->db = new Database;
    }

    // Obtener todos los consultorios
    public function getConsultorios() {
        $this->db->query("SELECT * FROM consultorios ORDER BY numero");
        return $this->db->resultSet();
    }

    // Obtener solo consultorios activos (estado = 1)
    public function getConsultoriosActivos() {
        $this->db->query("SELECT * FROM consultorios WHERE estado = 1 ORDER BY numero");
        return $this->db->resultSet();
    }

    // Obtener un consultorio por su ID
    public function getConsultorioById($id) {
        $this->db->query("SELECT * FROM consultorios WHERE id_consultorio = :id");
        $this->db->bind(':id', $id);
        return $this->db->single();
    }

    // Crear un nuevo consultorio
    public function create($data) {
        $this->db->query("INSERT INTO consultorios (numero, nombre, descripcion, estado) 
                          VALUES (:numero, :nombre, :descripcion, :estado)");
        
        // Vincular valores
        $this->db->bind(':numero', $data['numero']);
        $this->db->bind(':nombre', $data['nombre']);
        $this->db->bind(':descripcion', $data['descripcion']);
        $this->db->bind(':estado', isset($data['estado']) ? $data['estado'] : 1);
        
        // Ejecutar
        if($this->db->execute()) {
            return $this->db->lastInsertId();
        } else {
            return false;
        }
    }

    // Actualizar un consultorio existente
    public function update($data) {
        $this->db->query("UPDATE consultorios SET numero = :numero, nombre = :nombre, 
                          descripcion = :descripcion, estado = :estado 
                          WHERE id_consultorio = :id");
        
        // Vincular valores
        $this->db->bind(':id', $data['id_consultorio']);
        $this->db->bind(':numero', $data['numero']);
        $this->db->bind(':nombre', $data['nombre']);
        $this->db->bind(':descripcion', $data['descripcion']);
        $this->db->bind(':estado', isset($data['estado']) ? $data['estado'] : 1);
        
        // Ejecutar
        return $this->db->execute();
    }

    // Eliminar un consultorio por su ID
    public function delete($id) {
        $this->db->query("DELETE FROM consultorios WHERE id_consultorio = :id");
        $this->db->bind(':id', $id);
        return $this->db->execute();
    }
}
