<?php
class Database {
    private $host = DB_HOST;
    private $user = DB_USER;
    private $pass = DB_PASS;
    private $dbname = sistema_odontologico;

    private $dbh;
    private $stmt;
    private $error;

    public function __construct() {
        // Configurar DSN
        $dsn = 'mysql:host=' . $this->host . ';dbname=' . $this->dbname . ';charset=utf8';
        $options = array(
            PDO::ATTR_PERSISTENT => true,
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_OBJ
        );

        // Crear instancia PDO
        try {
            $this->dbh = new PDO($dsn, $this->user, $this->pass, $options);
        } catch(PDOException $e) {
            $this->error = $e->getMessage();
            echo 'Error de conexión: ' . $this->error;
        }
    }

    // Preparar sentencia
    public function query($sql) {
        $this->stmt = $this->dbh->prepare($sql);
    }

    // Vincular valores
    public function bind($param, $value, $type = null) {
        if(is_null($type)) {
            switch(true) {
                case is_int($value):
                    $type = PDO::PARAM_INT;
                    break;
                case is_bool($value):
                    $type = PDO::PARAM_BOOL;
                    break;
                case is_null($value):
                    $type = PDO::PARAM_NULL;
                    break;
                default:
                    $type = PDO::PARAM_STR;
            }
        }
        $this->stmt->bindValue($param, $value, $type);
    }

    // Ejecutar la sentencia
    public function execute() {
        return $this->stmt->execute();
    }

    // Obtener múltiples registros como objetos
    public function resultSet() {
        $this->execute();
        return $this->stmt->fetchAll();
    }

    // Obtener un solo registro como objeto
    public function single() {
        $this->execute();
        return $this->stmt->fetch();
    }

    // Obtener cantidad de filas
    public function rowCount() {
        return $this->stmt->rowCount();
    }

    // Obtener último ID insertado
    public function lastInsertId() {
        return $this->dbh->lastInsertId();
    }

    // Iniciar transacción
    public function beginTransaction() {
        return $this->dbh->beginTransaction();
    }

    // Confirmar transacción
    public function commit() {
        return $this->dbh->commit();
    }

    // Revertir transacción
    public function rollBack() {
        return $this->dbh->rollBack();
    }
}
?>

