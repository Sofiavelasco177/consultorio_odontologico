<?php


class CitasController {
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
    }
    
    /**
     * Crea una nueva cita
     * 
     * @param array $datos Datos de la cita
     * @return int|false ID de la cita creada o false si falla
     */
    public function crearCita($datos) {
        try {
            // Verificar disponibilidad
            if (!$this->verificarDisponibilidad(
                $datos['id_odontologo'],
                $datos['id_consultorio'],
                $datos['fecha'],
                $datos['hora_inicio'],
                $datos['hora_fin']
            )) {
                return false;
            }
            
            $stmt = $this->db->prepare("INSERT INTO citas 
                                     (id_paciente, id_odontologo, id_consultorio, fecha, hora_inicio, hora_fin, 
                                     motivo, estado, observaciones, fecha_creacion)
                                     VALUES 
                                     (:id_paciente, :id_odontologo, :id_consultorio, :fecha, :hora_inicio, :hora_fin, 
                                     :motivo, :estado, :observaciones, NOW())");
            
            $stmt->bindParam(':id_paciente', $datos['id_paciente'], PDO::PARAM_INT);
            $stmt->bindParam(':id_odontologo', $datos['id_odontologo'], PDO::PARAM_INT);
            $stmt->bindParam(':id_consultorio', $datos['id_consultorio'], PDO::PARAM_INT);
            $stmt->bindParam(':fecha', $datos['fecha'], PDO::PARAM_STR);
            $stmt->bindParam(':hora_inicio', $datos['hora_inicio'], PDO::PARAM_STR);
            $stmt->bindParam(':hora_fin', $datos['hora_fin'], PDO::PARAM_STR);
            $stmt->bindParam(':motivo', $datos['motivo'], PDO::PARAM_STR);
            
            // Estado por defecto: programada
            $estado = isset($datos['estado']) ? $datos['estado'] : 'programada';
            $stmt->bindParam(':estado', $estado, PDO::PARAM_STR);
            
            $stmt->bindParam(':observaciones', $datos['observaciones'], PDO::PARAM_STR);
            
            $stmt->execute();
            $cita_id = $this->db->lastInsertId();
            
            // Enviar notificación (por ejemplo)
            $this->enviarNotificacionCita($cita_id, 'nueva');
            
            return $cita_id;
        } catch (PDOException $e) {
            die('Error al crear cita: ' . $e->getMessage());
        }
    }
    
    /**
     * Verifica la disponibilidad para una cita
     * 
     * @param int $id_odontologo ID del odontólogo
     * @param int $id_consultorio ID del consultorio
     * @param string $fecha Fecha de la cita (YYYY-MM-DD)
     * @param string $hora_inicio Hora de inicio (HH:MM:SS)
     * @param string $hora_fin Hora de fin (HH:MM:SS)
     * @param int $id_cita_excluir ID de cita a excluir (para actualizaciones)
     * @return bool True si está disponible, false si no
     */
    public function verificarDisponibilidad($id_odontologo, $id_consultorio, $fecha, $hora_inicio, $hora_fin, $id_cita_excluir = null) {
        try {
            // Verificar que la fecha no sea pasada
            if (strtotime($fecha) < strtotime(date('Y-m-d'))) {
                return false;
            }
            
            // Verificar que la hora de inicio sea anterior a la de fin
            if (strtotime($hora_inicio) >= strtotime($hora_fin)) {
                return false;
            }
            
            // Consulta base
            $sql = "SELECT COUNT(*) FROM citas 
                   WHERE fecha = :fecha 
                   AND estado NOT IN ('cancelada', 'completada') 
                   AND (
                       (hora_inicio <= :hora_inicio AND hora_fin > :hora_inicio) OR
                       (hora_inicio < :hora_fin AND hora_fin >= :hora_fin) OR
                       (hora_inicio >= :hora_inicio AND hora_fin <= :hora_fin)
                   )
                   AND (
                       id_odontologo = :id_odontologo OR
                       id_consultorio = :id_consultorio
                   )";
            
            // Si estamos actualizando una cita, excluir la cita actual
            if ($id_cita_excluir) {
                $sql .= " AND id != :id_cita_excluir";
            }
            
            $stmt = $this->db->prepare($sql);
            
            $stmt->bindParam(':fecha', $fecha, PDO::PARAM_STR);
            $stmt->bindParam(':hora_inicio', $hora_inicio, PDO::PARAM_STR);
            $stmt->bindParam(':hora_fin', $hora_fin, PDO::PARAM_STR);
            $stmt->bindParam(':id_odontologo', $id_odontologo, PDO::PARAM_INT);
            $stmt->bindParam(':id_consultorio', $id_consultorio, PDO::PARAM_INT);
            
            if ($id_cita_excluir) {
                $stmt->bindParam(':id_cita_excluir', $id_cita_excluir, PDO::PARAM_INT);
            }
            
            $stmt->execute();
            $count = $stmt->fetchColumn();
            
            return $count == 0;
        } catch (PDOException $e) {
            die('Error al verificar disponibilidad: ' . $e->getMessage());
        }
    }
    
    /**
     * Actualiza una cita existente
     * 
     * @param int $id ID de la cita
     * @param array $datos Datos actualizados
     * @return bool Éxito o fracaso
     */
    public function actualizarCita($id, $datos) {
        try {
            // Verificar si la cita existe
            $cita_actual = $this->obtenerCita($id);
            if (!$cita_actual) {
                return false;
            }
            
            // Verificar disponibilidad solo si cambian los datos relevantes
            if (
                isset($datos['id_odontologo']) || 
                isset($datos['id_consultorio']) || 
                isset($datos['fecha']) || 
                isset($datos['hora_inicio']) || 
                isset($datos['hora_fin'])
            ) {
                $id_odontologo = isset($datos['id_odontologo']) ? $datos['id_odontologo'] : $cita_actual['id_odontologo'];
                $id_consultorio = isset($datos['id_consultorio']) ? $datos['id_consultorio'] : $cita_actual['id_consultorio'];
                $fecha = isset($datos['fecha']) ? $datos['fecha'] : $cita_actual['fecha'];
                $hora_inicio = isset($datos['hora_inicio']) ? $datos['hora_inicio'] : $cita_actual['hora_inicio'];
                $hora_fin = isset($datos['hora_fin']) ? $datos['hora_fin'] : $cita_actual['hora_fin'];
                
                if (!$this->verificarDisponibilidad($id_odontologo, $id_consultorio, $fecha, $hora_inicio, $hora_fin, $id)) {
                    return false;
                }
            }
            
            // Construir la consulta SQL dinámicamente
            $sql = "UPDATE citas SET fecha_actualizacion = NOW()";
            $parametros = [];
            
            $campos = [
                'id_paciente', 'id_odontologo', 'id_consultorio', 'fecha', 
                'hora_inicio', 'hora_fin', 'motivo', 'estado', 'observaciones'
            ];
            
            foreach ($campos as $campo) {
                if (isset($datos[$campo])) {
                    $sql .= ", $campo = :$campo";
                    $parametros[$campo] = $datos[$campo];
                }
            }
            
            $sql .= " WHERE id = :id";
            $parametros['id'] = $id;
            
            $stmt = $this->db->prepare($sql);
            
            foreach ($parametros as $clave => $valor) {
                $stmt->bindValue(":$clave", $valor);
            }
            
            $stmt->execute();
            
            // Enviar notificación si cambió la fecha o la hora
            if (isset($datos['fecha']) || isset($datos['hora_inicio'])) {
                $this->enviarNotificacionCita($id, 'reprogramada');
            }
            
            return $stmt->rowCount() > 0;
        } catch (PDOException $e) {
            die('Error al actualizar cita: ' . $e->getMessage());
        }
    }
    
    /**
     * Cancela una cita
     * 
     * @param int $id ID de la cita
     * @param string $motivo_cancelacion Motivo de la cancelación
     * @return bool Éxito o fracaso
     */
    public function cancelarCita($id, $motivo_cancelacion = '') {
        try {
            $stmt = $this->db->prepare("UPDATE citas 
                                      SET estado = 'cancelada', 
                                          motivo_cancelacion = :motivo_cancelacion,
                                          fecha_actualizacion = NOW()
                                      WHERE id = :id");
            
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->bindParam(':motivo_cancelacion', $motivo_cancelacion, PDO::PARAM_STR);
            $stmt->execute();
            
            if ($stmt->rowCount() > 0) {
                // Enviar notificación
                $this->enviarNotificacionCita($id, 'cancelada');
                return true;
            }
            
            return false;
        } catch (PDOException $e) {
            die('Error al cancelar cita: ' . $e->getMessage());
        }
    }
    
    /**
     * Marca una cita como completada
     * 
     * @param int $id ID de la cita
     * @param string $observaciones_finales Observaciones al finalizar la cita
     * @return bool Éxito o fracaso
     */
    public function completarCita($id, $observaciones_finales = '') {
        try {
            $stmt = $this->db->prepare("UPDATE citas 
                                      SET estado = 'completada', 
                                          observaciones_finales = :observaciones_finales,
                                          fecha_actualizacion = NOW(),
                                          fecha_completada = NOW()
                                      WHERE id = :id");
            
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->bindParam(':observaciones_finales', $observaciones_finales, PDO::PARAM_STR);
            $stmt->execute();
            
            return $stmt->rowCount() > 0;
        } catch (PDOException $e) {
            die('Error al completar cita: ' . $e->getMessage());
        }
    }
    
    /**
     * Obtiene una cita por su ID
     * 
     * @param int $id ID de la cita
     * @return array|false Datos de la cita o false si no existe
     */
    public function obtenerCita($id) {
        try {
            $stmt = $this->db->prepare("
                SELECT c.*, 
                       p.nombre AS paciente_nombre, p.apellido AS paciente_apellido,
                       o.nombre AS odontologo_nombre, o.apellido AS odontologo_apellido,
                       co.nombre AS consultorio_nombre
                FROM citas c
                JOIN pacientes p ON c.id_paciente = p.id
                JOIN odontologos o ON c.id_odontologo = o.id
                JOIN consultorios co ON c.id_consultorio = co.id
                WHERE c.id = :id
            ");
            
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            die('Error al obtener cita: ' . $e->getMessage());
        }
    }
    
    /**
     * Obtiene todas las citas en un rango de fechas
     * 
     * @param string $fecha_inicio Fecha inicial (YYYY-MM-DD)
     * @param string $fecha_fin Fecha final (YYYY-MM-DD)
     * @param array $filtros Filtros adicionales (opcional)
     * @return array Lista de citas
     */
    public function listarCitas($fecha_inicio, $fecha_fin, $filtros = []) {
        try {
            $sql = "
                SELECT c.*, 
                       p.nombre AS paciente_nombre, p.apellido AS paciente_apellido,
                       o.nombre AS odontologo_nombre, o.apellido AS odontologo_apellido,
                       co.nombre AS consultorio_nombre
                FROM citas c
                JOIN pacientes p ON c.id_paciente = p.id
                JOIN odontologos o ON c.id_odontologo = o.id
                JOIN consultorios co ON c.id_consultorio = co.id
                WHERE c.fecha BETWEEN :fecha_inicio AND :fecha_fin
            ";
            
            $parametros = [
                ':fecha_inicio' => $fecha_inicio,
                ':fecha_fin' => $fecha_fin
            ];
            
            // Aplicar filtros adicionales
            if (!empty($filtros)) {
                if (isset($filtros['id_paciente'])) {
                    $sql .= " AND c.id_paciente = :id_paciente";
                    $parametros[':id_paciente'] = $filtros['id_paciente'];
                }
                
                if (isset($filtros['id_odontologo'])) {
                    $sql .= " AND c.id_odontologo = :id_odontologo";
                    $parametros[':id_odontologo'] = $filtros['id_odontologo'];
                }
                
                if (isset($filtros['id_consultorio'])) {
                    $sql .= " AND c.id_consultorio = :id_consultorio";
                    $parametros[':id_consultorio'] = $filtros['id_consultorio'];
                }
                
                if (isset($filtros['estado'])) {
                    $sql .= " AND c.estado = :estado";
                    $parametros[':estado'] = $filtros['estado'];
                }
            }
            
            $sql .= " ORDER BY c.fecha, c.hora_inicio";
            
            $stmt = $this->db->prepare($sql);
            
            foreach ($parametros as $param => $valor) {
                $stmt->bindValue($param, $valor);
            }
            
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            die('Error al listar citas: ' . $e->getMessage());
        }
    }
    
    /**
     * Obtiene las citas de un paciente específico
     * 
     * @param int $id_paciente ID del paciente
     * @param string $estado Estado de las citas (opcional)
     * @return array Lista de citas
     */
    public function citasPorPaciente($id_paciente, $estado = null) {
        try {
            $sql = "
                SELECT c.*, 
                       o.nombre AS odontologo_nombre, o.apellido AS odontologo_apellido,
                       co.nombre AS consultorio_nombre
                FROM citas c
                JOIN odontologos o ON c.id_odontologo = o.id
                JOIN consultorios co ON c.id_consultorio = co.id
                WHERE c.id_paciente = :id_paciente
            ";
            
            if ($estado) {
                $sql .= " AND c.estado = :estado";
            }
            
            $sql .= " ORDER BY c.fecha DESC, c.hora_inicio DESC";
            
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':id_paciente', $id_paciente, PDO::PARAM_INT);
            
            if ($estado) {
                $stmt->bindParam(':estado', $estado, PDO::PARAM_STR);
            }
            
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            die('Error al obtener citas del paciente: ' . $e->getMessage());
        }
    }
    
    /**
     * Obtiene las citas de un odontólogo específico
     * 
     * @param int $id_odontologo ID del odontólogo
     * @param string $fecha Fecha específica (YYYY-MM-DD)
     * @param string $estado Estado de las citas (opcional)
     * @return array Lista de citas
     */
    public function citasPorOdontologo($id_odontologo, $fecha = null, $estado = null) {
        try {
            $sql = "
                SELECT c.*, 
                       p.nombre AS paciente_nombre, p.apellido AS paciente_apellido,
                       co.nombre AS consultorio_nombre
                FROM citas c
                JOIN pacientes p ON c.id_paciente = p.id
                JOIN consultorios co ON c.id_consultorio = co.id
                WHERE c.id_odontologo = :id_odontologo
            ";
            
            $parametros = [':id_odontologo' => $id_odontologo];
            
            if ($fecha) {
                $sql .= " AND c.fecha = :fecha";
                $parametros[':fecha'] = $fecha;
            }
            
            if ($estado) {
                $sql .= " AND c.estado = :estado";
                $parametros[':estado'] = $estado;
            }
            
            $sql .= " ORDER BY c.fecha, c.hora_inicio";
            
            $stmt = $this->db->prepare($sql);
            
            foreach ($parametros as $param => $valor) {
                $stmt->bindValue($param, $valor);
            }
            
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            die('Error al obtener citas del odontólogo: ' . $e->getMessage());
        }
    }
    
    /**
     * Obtiene las citas por consultorio
     * 
     * @param int $id_consultorio ID del consultorio
     * @param string $fecha Fecha específica (YYYY-MM-DD)
     * @return array Lista de citas
     */
    public function citasPorConsultorio($id_consultorio, $fecha = null) {
        try {
            $sql = "
                SELECT c.*, 
                       p.nombre AS paciente_nombre, p.apellido AS paciente_apellido,
                       o.nombre AS odontologo_nombre, o.apellido AS odontologo_apellido
                FROM citas c
                JOIN pacientes p ON c.id_paciente = p.id
                JOIN odontologos o ON c.id_odontologo = o.id
                WHERE c.id_consultorio = :id_consultorio
            ";
            
            $parametros = [':id_consultorio' => $id_consultorio];
            
            if ($fecha) {
                $sql .= " AND c.fecha = :fecha";
                $parametros[':fecha'] = $fecha;
            }
            
            $sql .= " ORDER BY c.fecha, c.hora_inicio";
            
            $stmt = $this->db->prepare($sql);
            
            foreach ($parametros as $param => $valor) {
                $stmt->bindValue($param, $valor);
            }
            
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            die('Error al obtener citas del consultorio: ' . $e->getMessage());
        }
    }
    
    /**
     * Obtiene las citas programadas para hoy
     * 
     * @param int $id_odontologo ID del odontólogo (opcional)
     * @return array Lista de citas
     */
    public function citasHoy($id_odontologo = null) {
        try {
            $fecha_hoy = date('Y-m-d');
            
            $sql = "
                SELECT c.*, 
                       p.nombre AS paciente_nombre, p.apellido AS paciente_apellido,
                       o.nombre AS odontologo_nombre, o.apellido AS odontologo_apellido,
                       co.nombre AS consultorio_nombre
                FROM citas c
                JOIN pacientes p ON c.id_paciente = p.id
                JOIN odontologos o ON c.id_odontologo = o.id
                JOIN consultorios co ON c.id_consultorio = co.id
                WHERE c.fecha = :fecha_hoy
                AND c.estado = 'programada'
            ";
            
            $parametros = [':fecha_hoy' => $fecha_hoy];
            
            if ($id_odontologo) {
                $sql .= " AND c.id_odontologo = :id_odontologo";
                $parametros[':id_odontologo'] = $id_odontologo;
            }
            
            $sql .= " ORDER BY c.hora_inicio";
            
            $stmt = $this->db->prepare($sql);
            
            foreach ($parametros as $param => $valor) {
                $stmt->bindValue($param, $valor);
            }
            
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            die('Error al obtener citas de hoy: ' . $e->getMessage());
        }
    }
    
    /**
     * Envía una notificación sobre una cita
     * 
     * @param int $id_cita ID de la cita
     * @param string $tipo_notificacion Tipo de notificación (nueva, reprogramada, cancelada)
     * @return bool Éxito o fracaso
     */
    private function enviarNotificacionCita($id_cita, $tipo_notificacion) {
        try {
            // Obtener datos de la cita
            $cita = $this->obtenerCita($id_cita);
            
            if (!$cita) {
                return false;
            }
            
            // Registrar la notificación en la base de datos
            $stmt = $this->db->prepare("INSERT INTO notificaciones 
                                      (id_paciente, id_cita, tipo, mensaje, fecha_creacion, leida)
                                      VALUES 
                                      (:id_paciente, :id_cita, :tipo, :mensaje, NOW(), 0)");
            
            $id_paciente = $cita['id_paciente'];
            $mensaje = '';
            
            switch ($tipo_notificacion) {
                case 'nueva':
                    $mensaje = "Nueva cita programada para el {$cita['fecha']} a las {$cita['hora_inicio']} con el Dr. {$cita['odontologo_apellido']}";
                    break;
                case 'reprogramada':
                    $mensaje = "Su cita ha sido reprogramada para el {$cita['fecha']} a las {$cita['hora_inicio']} con el Dr. {$cita['odontologo_apellido']}";
                    break;
                case 'cancelada':
                    $mensaje = "Su cita del {$cita['fecha']} a las {$cita['hora_inicio']} ha sido cancelada";
                    break;
                default:
                    $mensaje = "Notificación sobre su cita del {$cita['fecha']}";
            }
            
            $stmt->bindParam(':id_paciente', $id_paciente, PDO::PARAM_INT);
            $stmt->bindParam(':id_cita', $id_cita, PDO::PARAM_INT);
            $stmt->bindParam(':tipo', $tipo_notificacion, PDO::PARAM_STR);
            $stmt->bindParam(':mensaje', $mensaje, PDO::PARAM_STR);
            $stmt->execute();
            
            // Aquí se podría agregar código para enviar email o SMS al paciente
            
            return true;
        } catch (PDOException $e) {
            error_log('Error al enviar notificación: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Verifica si un paciente tiene citas pendientes
     * 
     * @param int $id_paciente ID del paciente
     * @return bool True si tiene citas pendientes, false si no
     */
    public function tieneCitasPendientes($id_paciente) {
        try {
            $stmt = $this->db->prepare("SELECT COUNT(*) FROM citas 
                                      WHERE id_paciente = :id_paciente 
                                      AND estado = 'programada'
                                      AND fecha >= CURDATE()");
            
            $stmt->bindParam(':id_paciente', $id_paciente, PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetchColumn() > 0;
        } catch (PDOException $e) {
            error_log('Error al verificar citas pendientes: ' . $e->getMessage());
            return false;
        }
    }
}
?>