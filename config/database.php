<?php
// =====================================
// ARCHIVO: config/database.php - CLASE DATABASE COMPLETA Y CORREGIDA
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

    // ============================================
    // MÉTODOS BÁSICOS DE CONSULTA
    // ============================================

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

    public function fetchColumn($sql, $params = []) {
        return $this->query($sql, $params)->fetchColumn();
    }

    // ============================================
    // MÉTODO INSERT CORREGIDO
    // ============================================

    public function insert($table, $data) {
        if (empty($data)) {
            throw new Exception("No data provided for insert");
        }
        
        $keys = array_keys($data);
        $values = array_values($data);
        
        // Crear placeholders para los valores
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

    // ============================================
    // MÉTODO UPDATE CORREGIDO
    // ============================================

    public function update($table, $data, $where, $whereParams = []) {
        if (empty($data)) {
            throw new Exception("No data provided for update");
        }
        
        // Construir SET clause
        $setParts = [];
        $values = [];
        
        foreach ($data as $key => $value) {
            $setParts[] = "`{$key}` = ?";
            $values[] = $value;
        }
        
        $setClause = implode(', ', $setParts);
        
        // Construir WHERE clause
        $whereClause = '';
        $allParams = $values; // Empezar con los valores del SET
        
        if (is_array($where)) {
            // Si $where es un array asociativo, construir condiciones AND
            $whereConditions = [];
            foreach ($where as $key => $value) {
                $whereConditions[] = "`{$key}` = ?";
                $allParams[] = $value;
            }
            $whereClause = implode(' AND ', $whereConditions);
        } else {
            // Si $where es una string, usarla directamente
            $whereClause = $where;
            // Agregar parámetros de WHERE al final
            $allParams = array_merge($allParams, $whereParams);
        }
        
        $sql = "UPDATE `{$table}` SET {$setClause} WHERE {$whereClause}";
        
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
            error_log("Where: " . print_r($where, true));
            error_log("All Params: " . print_r($allParams, true));
            throw $e;
        }
    }

    // ============================================
    // MÉTODO DELETE CORREGIDO
    // ============================================

    public function delete($table, $where, $whereParams = []) {
        // Construir WHERE clause
        $whereClause = '';
        $allParams = [];
        
        if (is_array($where)) {
            // Si $where es un array asociativo, construir condiciones AND
            $whereConditions = [];
            foreach ($where as $key => $value) {
                $whereConditions[] = "`{$key}` = ?";
                $allParams[] = $value;
            }
            $whereClause = implode(' AND ', $whereConditions);
        } else {
            // Si $where es una string, usarla directamente
            $whereClause = $where;
            $allParams = $whereParams;
        }
        
        $sql = "DELETE FROM `{$table}` WHERE {$whereClause}";
        
        try {
            error_log("DELETE SQL: " . $sql);
            error_log("DELETE Params: " . print_r($allParams, true));
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($allParams);
            
            return $stmt->rowCount();
        } catch (PDOException $e) {
            error_log("Delete error: " . $e->getMessage());
            error_log("SQL: " . $sql);
            error_log("Params: " . print_r($allParams, true));
            throw $e;
        }
    }

    // ============================================
    // MÉTODOS DE TRANSACCIONES
    // ============================================

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

    // ============================================
    // MÉTODOS AUXILIARES
    // ============================================

    public function exists($table, $where, $whereParams = []) {
        $whereClause = '';
        $allParams = [];
        
        if (is_array($where)) {
            $whereConditions = [];
            foreach ($where as $key => $value) {
                $whereConditions[] = "`{$key}` = ?";
                $allParams[] = $value;
            }
            $whereClause = implode(' AND ', $whereConditions);
        } else {
            $whereClause = $where;
            $allParams = $whereParams;
        }
        
        $sql = "SELECT 1 FROM `{$table}` WHERE {$whereClause} LIMIT 1";
        $result = $this->fetch($sql, $allParams);
        
        return !empty($result);
    }

    public function count($table, $where = '', $whereParams = []) {
        $sql = "SELECT COUNT(*) as count FROM `{$table}`";
        
        if (!empty($where)) {
            $sql .= " WHERE {$where}";
        }
        
        $result = $this->fetch($sql, $whereParams);
        return (int)$result['count'];
    }

    public function getLastInsertId() {
        return $this->pdo->lastInsertId();
    }

    // ============================================
    // MÉTODOS PARA MANEJO DE ESQUEMAS
    // ============================================

    public function tableExists($tableName) {
        try {
            $result = $this->fetch("SHOW TABLES LIKE ?", [$tableName]);
            return !empty($result);
        } catch (Exception $e) {
            return false;
        }
    }

    public function createTable($sql) {
        try {
            $this->pdo->exec($sql);
            return true;
        } catch (PDOException $e) {
            error_log("Create table error: " . $e->getMessage());
            throw $e;
        }
    }

    public function dropTable($tableName) {
        try {
            $this->pdo->exec("DROP TABLE IF EXISTS `{$tableName}`");
            return true;
        } catch (PDOException $e) {
            error_log("Drop table error: " . $e->getMessage());
            throw $e;
        }
    }

    // ============================================
    // MÉTODOS DE UTILIDAD PARA DEBUGGING
    // ============================================

    public function getTableInfo($tableName) {
        try {
            return $this->fetchAll("DESCRIBE `{$tableName}`");
        } catch (Exception $e) {
            error_log("Get table info error: " . $e->getMessage());
            return [];
        }
    }

    public function getTables() {
        try {
            $tables = $this->fetchAll("SHOW TABLES");
            $tableNames = [];
            foreach ($tables as $table) {
                $tableNames[] = array_values($table)[0];
            }
            return $tableNames;
        } catch (Exception $e) {
            error_log("Get tables error: " . $e->getMessage());
            return [];
        }
    }

    // ============================================
    // MÉTODO PARA OBTENER ESTADÍSTICAS DEL SERVIDOR
    // ============================================

    public function getServerInfo() {
        try {
            return [
                'version' => $this->pdo->getAttribute(PDO::ATTR_SERVER_VERSION),
                'connection_status' => $this->pdo->getAttribute(PDO::ATTR_CONNECTION_STATUS),
                'driver_name' => $this->pdo->getAttribute(PDO::ATTR_DRIVER_NAME),
                'server_info' => $this->pdo->getAttribute(PDO::ATTR_SERVER_INFO)
            ];
        } catch (Exception $e) {
            error_log("Get server info error: " . $e->getMessage());
            return [];
        }
    }

    // ============================================
    // MÉTODO PARA PREPARAR DATOS DE INSERCIÓN/ACTUALIZACIÓN
    // ============================================

    public function sanitizeData($data, $allowedFields = []) {
        if (empty($allowedFields)) {
            return $data;
        }
        
        $sanitized = [];
        foreach ($allowedFields as $field) {
            if (isset($data[$field])) {
                $sanitized[$field] = $data[$field];
            }
        }
        
        return $sanitized;
    }

    // ============================================
    // DESTRUCTOR
    // ============================================

    public function __destruct() {
        $this->pdo = null;
    }
}
?>