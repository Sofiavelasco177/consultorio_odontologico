<?php


class ConsultoriosController {
    private $db;
    
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
     * Obtiene todos los consultorios
     * 
     * @param bool $incluir_inactivos Incluir consultorios inactivos
     * @return array Lista de consultorios
     */
    public function listarConsultorios($incluir_inactivos = false) {
        try {
            $sql = "SELECT * FROM consultorios";
            
            if (!$incluir_inactivos) {
                $sql .= " WHERE estado = 'activo'";
            }
            
            $sql .= " ORDER BY nombre";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            die('Error al listar consultorios: ' . $e->getMessage());
        }
    }
    
    /**
     * Obtiene un consultorio por su ID
     * 
     * @param int $id ID del consultorio
     * @return array|false Datos del consultorio o false si no existe
     */
    public function obtenerConsultorio($id) {
        try {
            $stmt = $this->db->prepare("SELECT * FROM consultorios WHERE id = :id");
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            die('Error al obtener consultorio: ' . $e->getMessage());
        }
    }
    
    /**
     * Crea un nuevo consultorio
     * 
     * @param array $datos Datos del consultorio
     * @return int|false ID del consultorio creado o false si falla
     */
    public function crearConsultorio($datos) {
        try {
            $stmt = $this->db->prepare("INSERT INTO consultorios 
                                      (nombre, ubicacion, descripcion, capacidad, estado, equipamiento, fecha_creacion)
                                      VALUES 
                                      (:nombre, :ubicacion, :descripcion, :capacidad, :estado, :equipamiento, NOW())");
            
            $stmt->bindParam(':nombre', $datos['nombre'], PDO::PARAM_STR);
            $stmt->bindParam(':ubicacion', $datos['ubicacion'], PDO::PARAM_STR);
            $stmt->bindParam(':descripcion', $datos['descripcion'], PDO::PARAM_STR);
            $stmt->bindParam(':capacidad', $datos['capacidad'], PDO::PARAM_INT);
            
            // Estado por defecto: activo
            $estado = isset($datos['estado']) ? $datos['estado'] : 'activo';
            $stmt->bindParam(':estado', $estado, PDO::PARAM_STR);
            
            $stmt->bindParam(':equipamiento', $datos['equipamiento'], PDO::PARAM_STR);
            
            $stmt->execute();
            return $this->db->lastInsertId();
        } catch (PDOException $e) {
            die('Error al crear consultorio: ' . $e->getMessage());
        }
    }
    
    /**
     * Actualiza un consultorio existente
     * 
     * @param int $id ID del consultorio
     * @param array $datos Datos actualizados
     * @return bool Éxito o fracaso
     */
    public function actualizarConsultorio($id, $datos) {
        try {
            // Construir la consulta SQL dinámicamente
            $sql = "UPDATE consultorios SET fecha_actualizacion = NOW()";
            $parametros = [];
            
            $campos = [
                'nombre', 'ubicacion', 'descripcion', 'capacidad', 
                'estado', 'equipamiento'
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
            return $stmt->rowCount() > 0;
        } catch (PDOException $e) {
            die('Error al actualizar consultorio: ' . $e->getMessage());
        }
    }
    
    /**
     * Elimina un consultorio
     * 
     * @param int $id ID del consultorio
     * @return bool Éxito o fracaso
     */
    public function eliminarConsultorio($id) {
        try {
            // Verificar si tiene citas asignadas
            if ($this->tieneCitasAsignadas($id)) {
                return false;
            }
            
            $stmt = $this->db->prepare("DELETE FROM consultorios WHERE id = :id");
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->rowCount() > 0;
        } catch (PDOException $e) {
            die('Error al eliminar consultorio: ' . $e->getMessage());
        }
    }
    
    /**
     * Cambia el estado de un consultorio (activo/inactivo)
     * 
     * @param int $id ID del consultorio
     * @param string $estado Nuevo estado
     * @return bool Éxito o fracaso
     */
    public function cambiarEstadoConsultorio($id, $estado) {
        try {
            $estados_validos = ['activo', 'inactivo', 'mantenimiento'];
            
            if (!in_array($estado, $estados_validos)) {
                return false;
            }
            
            $stmt = $this->db->prepare("UPDATE consultorios 
                                      SET estado = :estado, 
                                          fecha_actualizacion = NOW()
                                      WHERE id = :id");
            
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->bindParam(':estado', $estado, PDO::PARAM_STR);
            $stmt->execute();
            
            return $stmt->rowCount() > 0;
        } catch (PDOException $e) {
            die('Error al cambiar estado del consultorio: ' . $e->getMessage());
        }
    }
    
    /**
     * Verifica si un consultorio tiene citas asignadas
     * 
     * @param int $id ID del consultorio
     * @return bool True si tiene citas, false si no
     */
    private function tieneCitasAsignadas($id) {
        try {
            $stmt = $this->db->prepare("SELECT COUNT(*) FROM citas 
                                      WHERE id_consultorio = :id_consultorio 
                                      AND fecha >= CURDATE()
                                      AND estado NOT IN ('cancelada', 'completada')");
            
            $stmt->bindParam(':id_consultorio', $id, PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetchColumn() > 0;
        } catch (PDOException $e) {
            die('Error al verificar citas asignadas: ' . $e->getMessage());
        }
    }
    
    /**
     * Obtiene la disponibilidad de un consultorio en una fecha específica
     * 
     * @param int $id ID del consultorio
     * @param string $fecha Fecha a consultar (YYYY-MM-DD)
     * @return array Horarios disponibles y ocupados
     */
    public function disponibilidadConsultorio($id, $fecha) {
        try {
            // Obtener todas las citas del consultorio en esa fecha
            $stmt = $this->db->prepare("SELECT hora_inicio, hora_fin 
                                      FROM citas 
                                      WHERE id_consultorio = :id_consultorio 
                                      AND fecha = :fecha
                                      AND estado NOT IN ('cancelada')
                                      ORDER BY hora_inicio");
            
            $stmt->bindParam(':id_consultorio', $id, PDO::PARAM_INT);
            $stmt->bindParam(':fecha', $fecha, PDO::PARAM_STR);
            $stmt->execute();
            
            $citas = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Definir horario de atención (ejemplo: 8:00 a 18:00)
            $hora_apertura = "08:00:00";
            $hora_cierre = "18:00:00";
            
            // Intervalo de citas (ejemplo: 30 minutos)
            $intervalo_minutos = 30;
            
            // Calcular slots disponibles
            $slots_disponibles = [];
            $slots_ocupados = [];
            
            $hora_actual = $hora_apertura;
            
            while (strtotime($hora_actual) < strtotime($hora_cierre)) {
                $hora_fin = date('H:i:s', strtotime("$hora_actual + $intervalo_minutos minutes"));
                
                $ocupado = false;
                foreach ($citas as $cita) {
                    // Verificar si el slot se superpone con alguna cita
                    if (
                        (strtotime($hora_actual) >= strtotime($cita['hora_inicio']) && strtotime($hora_actual) < strtotime($cita['hora_fin'])) ||
                        (strtotime($hora_fin) > strtotime($cita['hora_inicio']) && strtotime($hora_fin) <= strtotime($cita['hora_fin'])) ||
                        (strtotime($hora_actual) <= strtotime($cita['hora_inicio']) && strtotime($hora_fin) >= strtotime($cita['hora_fin']))
                    ) {
                        $ocupado = true;
                        break;
                    }
                }
                
                $slot = [
                    'hora_inicio' => $hora_actual,
                    'hora_fin' => $hora_fin
                ];
                
                if ($ocupado) {
                    $slots_ocupados[] = $slot;
                } else {
                    $slots_disponibles[] = $slot;
                }
                
                // Avanzar al siguiente slot
                $hora_actual = $hora_fin;
            }
            
            return [
                'disponibles' => $slots_disponibles,
                'ocupados' => $slots_ocupados
            ];
        } catch (PDOException $e) {
            die('Error al verificar disponibilidad: ' . $e->getMessage());
        }
    }
    
    /**
     * Asigna equipamiento a un consultorio
     * 
     * @param int $id ID del consultorio
     * @param array $equipamiento Lista de equipamiento
     * @return bool Éxito o fracaso
     */
    public function asignarEquipamiento($id, $equipamiento) {
        try {
            // Convertir el array a formato JSON para almacenar
            $equipamiento_json = json_encode($equipamiento);
            
            $stmt = $this->db->prepare("UPDATE consultorios 
                                      SET equipamiento = :equipamiento, 
                                          fecha_actualizacion = NOW()
                                      WHERE id = :id");
            
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->bindParam(':equipamiento', $equipamiento_json, PDO::PARAM_STR);
            $stmt->execute();
            
            return $stmt->rowCount() > 0;
        } catch (PDOException $e) {
            die('Error al asignar equipamiento: ' . $e->getMessage());
        }
    }
    
    /**
     * Busca consultorios por nombre o ubicación
     * 
     * @param string $termino Término de búsqueda
     * @return array Lista de consultorios que coinciden
     */
    public function buscarConsultorios($termino) {
        try {
            $termino = "%$termino%";
            
            $stmt = $this->db->prepare("SELECT * FROM consultorios 
                                      WHERE nombre LIKE :termino 
                                      OR ubicacion LIKE :termino
                                      ORDER BY nombre");
            
            $stmt->bindParam(':termino', $termino, PDO::PARAM_STR);
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            die('Error al buscar consultorios: ' . $e->getMessage());
        }
    }
    
    /**
     * Obtiene estadísticas de uso de un consultorio
     * 
     * @param int $id ID del consultorio
     * @param string $fecha_inicio Fecha inicial (YYYY-MM-DD)
     * @param string $fecha_fin Fecha final (YYYY-MM-DD)
     * @return array Estadísticas del consultorio
     */
    public function estadisticasConsultorio($id, $fecha_inicio, $fecha_fin) {
        try {
            // Total de citas en el período
            $stmt = $this->db->prepare("SELECT COUNT(*) as total_citas,
                                      SUM(CASE WHEN estado = 'completada' THEN 1 ELSE 0 END) as citas_completadas,
                                      SUM(CASE WHEN estado = 'cancelada' THEN 1 ELSE 0 END) as citas_canceladas,
                                      COUNT(DISTINCT id_odontologo) as total_odontologos,
                                      COUNT(DISTINCT id_paciente) as total_pacientes
                                      FROM citas 
                                      WHERE id_consultorio = :id_consultorio
                                      AND fecha BETWEEN :fecha_inicio AND :fecha_fin");
            
            $stmt->bindParam(':id_consultorio', $id, PDO::PARAM_INT);
            $stmt->bindParam(':fecha_inicio', $fecha_inicio, PDO::PARAM_STR);
            $stmt->bindParam(':fecha_fin', $fecha_fin, PDO::PARAM_STR);
            $stmt->execute();
            
            $estadisticas = $stmt->fetch(PDO::FETCH_ASSOC);
            
            // Ocupación por día de la semana
            $stmt = $this->db->prepare("SELECT 
                                      DAYOFWEEK(fecha) as dia_semana,
                                      COUNT(*) as total_citas
                                      FROM citas 
                                      WHERE id_consultorio = :id_consultorio
                                      AND fecha BETWEEN :fecha_inicio AND :fecha_fin
                                      GROUP BY DAYOFWEEK(fecha)
                                      ORDER BY DAYOFWEEK(fecha)");
            
            $stmt->bindParam(':id_consultorio', $id, PDO::PARAM_INT);
            $stmt->bindParam(':fecha_inicio', $fecha_inicio, PDO::PARAM_STR);
            $stmt->bindParam(':fecha_fin', $fecha_fin, PDO::PARAM_STR);
            $stmt->execute();
            
            $ocupacion_dias = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Ocupación por hora
            $stmt = $this->db->prepare("SELECT 
                                      HOUR(hora_inicio) as hora,
                                      COUNT(*) as total_citas
                                      FROM citas 
                                      WHERE id_consultorio = :id_consultorio
                                      AND fecha BETWEEN :fecha_inicio AND :fecha_fin
                                      GROUP BY HOUR(hora_inicio)
                                      ORDER BY HOUR(hora_inicio)");
            
            $stmt->bindParam(':id_consultorio', $id, PDO::PARAM_INT);
            $stmt->bindParam(':fecha_inicio', $fecha_inicio, PDO::PARAM_STR);
            $stmt->bindParam(':fecha_fin', $fecha_fin, PDO::PARAM_STR);
            $stmt->execute();
            
            $ocupacion_horas = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Combinar todos los resultados
            $estadisticas['ocupacion_por_dia'] = $ocupacion_dias;
            $estadisticas['ocupacion_por_hora'] = $ocupacion_horas;
            
            return $estadisticas;
        } catch (PDOException $e) {
            die('Error al obtener estadísticas: ' . $e->getMessage());
        }
    }
}
?>