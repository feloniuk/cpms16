<?php

class SessionManager {
    private static $instance = null;
    private $sessions = [];
    
    private function __construct() {
        // Инициализация
    }
    
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    public function startSession($telegram_id) {
        if (!isset($this->sessions[$telegram_id])) {
            $this->sessions[$telegram_id] = [
                'current_state' => null,
                'temp_data' => [],
                'last_activity' => time()
            ];
        }
        $this->sessions[$telegram_id]['last_activity'] = time();
    }
    
    public function setState($telegram_id, $state) {
        $this->startSession($telegram_id);
        $this->sessions[$telegram_id]['current_state'] = $state;
        $this->logMessage("Set state for user $telegram_id: $state");
    }
    
    public function getState($telegram_id) {
        $this->startSession($telegram_id);
        return $this->sessions[$telegram_id]['current_state'];
    }
    
    public function setTempData($telegram_id, $key, $value) {
        $this->startSession($telegram_id);
        $this->sessions[$telegram_id]['temp_data'][$key] = $value;
        $this->logMessage("Set temp data for user $telegram_id: $key = $value");
    }
    
    public function getTempData($telegram_id, $key = null) {
        $this->startSession($telegram_id);
        if ($key === null) {
            return $this->sessions[$telegram_id]['temp_data'];
        }
        return $this->sessions[$telegram_id]['temp_data'][$key] ?? null;
    }
    
    public function clearSession($telegram_id) {
        if (isset($this->sessions[$telegram_id])) {
            unset($this->sessions[$telegram_id]);
            $this->logMessage("Cleared session for user $telegram_id");
        }
    }
    
    public function clearOldSessions($timeout = 3600) {
        $current_time = time();
        foreach ($this->sessions as $telegram_id => $session) {
            if (($current_time - $session['last_activity']) > $timeout) {
                unset($this->sessions[$telegram_id]);
                $this->logMessage("Cleared old session for user $telegram_id");
            }
        }
    }
    
    public function getSessionInfo($telegram_id) {
        if (!isset($this->sessions[$telegram_id])) {
            return null;
        }
        return $this->sessions[$telegram_id];
    }
    
    public function getAllActiveSessions() {
        return $this->sessions;
    }
    
    private function logMessage($message) {
        $logEntry = date('Y-m-d H:i:s') . " - SESSION: " . $message . "\n";
        file_put_contents(__DIR__ . '/../logs/sessions.log', $logEntry, FILE_APPEND | LOCK_EX);
    }
}