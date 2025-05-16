<?php
class Rol {
    private $db;

    public function __construct() {
        $this->db = new Database;
    }

    public function getRoles() {
        $this->db->query("SELECT * FROM roles ORDER BY id_rol");
        return $this->db->resultSet();
    }

    public function getRoleById($id) {
        $this->db->query("SELECT * FROM roles WHERE id_rol = :id");
        $this->db->bind(':id', $id);
        return $this->db->single();
    }
}
?>
