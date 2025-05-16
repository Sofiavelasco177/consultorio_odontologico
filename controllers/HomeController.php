<?php


class HomeController {
    private $db;
    
    /**
     * Constructor - inicializa la conexión a la base de datos
     */
    public function __construct() {
        // Inicializar conexión a la base de datos
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
        
        // Iniciar sesión si no está iniciada
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
    }
    
    /**
     * Obtiene datos para el dashboard
     * 
     * @return array Datos del dashboard
     */
    public function obtenerDatosDashboard() {
        try {
            $fecha_hoy = date('Y-m-d');
            $fecha_inicio_mes = date('Y-m-01');
            $fecha_fin_mes = date('Y-m-t');
            
            $datos = [
                'citas_hoy' => $this->obtenerCitasHoy(),
                'estadisticas_mes' => $this->obtenerEstadisticasMes($fecha_inicio_mes, $fecha_fin_mes),
                'pacientes_recientes' => $this->obtenerPacientesRecientes(),
                'actividad_reciente' => $this->obtenerActividadReciente(),
                'odontologos_activos' => $this->obtenerOdontologosActivos(),
                'consultorios_disponibles' => $this->obtenerConsultoriosDisponibles($fecha_hoy)
            ];
            
            // Agregar datos específicos según el rol del usuario
            if (isset($_SESSION['usuario_rol'])) {
                switch ($_SESSION['usuario_rol']) {
                    case 'administrador':
                        $datos['estadisticas_generales'] = $this->obtenerEstadisticasGenerales();
                        break;
                    case 'odontologo':
                        $datos['mis_citas'] = $this->obtenerCitasOdontologo($_SESSION['usuario_id']);
                        break;
                    case 'recepcionista':
                        $datos['citas_pendientes'] = $this->obtenerCitasPendientes();
                        break;
                }
            }
            
            return $datos;
        } catch (PDOException $e) {
            die('Error al obtener datos del dashboard: ' . $e->getMessage());
        }
    }
    
    /**
     * Obtiene las citas programadas para hoy
     * 
     * @return array Lista de citas
     */
    private function obtenerCitasHoy() {
        try {
            $fecha_hoy = date('Y-m-d');
            
            $sql = "
                SELECT c.id, c.hora_inicio, c.hora_fin, c.estado,
                       p.nombre AS paciente_nombre, p.apellido AS paciente_apellido,
                       o.nombre AS odontologo_nombre, o.apellido AS odontologo_apellido,
                       co.nombre AS consultorio_nombre
                FROM citas c
                JOIN pacientes p ON c.id_paciente = p.id
                JOIN odontologos o ON c.id_odontologo = o.id
                JOIN consultorios co ON c.id_consultorio = co.id
                WHERE c.fecha = :fecha_hoy
                ORDER BY c.hora_inicio
            ";
            
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':fecha_hoy', $fecha_hoy, PDO::PARAM_STR);
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
              die('Error al obtener citas de hoy: ' . $e->getMessage());
        }
    }

    private function obtenerEstadisticasMes($inicio, $fin) {
        try {
            $sql = "
                SELECT 
                    COUNT(*) AS total_citas,
                    SUM(CASE WHEN estado = 'realizada' THEN 1 ELSE 0 END) AS citas_realizadas,
                    SUM(CASE WHEN estado = 'cancelada' THEN 1 ELSE 0 END) AS citas_canceladas
                FROM citas
                WHERE fecha BETWEEN :inicio AND :fin
            ";

            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':inicio', $inicio);
            $stmt->bindParam(':fin', $fin);
            $stmt->execute();

            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            die('Error al obtener estadísticas del mes: ' . $e->getMessage());
        }
    }

    private function obtenerPacientesRecientes() {
        try {
            $sql = "SELECT id, nombre, apellido, fecha_registro FROM pacientes ORDER BY fecha_registro DESC LIMIT 5";
            $stmt = $this->db->query($sql);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            die('Error al obtener pacientes recientes: ' . $e->getMessage());
        }
    }

    private function obtenerActividadReciente() {
        try {
            $sql = "SELECT descripcion, fecha_hora FROM actividad ORDER BY fecha_hora DESC LIMIT 5";
            $stmt = $this->db->query($sql);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            die('Error al obtener actividad reciente: ' . $e->getMessage());
        }
    }

    private function obtenerOdontologosActivos() {
        try {
            $sql = "SELECT id, nombre, apellido FROM odontologos WHERE estado = 'activo'";
            $stmt = $this->db->query($sql);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            die('Error al obtener odontólogos activos: ' . $e->getMessage());
        }
    }

    private function obtenerConsultoriosDisponibles($fecha) {
        try {
            $sql = "
                SELECT id, nombre FROM consultorios 
                WHERE id NOT IN (
                    SELECT id_consultorio FROM citas WHERE fecha = :fecha
                )
            ";
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':fecha', $fecha);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            die('Error al obtener consultorios disponibles: ' . $e->getMessage());
        }
    }

    private function obtenerEstadisticasGenerales() {
        try {
            $sql = "
                SELECT 
                    (SELECT COUNT(*) FROM pacientes) AS total_pacientes,
                    (SELECT COUNT(*) FROM odontologos WHERE estado = 'activo') AS total_odontologos,
                    (SELECT COUNT(*) FROM citas) AS total_citas,
                    (SELECT COUNT(*) FROM consultorios) AS total_consultorios
            ";
            $stmt = $this->db->query($sql);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            die('Error al obtener estadísticas generales: ' . $e->getMessage());
        }
    }

    private function obtenerCitasOdontologo($odontologo_id) {
        try {
            $fecha_hoy = date('Y-m-d');
            $sql = "
                SELECT c.id, c.fecha, c.hora_inicio, c.hora_fin, c.estado,
                       p.nombre AS paciente_nombre, p.apellido AS paciente_apellido
                FROM citas c
                JOIN pacientes p ON c.id_paciente = p.id
                WHERE c.id_odontologo = :odontologo_id AND c.fecha >= :fecha
                ORDER BY c.fecha, c.hora_inicio
            ";
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':odontologo_id', $odontologo_id, PDO::PARAM_INT);
            $stmt->bindParam(':fecha', $fecha_hoy, PDO::PARAM_STR);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            die('Error al obtener citas del odontólogo: ' . $e->getMessage());
        }
    }

    private function obtenerCitasPendientes() {
        try {
            $fecha_hoy = date('Y-m-d');
            $sql = "
                SELECT c.id, c.fecha, c.hora_inicio, c.hora_fin,
                       p.nombre AS paciente_nombre, p.apellido AS paciente_apellido,
                       o.nombre AS odontologo_nombre, o.apellido AS odontologo_apellido
                FROM citas c
                JOIN pacientes p ON c.id_paciente = p.id
                JOIN odontologos o ON c.id_odontologo = o.id
                WHERE c.estado = 'pendiente' AND c.fecha >= :fecha
                ORDER BY c.fecha, c.hora_inicio
            ";
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':fecha', $fecha_hoy, PDO::PARAM_STR);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            die('Error al obtener citas pendientes: ' . $e->getMessage());
        }
    }
}
