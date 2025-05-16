<?php

class Usuarios {
    private $conexion;
    private $tabla = 'usuarios';

    // Propiedades que mapean a las columnas de la tabla
    public $id_usuario;
    public $nombre_usuario;
    public $contrasena;
    public $correo;
    public $estado;
    public $fecha_registro;
    public $id_rol;

    /**
     * Constructor que recibe la conexión de la base de datos
     * @param PDO $db Conexión a la base de datos
     */
    public function __construct($db) {
        $this->conexion = $db;
    }

    /**
     * Obtiene todos los usuarios de la base de datos
     * @return PDOStatement Resultado de la consulta
     */
    public function obtenerTodos() {
        $query = "SELECT u.id_usuario, u.nombre_usuario, u.correo, u.estado, 
                 u.fecha_registro, u.id_rol, r.nombre_rol 
                 FROM " . $this->tabla . " u
                 LEFT JOIN roles r ON u.id_rol = r.id_rol
                 ORDER BY u.id_usuario DESC";
        
        $stmt = $this->conexion->prepare($query);
        $stmt->execute();
        
        return $stmt;
    }

    /**
     * Obtiene un usuario específico por su ID
     * @param int $id ID del usuario a buscar
     * @return bool True si se encontró el usuario, False en caso contrario
     */
    public function obtenerPorId($id) {
        $query = "SELECT u.id_usuario, u.nombre_usuario, u.correo, u.estado, 
                 u.fecha_registro, u.id_rol, r.nombre_rol 
                 FROM " . $this->tabla . " u
                 LEFT JOIN roles r ON u.id_rol = r.id_rol
                 WHERE u.id_usuario = ?";
        
        $stmt = $this->conexion->prepare($query);
        $stmt->bindParam(1, $id);
        $stmt->execute();
        
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($row) {
            $this->id_usuario = $row['id_usuario'];
            $this->nombre_usuario = $row['nombre_usuario'];
            $this->correo = $row['correo'];
            $this->estado = $row['estado'];
            $this->fecha_registro = $row['fecha_registro'];
            $this->id_rol = $row['id_rol'];
            return true;
        }
        
        return false;
    }

    /**
     * Busca un usuario por nombre de usuario
     * @param string $nombre Nombre de usuario a buscar
     * @return PDOStatement Resultado de la consulta
     */
    public function buscarPorNombre($nombre) {
        $query = "SELECT u.id_usuario, u.nombre_usuario, u.correo, u.estado, 
                 u.fecha_registro, u.id_rol, r.nombre_rol 
                 FROM " . $this->tabla . " u
                 LEFT JOIN roles r ON u.id_rol = r.id_rol
                 WHERE u.nombre_usuario LIKE ?";
        
        $nombreBusqueda = "%{$nombre}%";
        $stmt = $this->conexion->prepare($query);
        $stmt->bindParam(1, $nombreBusqueda);
        $stmt->execute();
        
        return $stmt;
    }

    /**
     * Crea un nuevo usuario en la base de datos
     * @return bool True si se creó correctamente, False en caso contrario
     */
    public function crear() {
        $query = "INSERT INTO " . $this->tabla . " 
                 (nombre_usuario, contrasena, correo, estado, id_rol) 
                 VALUES (?, ?, ?, ?, ?)";
        
        $stmt = $this->conexion->prepare($query);
        
        // Limpieza y sanitización de datos
        $this->nombre_usuario = htmlspecialchars(strip_tags($this->nombre_usuario));
        $this->correo = htmlspecialchars(strip_tags($this->correo));
        
        // Hash de la contraseña para seguridad
        $contrasena_hash = password_hash($this->contrasena, PASSWORD_BCRYPT);
        
        $stmt->bindParam(1, $this->nombre_usuario);
        $stmt->bindParam(2, $contrasena_hash);
        $stmt->bindParam(3, $this->correo);
        $stmt->bindParam(4, $this->estado);
        $stmt->bindParam(5, $this->id_rol);
        
        if ($stmt->execute()) {
            $this->id_usuario = $this->conexion->lastInsertId();
            return true;
        }
        
        return false;
    }

