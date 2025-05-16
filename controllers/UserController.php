<?php


class UsersController {
    private $db;
    
    public function __construct() {

        try {
            $this->db = new PDO(
                'mysql:host=localhost;dbname=nombre_base_datos',
                'usuario',
                'contraseña',
                [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
            );
        } catch (PDOException $e) {
            die('Error de conexión: ' . $e->getMessage());
        }
    }
    
    /**
     * Inicia sesión de usuario
     * 
     * @param string $username Nombre de usuario o email
     * @param string $password Contraseña
     * @return array|false Datos del usuario o false si falla
     */
    public function login($username, $password) {
        try {
            // Buscar usuario por nombre de usuario o email
            $stmt = $this->db->prepare("SELECT * FROM usuarios WHERE username = :username OR email = :email");
            $stmt->bindParam(':username', $username, PDO::PARAM_STR);
            $stmt->bindParam(':email', $username, PDO::PARAM_STR);
            $stmt->execute();
            
            $usuario = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($usuario && password_verify($password, $usuario['password'])) {
                // Actualizar último acceso
                $this->actualizarUltimoAcceso($usuario['id']);
                
                // Eliminar el hash de contraseña antes de devolver los datos del usuario
                unset($usuario['password']);
                
                // Iniciar sesión
                session_start();
                $_SESSION['usuario_id'] = $usuario['id'];
                $_SESSION['usuario_nombre'] = $usuario['nombre'];
                $_SESSION['usuario_rol'] = $usuario['rol'];
                
                return $usuario;
            }
            
            return false;
        } catch (PDOException $e) {
            die('Error al iniciar sesión: ' . $e->getMessage());
        }
    }
    
    /**
     * Cierra la sesión del usuario
     */
    public function logout() {
        session_start();
        session_destroy();
        
        // Redirigir a la página de inicio
        header('Location: index.php');
        exit;
    }
    
    /**
     * Registra un nuevo usuario
     * 
     * @param array $datos Datos del usuario
     * @return int|false ID del usuario creado o false si falla
     */
    public function registrarUsuario($datos) {
        try {
            // Verificar si el usuario o email ya existe
            if ($this->existeUsuario($datos['username'], $datos['email'])) {
                return false;
            }
            
            // Encriptar contraseña
            $password_hash = password_hash($datos['password'], PASSWORD_DEFAULT);
            
            $stmt = $this->db->prepare("INSERT INTO usuarios 
                                       (nombre, apellido, username, email, password, rol, fecha_registro)
                                       VALUES 
                                       (:nombre, :apellido, :username, :email, :password, :rol, NOW())");
            
            $stmt->bindParam(':nombre', $datos['nombre'], PDO::PARAM_STR);
            $stmt->bindParam(':apellido', $datos['apellido'], PDO::PARAM_STR);
            $stmt->bindParam(':username', $datos['username'], PDO::PARAM_STR);
            $stmt->bindParam(':email', $datos['email'], PDO::PARAM_STR);
            $stmt->bindParam(':password', $password_hash, PDO::PARAM_STR);
            $stmt->bindParam(':rol', $datos['rol'], PDO::PARAM_STR);
            
            $stmt->execute();
            return $this->db->lastInsertId();
        } catch (PDOException $e) {
            die('Error al registrar usuario: ' . $e->getMessage());
        }
    }
    
    /**
     * Verifica si ya existe un usuario con el mismo username o email
     * 
     * @param string $username Nombre de usuario
     * @param string $email Email
     * @return bool True si existe, false si no
     */
    private function existeUsuario($username, $email) {
        try {
            $stmt = $this->db->prepare("SELECT COUNT(*) FROM usuarios WHERE username = :username OR email = :email");
            $stmt->bindParam(':username', $username, PDO::PARAM_STR);
            $stmt->bindParam(':email', $email, PDO::PARAM_STR);
            $stmt->execute();
            
            return $stmt->fetchColumn() > 0;
        } catch (PDOException $e) {
            die('Error al verificar usuario: ' . $e->getMessage());
        }
    }
    
    /**
     * Actualiza la fecha de último acceso
     * 
     * @param int $id ID del usuario
     */
    private function actualizarUltimoAcceso($id) {
        try {
            $stmt = $this->db->prepare("UPDATE usuarios SET ultimo_acceso = NOW() WHERE id = :id");
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
        } catch (PDOException $e) {
            // No lanzamos excepción, solo registramos el error
            error_log('Error al actualizar último acceso: ' . $e->getMessage());
        }
    }
    
    /**
     * Obtiene un usuario por su ID
     * 
     * @param int $id ID del usuario
     * @return array|false Datos del usuario o false si no existe
     */
    public function obtenerUsuario($id) {
        try {
            $stmt = $this->db->prepare("SELECT id, nombre, apellido, username, email, rol, fecha_registro, ultimo_acceso 
                                        FROM usuarios WHERE id = :id");
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            die('Error al obtener usuario: ' . $e->getMessage());
        }
    }
    
    /**
     * Obtiene todos los usuarios
     * 
     * @return array Lista de usuarios
     */
    public function listarUsuarios() {
        try {
            $stmt = $this->db->prepare("SELECT id, nombre, apellido, username, email, rol, fecha_registro, ultimo_acceso 
                                        FROM usuarios ORDER BY apellido, nombre");
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            die('Error al listar usuarios: ' . $e->getMessage());
        }
    }
    
    /**
     * Actualiza los datos de un usuario
     * 
     * @param int $id ID del usuario
     * @param array $datos Datos actualizados
     * @return bool Éxito o fracaso
     */
    public function actualizarUsuario($id, $datos) {
        try {
            $sql = "UPDATE usuarios SET 
                   nombre = :nombre,
                   apellido = :apellido,
                   email = :email";
            
            // Si se incluye una nueva contraseña, la actualizamos
            if (!empty($datos['password'])) {
                $sql .= ", password = :password";
            }
            
            if (isset($datos['rol'])) {
                $sql .= ", rol = :rol";
            }
            
            $sql .= " WHERE id = :id";
            
            $stmt = $this->db->prepare($sql);
            
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->bindParam(':nombre', $datos['nombre'], PDO::PARAM_STR);
            $stmt->bindParam(':apellido', $datos['apellido'], PDO::PARAM_STR);
            $stmt->bindParam(':email', $datos['email'], PDO::PARAM_STR);
            
            if (!empty($datos['password'])) {
                $password_hash = password_hash($datos['password'], PASSWORD_DEFAULT);
                $stmt->bindParam(':password', $password_hash, PDO::PARAM_STR);
            }
            
            if (isset($datos['rol'])) {
                $stmt->bindParam(':rol', $datos['rol'], PDO::PARAM_STR);
            }
            
            $stmt->execute();
            return $stmt->rowCount() > 0;
        } catch (PDOException $e) {
            die('Error al actualizar usuario: ' . $e->getMessage());
        }
    }
    
    /**
     * Elimina un usuario
     * 
     * @param int $id ID del usuario
     * @return bool Éxito o fracaso
     */
    public function eliminarUsuario($id) {
        try {
            $stmt = $this->db->prepare("DELETE FROM usuarios WHERE id = :id");
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->rowCount() > 0;
        } catch (PDOException $e) {
            die('Error al eliminar usuario: ' . $e->getMessage());
        }
    }
    
    /**
     * Verifica si una contraseña es correcta para un usuario
     * 
     * @param int $id ID del usuario
     * @param string $password Contraseña a verificar
     * @return bool True si es correcta, false si no
     */
    public function verificarPassword($id, $password) {
        try {
            $stmt = $this->db->prepare("SELECT password FROM usuarios WHERE id = :id");
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            
            $hash = $stmt->fetchColumn();
            
            return password_verify($password, $hash);
        } catch (PDOException $e) {
            die('Error al verificar contraseña: ' . $e->getMessage());
        }
    }
    
    /**
     * Cambia la contraseña de un usuario
     * 
     * @param int $id ID del usuario
     * @param string $nueva_password Nueva contraseña
     * @return bool Éxito o fracaso
     */
    public function cambiarPassword($id, $nueva_password) {
        try {
            $password_hash = password_hash($nueva_password, PASSWORD_DEFAULT);
            
            $stmt = $this->db->prepare("UPDATE usuarios SET password = :password WHERE id = :id");
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->bindParam(':password', $password_hash, PDO::PARAM_STR);
            $stmt->execute();
            
            return $stmt->rowCount() > 0;
        } catch (PDOException $e) {
            die('Error al cambiar contraseña: ' . $e->getMessage());
        }
    }
}
?>