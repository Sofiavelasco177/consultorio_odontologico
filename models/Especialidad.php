<?php

    class Especialidad{

    // Propiedades
    private $conn;
    private $table_name = "especialidades";
    
    // Atributos
    public $id_especialidad;
    public $nombre;
    public $descripcion;
    
    /**
     * Constructor
     * 
     * @param PDO $db Conexión a la base de datos
     */
    public function __construct($db)
    {
        $this->conn = $db;
    }
    
    /**
     * Obtener todas las especialidades
     * 
     * @return PDOStatement
     */
    public function getAll()
    {
        $query = "SELECT id_especialidad, nombre, descripcion FROM " . $this->table_name . " ORDER BY nombre";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        
        return $stmt;
    }
    
    /**
     * Obtener una especialidad específica por ID
     * 
     * @param int $id ID de la especialidad
     * @return bool True si se encontró la especialidad, False si no
     */
    public function getById($id)
    {
        $query = "SELECT id_especialidad, nombre, descripcion FROM " . $this->table_name . " WHERE id_especialidad = ? LIMIT 0,1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $id);
        $stmt->execute();
        
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($row) {
            $this->id_especialidad = $row["id_especialidad"];
            $this->nombre = $row["nombre"];
            $this->descripcion = $row["descripcion"];
            return true;
        }
        
        return false;
    }
    
    /**
     * Crear una nueva especialidad
     * 
     * @return bool True si se creó la especialidad, False si no
     */
    public function create()
    {
        $query = "INSERT INTO " . $this->table_name . " (nombre, descripcion) VALUES (?, ?)";
        $stmt = $this->conn->prepare($query);
        
        // Sanitizar datos
        $this->nombre = htmlspecialchars(strip_tags($this->nombre));
        $this->descripcion = htmlspecialchars(strip_tags($this->descripcion));
        
        // Vincular parámetros
        $stmt->bindParam(1, $this->nombre);
        $stmt->bindParam(2, $this->descripcion);
        
        // Ejecutar query
        if ($stmt->execute()) {
            $this->id_especialidad = $this->conn->lastInsertId();
            return true;
        }
        
        return false;
    }
    
    /**
     * Actualizar una especialidad existente
     * 
     * @return bool True si se actualizó la especialidad, False si no
     */
    public function update()
    {
        $query = "UPDATE " . $this->table_name . " SET nombre = ?, descripcion = ? WHERE id_especialidad = ?";
        $stmt = $this->conn->prepare($query);
        
        // Sanitizar datos
        $this->nombre = htmlspecialchars(strip_tags($this->nombre));
        $this->descripcion = htmlspecialchars(strip_tags($this->descripcion));
        $this->id_especialidad = htmlspecialchars(strip_tags($this->id_especialidad));
        
        // Vincular parámetros
        $stmt->bindParam(1, $this->nombre);
        $stmt->bindParam(2, $this->descripcion);
        $stmt->bindParam(3, $this->id_especialidad);
        
        // Ejecutar query
        if ($stmt->execute()) {
            return true;
        }
        
        return false;
    }
    
    /**
     * Eliminar una especialidad
     * 
     * @return bool True si se eliminó la especialidad, False si no
     */
    public function delete()
    {
        $query = "DELETE FROM " . $this->table_name . " WHERE id_especialidad = ?";
        $stmt = $this->conn->prepare($query);
        
        // Sanitizar dato
        $this->id_especialidad = htmlspecialchars(strip_tags($this->id_especialidad));
        
        // Vincular parámetro
        $stmt->bindParam(1, $this->id_especialidad);
        
        // Ejecutar query
        if ($stmt->execute()) {
            return true;
        }
        
        return false;
    }
    
    /**
     * Buscar especialidades por nombre
     * 
     * @param string $keyword Palabra clave para buscar
     * @return PDOStatement
     */
    public function search($keyword)
    {
        $query = "SELECT id_especialidad, nombre, descripcion FROM " . $this->table_name . " 
                  WHERE nombre LIKE ? OR descripcion LIKE ? 
                  ORDER BY nombre";
        
        $stmt = $this->conn->prepare($query);
        
        // Sanitizar dato
        $keyword = htmlspecialchars(strip_tags($keyword));
        $keyword = "%{$keyword}%";
        
        // Vincular parámetros
        $stmt->bindParam(1, $keyword);
        $stmt->bindParam(2, $keyword);
        
        // Ejecutar query
        $stmt->execute();
        
        return $stmt;
    }
}
?>