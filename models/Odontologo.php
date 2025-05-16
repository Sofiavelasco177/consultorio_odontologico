<?php
class Odontologo {
    private $db;

    public function __construct() {
        $this->db = new Database;
    }

    public function getOdontologos() {
        $this->db->query("SELECT o.*, e.nombre as especialidad_nombre, u.correo as usuario_correo 
                        FROM odontologos o 
                        LEFT JOIN especialidades e ON o.id_especialidad = e.id_especialidad 
                        LEFT JOIN usuarios u ON o.id_usuario = u.id_usuario 
                        ORDER BY o.apellido, o.nombre");
        return $this->db->resultSet();
    }

    public function getOdontologoById($id) {
        $this->db->query("SELECT o.*, e.nombre as especialidad_nombre, u.correo as usuario_correo 
                        FROM odontologos o 
                        LEFT JOIN especialidades e ON o.id_especialidad = e.id_especialidad 
                        LEFT JOIN usuarios u ON o.id_usuario = u.id_usuario 
                        WHERE o.id_odontologo = :id");
        $this->db->bind(':id', $id);
        return $this->db->single();
    }

    public function getOdontologoByUserId($userId) {
        $this->db->query("SELECT * FROM odontologos WHERE id_usuario = :id_usuario");
        $this->db->bind(':id_usuario', $userId);
        return $this->db->single();
    }

    public function create($data) {
        $this->db->query("INSERT INTO odontologos (cedula, nombre, apellido, telefono, correo, num_licencia, id_especialidad, id_usuario) 
                        VALUES (:cedula, :nombre, :apellido, :telefono, :correo, :num_licencia, :id_especialidad, :id_usuario)");
        
        // Vincular valores
        $this->db->bind(':cedula', $data['cedula']);
        $this->db->bind(':nombre', $data['nombre']);
        $this->db->bind(':apellido', $data['apellido']);
        $this->db->bind(':telefono', $data['telefono']);
        $this->db->bind(':correo', $data['correo']);
        $this->db->bind(':num_licencia', $data['num_licencia']);
        $this->db->bind(':id_especialidad', $data['id_especialidad']);
        $this->db->bind(':id_usuario', $data['id_usuario']);
        
        // Ejecutar
        if($this->db->execute()) {
            return $this->db->lastInsertId();
        } else {
            return false;
        }
    }

    public function update($data) {
        $this->db->query("UPDATE odontologos SET cedula = :cedula, nombre = :nombre, apellido = :apellido, 
                        telefono = :telefono, correo = :correo, num_licencia = :num_licencia, 
                        id_especialidad = :id_especialidad 
                        WHERE id_odontologo = :id");
        
        // Vincular valores
        $this->db->bind(':id', $data['id_odontologo']);
        $this->db->bind(':cedula', $data['cedula']);
        $this->db->bind(':nombre', $data['nombre']);
        $this->db->bind(':apellido', $data['apellido']);
        $this->db->bind(':telefono', $data['telefono']);
        $this->db->bind(':correo', $data['correo']);
        $this->db->bind(':num_licencia', $data['num_licencia']);
        $this->db->bind(':id_especialidad', $data['id_especialidad']);
        
        // Ejecutar
        return $this->db->execute();
    }

    public function delete($id) {
        $this->db->query("DELETE FROM odontologos WHERE id_odontologo = :id");
        $this->db->bind(':id', $id);
        return $this->db->execute();
    }

    public function checkCedulaExists($cedula, $idExcept = null) {
        if($idExcept) {
            $this->db->query("SELECT id_odontologo FROM odontologos WHERE cedula = :cedula AND id_odontologo != :id");
            $this->db->bind(':id', $idExcept);
        } else {
            $this->db->query("SELECT id_odontologo FROM odontologos WHERE cedula = :cedula");
        }
        
        $this->db->bind(':cedula', $cedula);
        $this->db->execute();
        return $this->db->rowCount() > 0;
    }

    public function checkLicenciaExists($licencia, $idExcept = null) {
        if($idExcept) {
            $this->db->query("SELECT id_odontologo FROM odontologos WHERE num_licencia = :licencia AND id_odontologo != :id");
            $this->db->bind(':id', $idExcept);
        } else {
            $this->db->query("SELECT id_odontologo FROM odontologos WHERE num_licencia = :licencia");
        }
        
        $this->db->bind(':licencia', $licencia);
        $this->db->execute();
        return $this->db->rowCount() > 0;
    }
}
