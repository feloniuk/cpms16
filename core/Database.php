<?php

class Database {
    private static $instance = null;
    private $pdo;
    
    private function __construct() {
        $config = require __DIR__ . '/../config/database.php';
        
        $dsn = "mysql:host={$config['host']};dbname={$config['dbname']};charset={$config['charset']}";
        
        try {
            $this->pdo = new PDO($dsn, $config['username'], $config['password'], $config['options']);
            
            // Проверка подключения
            $this->pdo->query("SELECT 1");
            
        } catch (PDOException $e) {
            error_log("Database connection failed: " . $e->getMessage());
            throw new Exception("Помилка підключення до бази даних: " . $e->getMessage());
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
    
    public function prepare($sql) {
        return $this->pdo->prepare($sql);
    }
    
    public function query($sql) {
        return $this->pdo->query($sql);
    }
    
    public function lastInsertId() {
        return $this->pdo->lastInsertId();
    }
    
    public function beginTransaction() {
        return $this->pdo->beginTransaction();
    }
    
    public function commit() {
        return $this->pdo->commit();
    }
    
    public function rollback() {
        return $this->pdo->rollback();
    }
    
    public function exec($sql) {
        return $this->pdo->exec($sql);
    }
    
    public function quote($string) {
        return $this->pdo->quote($string);
    }
    
    // Метод для проверки существования таблицы
    public function tableExists($tableName) {
        $stmt = $this->prepare("SHOW TABLES LIKE ?");
        $stmt->execute([$tableName]);
        return $stmt->fetch() !== false;
    }
    
    // Метод для получения информации о таблице
    public function getTableInfo($tableName) {
        $stmt = $this->prepare("DESCRIBE $tableName");
        $stmt->execute();
        return $stmt->fetchAll();
    }
    
    // Метод для безопасного выполнения запросов
    public function safeQuery($sql, $params = []) {
        try {
            $stmt = $this->prepare($sql);
            $stmt->execute($params);
            return $stmt;
        } catch (PDOException $e) {
            error_log("Database query failed: " . $e->getMessage() . " SQL: " . $sql);
            throw new Exception("Помилка виконання запиту до бази даних");
        }
    }
    
    // Метод для получения одной записи
    public function fetchOne($sql, $params = []) {
        $stmt = $this->safeQuery($sql, $params);
        return $stmt->fetch();
    }
    
    // Метод для получения всех записей
    public function fetchAll($sql, $params = []) {
        $stmt = $this->safeQuery($sql, $params);
        return $stmt->fetchAll();
    }
    
    // Метод для получения количества записей
    public function fetchCount($sql, $params = []) {
        $stmt = $this->safeQuery($sql, $params);
        $result = $stmt->fetch();
        return $result['count'] ?? 0;
    }
}