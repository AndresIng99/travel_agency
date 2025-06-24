<?php
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
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }

    public function fetchAll($sql, $params = []) {
        return $this->query($sql, $params)->fetchAll();
    }

    public function fetch($sql, $params = []) {
        return $this->query($sql, $params)->fetch();
    }

    public function insert($table, $data) {
        $keys = implode(',', array_keys($data));
        $placeholders = ':' . implode(', :', array_keys($data));
        
        $sql = "INSERT INTO {$table} ({$keys}) VALUES ({$placeholders})";
        $this->query($sql, $data);
        
        return $this->pdo->lastInsertId();
    }

    public function update($table, $data, $condition, $params = []) {
        $setClause = implode(', ', array_map(fn($key) => "{$key} = :{$key}", array_keys($data)));
        
        $sql = "UPDATE {$table} SET {$setClause} WHERE {$condition}";
        $allParams = array_merge($data, $params);
        
        return $this->query($sql, $allParams)->rowCount();
    }
}
?>
