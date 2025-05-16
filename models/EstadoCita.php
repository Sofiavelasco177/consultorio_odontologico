<?php

class EstadoCitas {
    
    private $id_estado;
    private $nombre;
    private $descripcion;
    
    // Conexión a la base de datos
    private $conn;
    private $table_name = "estados_cita";
    
    /**
     * Constructor - Inicializa la conexión a la base de datos
     * 
     * @param object $db Conexión a la base de datos
     */
    public function __construct($db) {
        $this->conn = $db;
    }
    
    /**
     * Obtener todos los estados de cita
     * 
     * @return PDOStatement Resultado de la consulta
     */
    public function getAll() {
        $query = "SELECT id_estado, nombre, descripcion FROM " . $this->table_name;
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        
        return $stmt;
    }
    
    /**
     * Obtener un estado de cita por ID
     * 
     * @param int $id ID del estado a buscar
     * @return bool True si el estado existe, false en caso contrario
     */
    public function getById($id) {
        $query = "SELECT id_estado, nombre, descripcion 
                  FROM " . $this->table_name . " 
                  WHERE id_estado = ?";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $id);
        $stmt->execute();
        
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($row) {
            $this->id_estado = $row['id_estado'];
            $this->nombre = $row['nombre'];
            $this->descripcion = $row['descripcion'];
            return true;
        }
        
        return false;
    }
    
    /**
     * Crear un nuevo estado de cita
     * 
     * @return bool True si se creó correctamente, false en caso contrario
     */
    public function create() {
        $query = "INSERT INTO " . $this->table_name . " 
                  (nombre, descripcion) 
                  VALUES (?, ?)";
        
        $stmt = $this->conn->prepare($query);
        
        // Limpiar datos
        $this->nombre = htmlspecialchars(strip_tags($this->nombre));
        $this->descripcion = htmlspecialchars(strip_tags($this->descripcion));
        
        // Vincular parámetros
        $stmt->bindParam(1, $this->nombre);
        $stmt->bindParam(2, $this->descripcion);
        
        // Ejecutar consulta
        if ($stmt->execute()) {
            $this->id_estado = $this->conn->lastInsertId();
            return true;
        }
        
        return false;
    }
    
    /**
     * Actualizar estado de cita existente
     * 
     * @return bool True si se actualizó correctamente, false en caso contrario
     */
    public function update() {
        $query = "UPDATE " . $this->table_name . " 
                  SET nombre = ?, descripcion = ? 
                  WHERE id_estado = ?";
        
        $stmt = $this->conn->prepare($query);
        
        // Limpiar datos
        $this->nombre = htmlspecialchars(strip_tags($this->nombre));
        $this->descripcion = htmlspecialchars(strip_tags($this->descripcion));
        $this->id_estado = htmlspecialchars(strip_tags($this->id_estado));
        
        // Vincular parámetros
        $stmt->bindParam(1, $this->nombre);
        $stmt->bindParam(2, $this->descripcion);
        $stmt->bindParam(3, $this->id_estado);
        
        // Ejecutar consulta
        if ($stmt->execute()) {
            return true;
        }
        
        return false;
    }
    
    /**
     * Eliminar estado de cita
     * 
     * @return bool True si se eliminó correctamente, false en caso contrario
     */
    public function delete() {
        $query = "DELETE FROM " . $this->table_name . " WHERE id_estado = ?";
        
        $stmt = $this->conn->prepare($query);
        
        // Limpiar dato
        $this->id_estado = htmlspecialchars(strip_tags($this->id_estado));
        
        // Vincular parámetro
        $stmt->bindParam(1, $this->id_estado);
        
        // Ejecutar consulta
        if ($stmt->execute()) {
            return true;
        }
        
        return false;
    }
    
    // Getters y setters
    
    public function getId() {
        return $this->id_estado;
    }
    
    public function setId($id_estado) {
        $this->id_estado = $id_estado;
    }
    
    public function getNombre() {
        return $this->nombre;
    }
    
    public function setNombre($nombre) {
        $this->nombre = $nombre;
    }
    
    public function getDescripcion() {
        return $this->descripcion;
    }
    
    public function setDescripcion($descripcion) {
        $this->descripcion = $descripcion;
    }
}
?>