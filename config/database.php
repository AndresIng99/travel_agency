<?php
// =====================================
// ARCHIVO: config/database.php - CORREGIDO PARA EVITAR MEZCLA DE PARÁMETROS
// =====================================

class Database {
    private static $instance = null;
    private $pdo;

    private function __construct() {
        $this->loadEnv();
        $this->connect();
    }

    private function loadEnv() {
        if (file_exists(__DIR__ . '/../.env')) {
            $lines = file(__DIR__ . '/../.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            foreach ($lines as $line) {
                if (strpos($line, '=') !== false && strpos($line, '#') !== 0) {
                    list($key, $value) = explode('=', $line, 2);
                    $_ENV[trim($key)] = trim($value);
                }
            }
        }
    }

    private function connect() {
        $host = $_ENV['DB_HOST'] ?? 'localhost';
        $dbname = $_ENV['DB_NAME'] ?? 'travel_agency';
        $username = $_ENV['DB_USER'] ?? 'root';
        $password = $_ENV['DB_PASS'] ?? '';
        $charset = $_ENV['DB_CHARSET'] ?? 'utf8mb4';

        $dsn = "mysql:host={$host};dbname={$dbname};charset={$charset}";
        
        try {
            $this->pdo = new PDO($dsn, $username, $password, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false
            ]);
        } catch (PDOException $e) {
            die("Error de conexión: " . $e->getMessage());
        }
    }

    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function getConnection() {
        return $this->pdo;
    }

    public function query($sql, $params = []) {
        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            return $stmt;
        } catch (PDOException $e) {
            error_log("Database query error: " . $e->getMessage());
            error_log("SQL: " . $sql);
            error_log("Params: " . print_r($params, true));
            throw $e;
        }
    }

    public function fetchAll($sql, $params = []) {
        return $this->query($sql, $params)->fetchAll();
    }

    public function fetch($sql, $params = []) {
        return $this->query($sql, $params)->fetch();
    }

    public function insert($table, $data) {
        if (empty($data)) {
            throw new Exception("No data provided for insert");
        }
        
        $keys = array_keys($data);
        $values = array_values($data);
        
        $placeholders = str_repeat('?,', count($keys) - 1) . '?';
        $keysStr = '`' . implode('`, `', $keys) . '`';
        
        $sql = "INSERT INTO `{$table}` ({$keysStr}) VALUES ({$placeholders})";
        
        try {
            error_log("INSERT SQL: " . $sql);
            error_log("INSERT Values: " . print_r($values, true));
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($values);
            
            return $this->pdo->lastInsertId();
        } catch (PDOException $e) {
            error_log("Insert error: " . $e->getMessage());
            error_log("SQL: " . $sql);
            error_log("Data: " . print_r($data, true));
            throw $e;
        }
    }

    public function update($table, $data, $condition, $conditionParams = []) {
        if (empty($data)) {
            throw new Exception("No data provided for update");
        }
        
        // Construir SET clause con placeholders posicionales
        $setParts = [];
        $values = [];
        
        foreach ($data as $key => $value) {
            $setParts[] = "`{$key}` = ?";
            $values[] = $value;
        }
        
        $setClause = implode(', ', $setParts);
        
        // Agregar parámetros de condición al final
        $allParams = array_merge($values, $conditionParams);
        
        $sql = "UPDATE `{$table}` SET {$setClause} WHERE {$condition}";
        
        try {
            error_log("UPDATE SQL: " . $sql);
            error_log("UPDATE Params: " . print_r($allParams, true));
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($allParams);
            
            return $stmt->rowCount();
        } catch (PDOException $e) {
            error_log("Update error: " . $e->getMessage());
            error_log("SQL: " . $sql);
            error_log("Data: " . print_r($data, true));
            error_log("Condition: " . $condition);
            error_log("All Params: " . print_r($allParams, true));
            throw $e;
        }
    }

    public function delete($table, $condition, $params = []) {
        $sql = "DELETE FROM `{$table}` WHERE {$condition}";
        
        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            
            return $stmt->rowCount();
        } catch (PDOException $e) {
            error_log("Delete error: " . $e->getMessage());
            error_log("SQL: " . $sql);
            error_log("Params: " . print_r($params, true));
            throw $e;
        }
    }

    // Método auxiliar para transacciones
    public function beginTransaction() {
        return $this->pdo->beginTransaction();
    }

    public function commit() {
        return $this->pdo->commit();
    }

    public function rollback() {
        return $this->pdo->rollBack();
    }

    // Método para ejecutar múltiples queries en transacción
    public function transaction(callable $callback) {
        $this->beginTransaction();
        
        try {
            $result = $callback($this);
            $this->commit();
            return $result;
        } catch (Exception $e) {
            $this->rollback();
            throw $e;
        }
    }
}
?>