    /**
     * Actualiza la información de un usuario existente
     * @return bool True si se actualizó correctamente, False en caso contrario
     */
    public function actualizar() {
        $query = "UPDATE " . $this->tabla . " 
                 SET nombre_usuario = ?, correo = ?, estado = ?, id_rol = ? 
                 WHERE id_usuario = ?";
        
        $stmt = $this->conexion->prepare($query);
        
        // Limpieza y sanitización de datos
        $this->nombre_usuario = htmlspecialchars(strip_tags($this->nombre_usuario));
        $this->correo = htmlspecialchars(strip_tags($this->correo));
        $this->id_usuario = htmlspecialchars(strip_tags($this->id_usuario));
        
        $stmt->bindParam(1, $this->nombre_usuario);
        $stmt->bindParam(2, $this->correo);
        $stmt->bindParam(3, $this->estado);
        $stmt->bindParam(4, $this->id_rol);
        $stmt->bindParam(5, $this->id_usuario);
        
        if ($stmt->execute()) {
            return true;
        }
        
        return false;
    }

    /**
     * Actualiza la contraseña de un usuario
     * @return bool True si se actualizó correctamente, False en caso contrario
     */
    public function actualizarContrasena() {
        $query = "UPDATE " . $this->tabla . " SET contrasena = ? WHERE id_usuario = ?";
        
        $stmt = $this->conexion->prepare($query);
        
        // Hash de la nueva contraseña
        $contrasena_hash = password_hash($this->contrasena, PASSWORD_BCRYPT);
        
        $stmt->bindParam(1, $contrasena_hash);
        $stmt->bindParam(2, $this->id_usuario);
        
        if ($stmt->execute()) {
            return true;
        }
        
        return false;
    }

    /**
     * Elimina un usuario de la base de datos
     * @return bool True si se eliminó correctamente, False en caso contrario
     */
    public function eliminar() {
        $query = "DELETE FROM " . $this->tabla . " WHERE id_usuario = ?";
        
        $stmt = $this->conexion->prepare($query);
        
        $this->id_usuario = htmlspecialchars(strip_tags($this->id_usuario));
        
        $stmt->bindParam(1, $this->id_usuario);
        
        if ($stmt->execute()) {
            return true;
        }
        
        return false;
    }

    /**
     * Verifica las credenciales de un usuario para el inicio de sesión
     * @param string $nombre_usuario Nombre de usuario
     * @param string $contrasena Contraseña sin encriptar
     * @return bool True si las credenciales son válidas, False en caso contrario
     */
    public function login($nombre_usuario, $contrasena) {
        $query = "SELECT id_usuario, nombre_usuario, contrasena, correo, estado, id_rol 
                 FROM " . $this->tabla . " 
                 WHERE nombre_usuario = ? AND estado = 1";
        
        $stmt = $this->conexion->prepare($query);
        $stmt->bindParam(1, $nombre_usuario);
        $stmt->execute();
        
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($row && password_verify($contrasena, $row['contrasena'])) {
            $this->id_usuario = $row['id_usuario'];
            $this->nombre_usuario = $row['nombre_usuario'];
            $this->correo = $row['correo'];
            $this->estado = $row['estado'];
            $this->id_rol = $row['id_rol'];
            return true;
        }
        
        return false;
    }
    
    /**
     * Verifica si un nombre de usuario ya existe en la base de datos
     * @param string $nombre_usuario Nombre de usuario a verificar
     * @return bool True si el usuario existe, False en caso contrario
     */
    public function existeUsuario($nombre_usuario) {
        $query = "SELECT COUNT(*) FROM " . $this->tabla . " WHERE nombre_usuario = ?";
        
        $stmt = $this->conexion->prepare($query);
        $stmt->bindParam(1, $nombre_usuario);
        $stmt->execute();
        
        $count = $stmt->fetchColumn();
        
        return $count > 0;
    }
    
    /**
     * Verifica si un correo electrónico ya existe en la base de datos
     * @param string $correo Correo electrónico a verificar
     * @return bool True si el correo existe, False en caso contrario
     */
    public function existeCorreo($correo) {
        $query = "SELECT COUNT(*) FROM " . $this->tabla . " WHERE correo = ?";
        
        $stmt = $this->conexion->prepare($query);
        $stmt->bindParam(1, $correo);
        $stmt->execute();
        
        $count = $stmt->fetchColumn();
        
        return $count > 0;
    }
}
?>