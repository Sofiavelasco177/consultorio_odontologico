<?php
class Auth {
    private $db;

    public function __construct() {
        $this->db = new Database;
    }

    public function login($username, $password) {
        // Buscar usuario por nombre de usuario
        $this->db->query("SELECT u.*, r.nombre_rol 
                          FROM usuarios u 
                          JOIN roles r ON u.id_rol = r.id_rol 
                          WHERE u.nombre_usuario = :username AND u.estado = 1");
        $this->db->bind(':username', $username);
        
        $row = $this->db->single();
        
        // Verificar que existe el usuario
        if(!$row) {
            return false;
        }
        
        // Verificar contraseña
        if(password_verify($password, $row->contrasena)) {
            return $row;
        } else {
            return false;
        }
    }

    public function getUserById($userId) {
        $this->db->query("SELECT u.*, r.nombre_rol 
                          FROM usuarios u 
                          JOIN roles r ON u.id_rol = r.id_rol 
                          WHERE u.id_usuario = :id");
        $this->db->bind(':id', $userId);
        
        return $this->db->single();
    }
    
    public function createUser($data) {
        $this->db->query("INSERT INTO usuarios (nombre_usuario, contrasena, correo, id_rol) 
                          VALUES (:username, :password, :email, :role_id)");
        
        // Vincular valores
        $this->db->bind(':username', $data['username']);
        $this->db->bind(':password', password_hash($data['password'], PASSWORD_DEFAULT));
        $this->db->bind(':email', $data['email']);
        $this->db->bind(':role_id', $data['role_id']);
        
        // Ejecutar
        if($this->db->execute()) {
            return $this->db->lastInsertId();
        } else {
            return false;
        }
    }
    
    public function checkUsernameExists($username) {
        $this->db->query("SELECT id_usuario FROM usuarios WHERE nombre_usuario = :username");
        $this->db->bind(':username', $username);
        
        $this->db->execute();
        
        // Si encontramos un registro, el nombre de usuario existe
        if($this->db->rowCount() > 0) {
            return true;
        } else {
            return false;
        }
    }
    
    public function checkEmailExists($email) {
        $this->db->query("SELECT id_usuario FROM usuarios WHERE correo = :email");
        $this->db->bind(':email', $email);
        
        $this->db->execute();
        
        // Si encontramos un registro, el correo existe
        if($this->db->rowCount() > 0) {
            return true;
        } else {
            return false;
        }
    }
}
?>