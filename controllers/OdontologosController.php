<?php


class OdontologosController {
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
     * Lista todos los odontólogos
     */
    public function listar() {
        try {
            $sql = "
                SELECT o.*, e.nombre AS especialidad
                FROM odontologos o
                LEFT JOIN especialidades e ON o.id_especialidad = e.id_especialidad
                ORDER BY o.apellido
            ";
            $stmt = $this->db->query($sql);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            die('Error al listar odontólogos: ' . $e->getMessage());
        }
    }

    /**
     * Obtiene un odontólogo por ID
     */
    public function obtenerPorId($id) {
        try {
            $sql = "SELECT * FROM odontologos WHERE id_odontologo = :id";
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            die('Error al obtener odontólogo: ' . $e->getMessage());
        }
    }

    /**
     * Agrega un nuevo odontólogo
     */
    public function agregar($datos) {
        try {
            $sql = "
                INSERT INTO odontologos 
                (cedula, nombre, apellido, telefono, correo, num_licencia, id_especialidad, id_usuario)
                VALUES
                (:cedula, :nombre, :apellido, :telefono, :correo, :num_licencia, :id_especialidad, :id_usuario)
            ";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                ':cedula' => $datos['cedula'],
                ':nombre' => $datos['nombre'],
                ':apellido' => $datos['apellido'],
                ':telefono' => $datos['telefono'],
                ':correo' => $datos['correo'],
                ':num_licencia' => $datos['num_licencia'],
                ':id_especialidad' => $datos['id_especialidad'],
                ':id_usuario' => $datos['id_usuario'],
            ]);
            return $this->db->lastInsertId();
        } catch (PDOException $e) {
            die('Error al agregar odontólogo: ' . $e->getMessage());
        }
    }

    /**
     * Actualiza un odontólogo
     */
    public function actualizar($id, $datos) {
        try {
            $sql = "
                UPDATE odontologos SET
                    cedula = :cedula,
                    nombre = :nombre,
                    apellido = :apellido,
                    telefono = :telefono,
                    correo = :correo,
                    num_licencia = :num_licencia,
                    id_especialidad = :id_especialidad,
                    id_usuario = :id_usuario
                WHERE id_odontologo = :id
            ";
            $stmt = $this->db->prepare($sql);
            $datos[':id'] = $id;
            $stmt->execute([
                ':cedula' => $datos['cedula'],
                ':nombre' => $datos['nombre'],
                ':apellido' => $datos['apellido'],
                ':telefono' => $datos['telefono'],
                ':correo' => $datos['correo'],
                ':num_licencia' => $datos['num_licencia'],
                ':id_especialidad' => $datos['id_especialidad'],
                ':id_usuario' => $datos['id_usuario'],
                ':id' => $id
            ]);
            return true;
        } catch (PDOException $e) {
            die('Error al actualizar odontólogo: ' . $e->getMessage());
        }
    }

    /**
     * Elimina un odontólogo por ID
     */
    public function eliminar($id) {
        try {
            $sql = "DELETE FROM odontologos WHERE id_odontologo = :id";
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            return true;
        } catch (PDOException $e) {
            die('Error al eliminar odontólogo: ' . $e->getMessage());
        }
    }
}
