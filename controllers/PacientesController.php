<?php

class PacientesController {
    private $db;
    
    /**
     * Constructor - inicializa la conexión a la base de datos
     */
    public function __construct() {
        // Inicializar conexión a la base de datos
        // Ejemplo usando PDO, ajusta según tu configuración
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
     * Obtiene todos los pacientes
     * 
     * @return array Lista de pacientes
     */
    public function listarPacientes() {
        try {
            $stmt = $this->db->prepare("SELECT * FROM pacientes ORDER BY apellido, nombre");
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            die('Error al listar pacientes: ' . $e->getMessage());
        }
    }
    
    /**
     * Obtiene un paciente por su ID
     * 
     * @param int $id ID del paciente
     * @return array|false Datos del paciente o false si no existe
     */
    public function obtenerPaciente($id) {
        try {
            $stmt = $this->db->prepare("SELECT * FROM pacientes WHERE id = :id");
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            die('Error al obtener paciente: ' . $e->getMessage());
        }
    }
    
    /**
     * Busca pacientes por nombre o apellido
     * 
     * @param string $termino Término de búsqueda
     * @return array Lista de pacientes que coinciden
     */
    public function buscarPacientes($termino) {
        try {
            $termino = "%$termino%";
            $stmt = $this->db->prepare("SELECT * FROM pacientes WHERE 
                                        nombre LIKE :termino OR 
                                        apellido LIKE :termino OR 
                                        documento LIKE :termino
                                        ORDER BY apellido, nombre");
            $stmt->bindParam(':termino', $termino, PDO::PARAM_STR);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            die('Error al buscar pacientes: ' . $e->getMessage());
        }
    }
    
    /**
     * Crea un nuevo paciente
     * 
     * @param array $datos Datos del paciente
     * @return int|false ID del paciente creado o false si falla
     */
    public function crearPaciente($datos) {
        try {
            $stmt = $this->db->prepare("INSERT INTO pacientes 
                                      (nombre, apellido, fecha_nacimiento, documento, telefono, email, direccion, observaciones)
                                      VALUES 
                                      (:nombre, :apellido, :fecha_nacimiento, :documento, :telefono, :email, :direccion, :observaciones)");
            
            $stmt->bindParam(':nombre', $datos['nombre'], PDO::PARAM_STR);
            $stmt->bindParam(':apellido', $datos['apellido'], PDO::PARAM_STR);
            $stmt->bindParam(':fecha_nacimiento', $datos['fecha_nacimiento'], PDO::PARAM_STR);
            $stmt->bindParam(':documento', $datos['documento'], PDO::PARAM_STR);
            $stmt->bindParam(':telefono', $datos['telefono'], PDO::PARAM_STR);
            $stmt->bindParam(':email', $datos['email'], PDO::PARAM_STR);
            $stmt->bindParam(':direccion', $datos['direccion'], PDO::PARAM_STR);
            $stmt->bindParam(':observaciones', $datos['observaciones'], PDO::PARAM_STR);
            
            $stmt->execute();
            return $this->db->lastInsertId();
        } catch (PDOException $e) {
            die('Error al crear paciente: ' . $e->getMessage());
        }
    }
    
    /**
     * Actualiza un paciente existente
     * 
     * @param int $id ID del paciente
     * @param array $datos Datos actualizados
     * @return bool Éxito o fracaso
     */
    public function actualizarPaciente($id, $datos) {
        try {
            $stmt = $this->db->prepare("UPDATE pacientes SET 
                                      nombre = :nombre,
                                      apellido = :apellido,
                                      fecha_nacimiento = :fecha_nacimiento,
                                      documento = :documento,
                                      telefono = :telefono,
                                      email = :email,
                                      direccion = :direccion,
                                      observaciones = :observaciones
                                      WHERE id = :id");
            
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->bindParam(':nombre', $datos['nombre'], PDO::PARAM_STR);
            $stmt->bindParam(':apellido', $datos['apellido'], PDO::PARAM_STR);
            $stmt->bindParam(':fecha_nacimiento', $datos['fecha_nacimiento'], PDO::PARAM_STR);
            $stmt->bindParam(':documento', $datos['documento'], PDO::PARAM_STR);
            $stmt->bindParam(':telefono', $datos['telefono'], PDO::PARAM_STR);
            $stmt->bindParam(':email', $datos['email'], PDO::PARAM_STR);
            $stmt->bindParam(':direccion', $datos['direccion'], PDO::PARAM_STR);
            $stmt->bindParam(':observaciones', $datos['observaciones'], PDO::PARAM_STR);
            
            $stmt->execute();
            return $stmt->rowCount() > 0;
        } catch (PDOException $e) {
            die('Error al actualizar paciente: ' . $e->getMessage());
        }
    }
    
    /**
     * Elimina un paciente
     * 
     * @param int $id ID del paciente
     * @return bool Éxito o fracaso
     */
    public function eliminarPaciente($id) {
        try {
            $stmt = $this->db->prepare("DELETE FROM pacientes WHERE id = :id");
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->rowCount() > 0;
        } catch (PDOException $e) {
            die('Error al eliminar paciente: ' . $e->getMessage());
        }
    }
    
    /**
     * Obtiene el historial médico de un paciente
     * 
     * @param int $id ID del paciente
     * @return array Historial médico
     */
    public function obtenerHistorialMedico($id) {
        try {
            $stmt = $this->db->prepare("SELECT * FROM historiales_medicos WHERE id_paciente = :id ORDER BY fecha DESC");
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            die('Error al obtener historial médico: ' . $e->getMessage());
        }
    }
    
    /**
     * Registra una nueva consulta en el historial médico del paciente
     * 
     * @param array $datos Datos de la consulta
     * @return int|false ID de la consulta creada o false si falla
     */
    public function registrarConsulta($datos) {
        try {
            $stmt = $this->db->prepare("INSERT INTO historiales_medicos 
                                      (id_paciente, fecha, motivo_consulta, diagnostico, tratamiento, observaciones)
                                      VALUES 
                                      (:id_paciente, :fecha, :motivo_consulta, :diagnostico, :tratamiento, :observaciones)");
            
            $stmt->bindParam(':id_paciente', $datos['id_paciente'], PDO::PARAM_INT);
            $stmt->bindParam(':fecha', $datos['fecha'], PDO::PARAM_STR);
            $stmt->bindParam(':motivo_consulta', $datos['motivo_consulta'], PDO::PARAM_STR);
            $stmt->bindParam(':diagnostico', $datos['diagnostico'], PDO::PARAM_STR);
            $stmt->bindParam(':tratamiento', $datos['tratamiento'], PDO::PARAM_STR);
            $stmt->bindParam(':observaciones', $datos['observaciones'], PDO::PARAM_STR);
            
            $stmt->execute();
            return $this->db->lastInsertId();
        } catch (PDOException $e) {
            die('Error al registrar consulta: ' . $e->getMessage());
        }
    }
}
?>




