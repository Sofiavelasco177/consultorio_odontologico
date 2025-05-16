<?php
class Paciente {
    private $db;

    public function __construct() {
        $this->db = new Database;
    }

    public function getPacientes() {
        $this->db->query("SELECT p.*, u.correo as usuario_correo 
                        FROM pacientes p 
                        LEFT JOIN usuarios u ON p.id_usuario = u.id_usuario 
                        ORDER BY p.apellido, p.nombre");
        return $this->db->resultSet();
    }

    public function getPacienteById($id) {
        $this->db->query("SELECT p.*, u.correo as usuario_correo 
                        FROM pacientes p 
                        LEFT JOIN usuarios u ON p.id_usuario = u.id_usuario 
                        WHERE p.id_paciente = :id");
        $this->db->bind(':id', $id);
        return $this->db->single();
    }

    public function getPacienteByUserId($userId) {
        $this->db->query("SELECT * FROM pacientes WHERE id_usuario = :id_usuario");
        $this->db->bind(':id_usuario', $userId);
        return $this->db->single();
    }

    public function create($data) {
        $this->db->query("INSERT INTO pacientes (cedula, nombre, apellido, fecha_nacimiento, genero, direccion, telefono, correo, antecedentes_medicos, id_usuario) 
                        VALUES (:cedula, :nombre, :apellido, :fecha_nacimiento, :genero, :direccion, :telefono, :correo, :antecedentes_medicos, :id_usuario)");
        
        // Vincular valores
        $this->db->bind(':cedula', $data['cedula']);
        $this->db->bind(':nombre', $data['nombre']);
        $this->db->bind(':apellido', $data['apellido']);
        $this->db->bind(':fecha_nacimiento', $data['fecha_nacimiento']);
        $this->db->bind(':genero', $data['genero']);
        $this->db->bind(':direccion', $data['direccion']);
        $this->db->bind(':telefono', $data['telefono']);
        $this->db->bind(':correo', $data['correo']);
        $this->db->bind(':antecedentes_medicos', $data['antecedentes_medicos']);
        $this->db->bind(':id_usuario', !empty($data['id_usuario']) ? $data['id_usuario'] : null);
        
        // Ejecutar
        if($this->db->execute()) {
            return $this->db->lastInsertId();
        } else {
            return false;
        }
    }

    public function update($data) {
        $this->db->query("UPDATE pacientes SET cedula = :cedula, nombre = :nombre, apellido = :apellido, 
                        fecha_nacimiento = :fecha_nacimiento, genero = :genero, direccion = :direccion, 
                        telefono = :telefono, correo = :correo, antecedentes_medicos = :antecedentes_medicos 
                        WHERE id_paciente = :id");
        
        // Vincular valores
        $this->db->bind(':id', $data['id_paciente']);
        $this->db->bind(':cedula', $data['cedula']);
        $this->db->bind(':nombre', $data['nombre']);
        $this->db->bind(':apellido', $data['apellido']);
        $this->db->bind(':fecha_nacimiento', $data['fecha_nacimiento']);
        $this->db->bind(':genero', $data['genero']);
        $this->db->bind(':direccion', $data['direccion']);
        $this->db->bind(':telefono', $data['telefono']);
        $this->db->bind(':correo', $data['correo']);
        $this->db->bind(':antecedentes_medicos', $data['antecedentes_medicos']);
        
        // Ejecutar
        return $this->db->execute();
    }

    public function delete($id) {
        $this->db->query("DELETE FROM pacientes WHERE id_paciente = :id");
        $this->db->bind(':id', $id);
        return $this->db->execute();
    }

    public function checkCedulaExists($cedula, $idExcept = null) {
        if($idExcept) {
            $this->db->query("SELECT id_paciente FROM pacientes WHERE cedula = :cedula AND id_paciente != :id");
            $this->db->bind(':id', $idExcept);
        } else {
            $this->db->query("SELECT id_paciente FROM pacientes WHERE cedula = :cedula");
        }
        
        $this->db->bind(':cedula', $cedula);
        $this->db->execute();
        return $this->db->rowCount() > 0;
    }
}