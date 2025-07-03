<?php

require_once __DIR__ . '/BaseRepository.php';

class UserStateRepository extends BaseRepository {
    protected $table = 'user_states';
    
    public function getUserState($telegram_id) {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE telegram_id = ?");
        $stmt->execute([$telegram_id]);
        $result = $stmt->fetch();
        
        if ($result && $result['temp_data']) {
            $result['temp_data'] = json_decode($result['temp_data'], true);
        }
        
        return $result;
    }
    
    public function setState($telegram_id, $state, $temp_data = null) {
        $existing = $this->getUserState($telegram_id);
        
        $data = [
            'current_state' => $state,
            'temp_data' => $temp_data ? json_encode($temp_data) : null,
            'updated_at' => date('Y-m-d H:i:s')
        ];
        
        if ($existing) {
            $stmt = $this->db->prepare("UPDATE {$this->table} SET current_state = ?, temp_data = ?, updated_at = ? WHERE telegram_id = ?");
            return $stmt->execute([$state, $data['temp_data'], $data['updated_at'], $telegram_id]);
        } else {
            $stmt = $this->db->prepare("INSERT INTO {$this->table} (telegram_id, current_state, temp_data, updated_at) VALUES (?, ?, ?, ?)");
            return $stmt->execute([$telegram_id, $state, $data['temp_data'], $data['updated_at']]);
        }
    }
    
    public function clearState($telegram_id) {
        return $this->setState($telegram_id, null, null);
    }
    
    public function updateTempData($telegram_id, $temp_data) {
        $current = $this->getUserState($telegram_id);
        if ($current) {
            $stmt = $this->db->prepare("UPDATE {$this->table} SET temp_data = ?, updated_at = ? WHERE telegram_id = ?");
            return $stmt->execute([json_encode($temp_data), date('Y-m-d H:i:s'), $telegram_id]);
        }
        return false;
    }
    
    public function getTempData($telegram_id) {
        $state = $this->getUserState($telegram_id);
        return $state ? $state['temp_data'] : null;
    }
    
    public function addToTempData($telegram_id, $key, $value) {
        $current = $this->getUserState($telegram_id);
        $temp_data = $current ? $current['temp_data'] : [];
        
        if (!is_array($temp_data)) {
            $temp_data = [];
        }
        
        $temp_data[$key] = $value;
        return $this->updateTempData($telegram_id, $temp_data);
    }
    
    public function clearOldStates($hours = 24) {
        $stmt = $this->db->prepare("DELETE FROM {$this->table} WHERE updated_at < ?");
        $cutoff = date('Y-m-d H:i:s', strtotime("-{$hours} hours"));
        return $stmt->execute([$cutoff]);
    }
}