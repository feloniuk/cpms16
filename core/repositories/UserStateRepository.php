<?php

require_once __DIR__ . '/BaseRepository.php';

class UserStateRepository extends BaseRepository {
    protected $table = 'user_states';
    
    public function getUserState($telegram_id) {
        try {
            $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE telegram_id = ?");
            $stmt->execute([$telegram_id]);
            $result = $stmt->fetch();
            
            if ($result && $result['temp_data']) {
                $result['temp_data'] = json_decode($result['temp_data'], true);
                if (json_last_error() !== JSON_ERROR_NONE) {
                    // Если JSON поврежден, сбрасываем temp_data
                    $result['temp_data'] = [];
                }
            }
            
            return $result;
            
        } catch (Exception $e) {
            error_log("Error getting user state: " . $e->getMessage());
            return null;
        }
    }
    
    public function setState($telegram_id, $state, $temp_data = null) {
        try {
            $existing = $this->getUserState($telegram_id);
            
            $data = [
                'current_state' => $state,
                'temp_data' => $temp_data ? json_encode($temp_data, JSON_UNESCAPED_UNICODE) : null,
                'updated_at' => date('Y-m-d H:i:s')
            ];
            
            if ($existing) {
                $stmt = $this->db->prepare("UPDATE {$this->table} SET current_state = ?, temp_data = ?, updated_at = ? WHERE telegram_id = ?");
                return $stmt->execute([$state, $data['temp_data'], $data['updated_at'], $telegram_id]);
            } else {
                $stmt = $this->db->prepare("INSERT INTO {$this->table} (telegram_id, current_state, temp_data, updated_at) VALUES (?, ?, ?, ?)");
                return $stmt->execute([$telegram_id, $state, $data['temp_data'], $data['updated_at']]);
            }
            
        } catch (Exception $e) {
            error_log("Error setting user state: " . $e->getMessage());
            return false;
        }
    }
    
    public function clearState($telegram_id) {
        try {
            $stmt = $this->db->prepare("DELETE FROM {$this->table} WHERE telegram_id = ?");
            return $stmt->execute([$telegram_id]);
        } catch (Exception $e) {
            error_log("Error clearing user state: " . $e->getMessage());
            return false;
        }
    }
    
    public function updateTempData($telegram_id, $temp_data) {
        try {
            $stmt = $this->db->prepare("UPDATE {$this->table} SET temp_data = ?, updated_at = ? WHERE telegram_id = ?");
            return $stmt->execute([json_encode($temp_data, JSON_UNESCAPED_UNICODE), date('Y-m-d H:i:s'), $telegram_id]);
        } catch (Exception $e) {
            error_log("Error updating temp data: " . $e->getMessage());
            return false;
        }
    }
    
    public function getTempData($telegram_id) {
        $state = $this->getUserState($telegram_id);
        return $state ? $state['temp_data'] : null;
    }
    
    public function addToTempData($telegram_id, $key, $value) {
        try {
            $current = $this->getUserState($telegram_id);
            $temp_data = $current ? $current['temp_data'] : [];
            
            if (!is_array($temp_data)) {
                $temp_data = [];
            }
            
            $temp_data[$key] = $value;
            
            // Если состояние не существует, создаем его
            if (!$current) {
                $stmt = $this->db->prepare("INSERT INTO {$this->table} (telegram_id, current_state, temp_data, updated_at) VALUES (?, NULL, ?, ?)");
                return $stmt->execute([$telegram_id, json_encode($temp_data, JSON_UNESCAPED_UNICODE), date('Y-m-d H:i:s')]);
            } else {
                return $this->updateTempData($telegram_id, $temp_data);
            }
            
        } catch (Exception $e) {
            error_log("Error adding to temp data: " . $e->getMessage());
            return false;
        }
    }
    
    public function removeFromTempData($telegram_id, $key) {
        try {
            $current = $this->getUserState($telegram_id);
            if (!$current || !is_array($current['temp_data'])) {
                return true;
            }
            
            $temp_data = $current['temp_data'];
            unset($temp_data[$key]);
            
            return $this->updateTempData($telegram_id, $temp_data);
            
        } catch (Exception $e) {
            error_log("Error removing from temp data: " . $e->getMessage());
            return false;
        }
    }
    
    public function clearOldStates($hours = 24) {
        try {
            $stmt = $this->db->prepare("DELETE FROM {$this->table} WHERE updated_at < ?");
            $cutoff = date('Y-m-d H:i:s', strtotime("-{$hours} hours"));
            return $stmt->execute([$cutoff]);
        } catch (Exception $e) {
            error_log("Error clearing old states: " . $e->getMessage());
            return false;
        }
    }
    
    public function getActiveStatesCount() {
        try {
            $stmt = $this->db->prepare("SELECT COUNT(*) as count FROM {$this->table} WHERE current_state IS NOT NULL");
            $stmt->execute();
            $result = $stmt->fetch();
            return $result['count'] ?? 0;
        } catch (Exception $e) {
            error_log("Error getting active states count: " . $e->getMessage());
            return 0;
        }
    }
    
    public function getStatesByType($state_prefix) {
        try {
            $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE current_state LIKE ?");
            $stmt->execute([$state_prefix . '%']);
            return $stmt->fetchAll();
        } catch (Exception $e) {
            error_log("Error getting states by type: " . $e->getMessage());
            return [];
        }
    }
}