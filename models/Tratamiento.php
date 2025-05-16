<?php

class Tratamiento {
    
    private $id_tratamiento;
    private $nombre;
    private $descripcion;
    private $precio;
    
    
    private $conn;
    private $table_name = "tratamientos";
    
    /**
     * Constructor - Inicializa la conexión a la base de datos
     * 
     * @param object $db Conexión a la base de datos
     */
    public function __construct($db) {
        $this->conn = $db;
    }
    
    /**
     * Obtener todos los tratamientos
     * 
     * @return PDOStatement Resultado de la consulta
     */
    public function getAll() {
        $query = "SELECT id_tratamiento, nombre, descripcion, precio 
                  FROM " . $this->table_name . " 
                  ORDER BY nombre";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        
        return $stmt;
    }
    
    /**
     * Obtener un tratamiento por ID
     * 
     * @param int $id ID del tratamiento a buscar
     * @return bool True si el tratamiento existe, false en caso contrario
     */
    public function getById($id) {
        $query = "SELECT id_tratamiento, nombre, descripcion, precio 
                  FROM " . $this->table_name . " 
                  WHERE id_tratamiento = ?";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $id);
        $stmt->execute();
        
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($row) {
            $this->id_tratamiento = $row['id_tratamiento'];
            $this->nombre = $row['nombre'];
            $this->descripcion = $row['descripcion'];
            $this->precio = $row['precio'];
            return true;
        }
        
        return false;
    }
    
    /**
     * Buscar tratamientos por nombre o descripción
     * 
     * @param string $keyword Palabra clave a buscar
     * @return PDOStatement Resultado de la consulta
     */
    public function search($keyword) {
        $query = "SELECT id_tratamiento, nombre, descripcion, precio 
                  FROM " . $this->table_name . " 
                  WHERE nombre LIKE ? OR descripcion LIKE ?
                  ORDER BY nombre";
        
        $keyword = "%{$keyword}%";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $keyword);
        $stmt->bindParam(2, $keyword);
        $stmt->execute();
        
        return $stmt;
    }
    
    /**
     * Crear un nuevo tratamiento
     * 
     * @return bool True si se creó correctamente, false en caso contrario
     */
    public function create() {
        // Validar datos antes de insertar
        if (!$this->validarDatos()) {
            return false;
        }
        
        $query = "INSERT INTO " . $this->table_name . " 
                  (nombre, descripcion, precio) 
                  VALUES (?, ?, ?)";
        
        $stmt = $this->conn->prepare($query);
        
        // Limpiar datos
        $this->nombre = htmlspecialchars(strip_tags($this->nombre));
        $this->descripcion = htmlspecialchars(strip_tags($this->descripcion));
        $this->precio = htmlspecialchars(strip_tags($this->precio));
        
        // Vincular parámetros
        $stmt->bindParam(1, $this->nombre);
        $stmt->bindParam(2, $this->descripcion);
        $stmt->bindParam(3, $this->precio);
        
        // Ejecutar consulta
        if ($stmt->execute()) {
            $this->id_tratamiento = $this->conn->lastInsertId();
            return true;
        }
        
        return false;
    }
    
    /**
     * Actualizar tratamiento existente
     * 
     * @return bool True si se actualizó correctamente, false en caso contrario
     */
    public function update() {
        // Validar datos antes de actualizar
        if (!$this->validarDatos()) {
            return false;
        }
        
        $query = "UPDATE " . $this->table_name . " 
                  SET nombre = ?, descripcion = ?, precio = ? 
                  WHERE id_tratamiento = ?";
        
        $stmt = $this->conn->prepare($query);
        
        // Limpiar datos
        $this->nombre = htmlspecialchars(strip_tags($this->nombre));
        $this->descripcion = htmlspecialchars(strip_tags($this->descripcion));
        $this->precio = htmlspecialchars(strip_tags($this->precio));
        $this->id_tratamiento = htmlspecialchars(strip_tags($this->id_tratamiento));
        
        // Vincular parámetros
        $stmt->bindParam(1, $this->nombre);
        $stmt->bindParam(2, $this->descripcion);
        $stmt->bindParam(3, $this->precio);
        $stmt->bindParam(4, $this->id_tratamiento);
        
        // Ejecutar consulta
        if ($stmt->execute()) {
            return true;
        }
        
        return false;
    }
    
    /**
     * Eliminar tratamiento
     * 
     * @return bool True si se eliminó correctamente, false en caso contrario
     */
    public function delete() {
        $query = "DELETE FROM " . $this->table_name . " WHERE id_tratamiento = ?";
        
        $stmt = $this->conn->prepare($query);
        
        // Limpiar dato
        $this->id_tratamiento = htmlspecialchars(strip_tags($this->id_tratamiento));
        
        // Vincular parámetro
        $stmt->bindParam(1, $this->id_tratamiento);
        
        // Ejecutar consulta
        if ($stmt->execute()) {
            return true;
        }
        
        return false;
    }
    
    /**
     * Verificar si el tratamiento está siendo utilizado en citas
     * 
     * @return bool True si está siendo utilizado, false en caso contrario
     */
    public function estaEnUso() {
        $query = "SELECT COUNT(*) as total FROM citas_tratamientos 
                  WHERE id_tratamiento = ?";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->id_tratamiento);
        $stmt->execute();
        
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return ($row['total'] > 0);
    }
    
    /**
     * Validar que los datos del tratamiento sean coherentes
     * 
     * @return bool True si los datos son válidos, false en caso contrario
     */
    private function validarDatos() {
        // Verificar que el nombre no esté vacío
        if (empty($this->nombre)) {
            return false;
        }
        
        // Verificar que el precio sea un número positivo
        if (!is_numeric($this->precio) || $this->precio <= 0) {
            return false;
        }
        
        return true;
    }
    
    /**
     * Formatear el precio para mostrar
     * 
     * @return string Precio formateado
     */
    public function getPrecioFormateado() {
        return number_format($this->precio, 2, ',', '.');
    }
    
    // Getters y setters
    
    public function getIdTratamiento() {
        return $this->id_tratamiento;
    }
    
    public function setIdTratamiento($id_tratamiento) {
        $this->id_tratamiento = $id_tratamiento;
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
    
    public function getPrecio() {
        return $this->precio;
    }
    
    public function setPrecio($precio) {
        $this->precio = $precio;
    }
}
?>