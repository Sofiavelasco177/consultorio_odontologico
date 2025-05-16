<?php

class Horario {
    // Propiedades que corresponden a las columnas de la tabla
    private $id_horario;
    private $id_odontologo;
    private $dia_semana;
    private $hora_inicio;
    private $hora_fin;
    
    // Conexión a la base de datos
    private $conn;
    private $table_name = "horarios";
    
    // Array de días de semana válidos
    private $dias_validos = ['Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado', 'Domingo'];
    
    /**
     * Constructor - Inicializa la conexión a la base de datos
     * 
     * @param object $db Conexión a la base de datos
     */
    public function __construct($db) {
        $this->conn = $db;
    }
    
    /**
     * Obtener todos los horarios
     * 
     * @return PDOStatement Resultado de la consulta
     */
    public function getAll() {
        $query = "SELECT h.id_horario, h.id_odontologo, h.dia_semana, 
                  h.hora_inicio, h.hora_fin, o.nombre, o.apellido 
                  FROM " . $this->table_name . " h
                  LEFT JOIN odontologos o ON h.id_odontologo = o.id_odontologo
                  ORDER BY h.id_odontologo, FIELD(h.dia_semana, 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado', 'Domingo'), h.hora_inicio";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        
        return $stmt;
    }
    
    /**
     * Obtener un horario por ID
     * 
     * @param int $id ID del horario a buscar
     * @return bool True si el horario existe, false en caso contrario
     */
    public function getById($id) {
        $query = "SELECT id_horario, id_odontologo, dia_semana, hora_inicio, hora_fin
                  FROM " . $this->table_name . " 
                  WHERE id_horario = ?";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $id);
        $stmt->execute();
        
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($row) {
            $this->id_horario = $row['id_horario'];
            $this->id_odontologo = $row['id_odontologo'];
            $this->dia_semana = $row['dia_semana'];
            $this->hora_inicio = $row['hora_inicio'];
            $this->hora_fin = $row['hora_fin'];
            return true;
        }
        
        return false;
    }
    
    /**
     * Obtener horarios de un odontólogo específico
     * 
     * @param int $id_odontologo ID del odontólogo
     * @return PDOStatement Resultado de la consulta
     */
    public function getByOdontologo($id_odontologo) {
        $query = "SELECT id_horario, id_odontologo, dia_semana, hora_inicio, hora_fin
                  FROM " . $this->table_name . " 
                  WHERE id_odontologo = ?
                  ORDER BY FIELD(dia_semana, 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado', 'Domingo'), hora_inicio";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $id_odontologo);
        $stmt->execute();
        
        return $stmt;
    }
    
    /**
     * Obtener horarios disponibles de un odontólogo para un día específico
     * 
     * @param int $id_odontologo ID del odontólogo
     * @param string $dia Día de la semana
     * @return PDOStatement Resultado de la consulta
     */
    public function getHorariosDisponiblesPorDia($id_odontologo, $dia) {
        $query = "SELECT id_horario, id_odontologo, dia_semana, hora_inicio, hora_fin
                  FROM " . $this->table_name . " 
                  WHERE id_odontologo = ? AND dia_semana = ?
                  ORDER BY hora_inicio";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $id_odontologo);
        $stmt->bindParam(2, $dia);
        $stmt->execute();
        
        return $stmt;
    }
    
    /**
     * Verificar si un horario se superpone con otro existente del mismo odontólogo
     * 
     * @return bool True si hay superposición, false en caso contrario
     */
    public function verificarSuperposicion() {
        $query = "SELECT id_horario FROM " . $this->table_name . "
                  WHERE id_odontologo = ? AND dia_semana = ? 
                  AND ((hora_inicio <= ? AND hora_fin > ?) OR 
                       (hora_inicio < ? AND hora_fin >= ?) OR
                       (hora_inicio >= ? AND hora_fin <= ?))";
        
        if ($this->id_horario) {
            $query .= " AND id_horario != ?";
        }
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->id_odontologo);
        $stmt->bindParam(2, $this->dia_semana);
        $stmt->bindParam(3, $this->hora_fin);
        $stmt->bindParam(4, $this->hora_inicio);
        $stmt->bindParam(5, $this->hora_fin);
        $stmt->bindParam(6, $this->hora_inicio);
        $stmt->bindParam(7, $this->hora_inicio);
        $stmt->bindParam(8, $this->hora_fin);
        
        if ($this->id_horario) {
            $stmt->bindParam(9, $this->id_horario);
        }
        
        $stmt->execute();
        
        return ($stmt->rowCount() > 0);
    }
    
    /**
     * Crear un nuevo horario
     * 
     * @return bool True si se creó correctamente, false en caso contrario
     */
    public function create() {
        // Validar datos antes de insertar
        if (!$this->validarDatos()) {
            return false;
        }
        
        // Verificar superposición de horarios
        if ($this->verificarSuperposicion()) {
            return false;
        }
        
        $query = "INSERT INTO " . $this->table_name . " 
                  (id_odontologo, dia_semana, hora_inicio, hora_fin) 
                  VALUES (?, ?, ?, ?)";
        
        $stmt = $this->conn->prepare($query);
        
        // Limpiar datos
        $this->id_odontologo = htmlspecialchars(strip_tags($this->id_odontologo));
        $this->dia_semana = htmlspecialchars(strip_tags($this->dia_semana));
        $this->hora_inicio = htmlspecialchars(strip_tags($this->hora_inicio));
        $this->hora_fin = htmlspecialchars(strip_tags($this->hora_fin));
        
        // Vincular parámetros
        $stmt->bindParam(1, $this->id_odontologo);
        $stmt->bindParam(2, $this->dia_semana);
        $stmt->bindParam(3, $this->hora_inicio);
        $stmt->bindParam(4, $this->hora_fin);
        
        // Ejecutar consulta
        if ($stmt->execute()) {
            $this->id_horario = $this->conn->lastInsertId();
            return true;
        }
        
        return false;
    }
    
    /**
     * Actualizar horario existente
     * 
     * @return bool True si se actualizó correctamente, false en caso contrario
     */
    public function update() {
        // Validar datos antes de actualizar
        if (!$this->validarDatos()) {
            return false;
        }
        
        // Verificar superposición de horarios
        if ($this->verificarSuperposicion()) {
            return false;
        }
        
        $query = "UPDATE " . $this->table_name . " 
                  SET id_odontologo = ?, dia_semana = ?, hora_inicio = ?, hora_fin = ? 
                  WHERE id_horario = ?";
        
        $stmt = $this->conn->prepare($query);
        
        // Limpiar datos
        $this->id_odontologo = htmlspecialchars(strip_tags($this->id_odontologo));
        $this->dia_semana = htmlspecialchars(strip_tags($this->dia_semana));
        $this->hora_inicio = htmlspecialchars(strip_tags($this->hora_inicio));
        $this->hora_fin = htmlspecialchars(strip_tags($this->hora_fin));
        $this->id_horario = htmlspecialchars(strip_tags($this->id_horario));
        
        // Vincular parámetros
        $stmt->bindParam(1, $this->id_odontologo);
        $stmt->bindParam(2, $this->dia_semana);
        $stmt->bindParam(3, $this->hora_inicio);
        $stmt->bindParam(4, $this->hora_fin);
        $stmt->bindParam(5, $this->id_horario);
        
        // Ejecutar consulta
        if ($stmt->execute()) {
            return true;
        }
        
        return false;
    }
    
    /**
     * Eliminar horario
     * 
     * @return bool True si se eliminó correctamente, false en caso contrario
     */
    public function delete() {
        $query = "DELETE FROM " . $this->table_name . " WHERE id_horario = ?";
        
        $stmt = $this->conn->prepare($query);
        
        // Limpiar dato
        $this->id_horario = htmlspecialchars(strip_tags($this->id_horario));
        
        // Vincular parámetro
        $stmt->bindParam(1, $this->id_horario);
        
        // Ejecutar consulta
        if ($stmt->execute()) {
            return true;
        }
        
        return false;
    }
    
    /**
     * Validar que los datos del horario sean coherentes
     * 
     * @return bool True si los datos son válidos, false en caso contrario
     */
    private function validarDatos() {
        // Verificar que el día de la semana sea válido
        if (!in_array($this->dia_semana, $this->dias_validos)) {
            return false;
        }
        
        // Verificar que la hora de inicio sea anterior a la hora de fin
        if ($this->hora_inicio >= $this->hora_fin) {
            return false;
        }
        
        return true;
    }
    
    // Getters y setters
    
    public function getIdHorario() {
        return $this->id_horario;
    }
    
    public function setIdHorario($id_horario) {
        $this->id_horario = $id_horario;
    }
    
    public function getIdOdontologo() {
        return $this->id_odontologo;
    }
    
    public function setIdOdontologo($id_odontologo) {
        $this->id_odontologo = $id_odontologo;
    }
    
    public function getDiaSemana() {
        return $this->dia_semana;
    }
    
    public function setDiaSemana($dia_semana) {
        $this->dia_semana = $dia_semana;
    }
    
    public function getHoraInicio() {
        return $this->hora_inicio;
    }
    
    public function setHoraInicio($hora_inicio) {
        $this->hora_inicio = $hora_inicio;
    }
    
    public function getHoraFin() {
        return $this->hora_fin;
    }
    
    public function setHoraFin($hora_fin) {
        $this->hora_fin = $hora_fin;
    }
    
    /**
     * Obtener array con los días de la semana válidos
     * 
     * @return array Array de días de la semana
     */
    public function getDiasValidos() {
        return $this->dias_validos;
    }
}
?>