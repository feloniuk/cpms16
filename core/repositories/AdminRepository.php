<?php

require_once __DIR__ . '/BaseRepository.php';

class AdminRepository extends BaseRepository {
    protected $table = 'admins';
    
    public function isAdmin($telegram_id) {
        $stmt = $this->db->prepare("SELECT id FROM {$this->table} WHERE telegram_id = ? AND is_active = 1");
        $stmt->execute([$telegram_id]);
        return $stmt->fetch() !== false;
    }
    
    public function getByTelegramId($telegram_id) {
        return $this->findOneBy('telegram_id', $telegram_id);
    }
    
    public function getActiveAdmins() {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE is_active = 1 ORDER BY name");
        $stmt->execute();
        return $stmt->fetchAll();
    }
    
    public function getAllAdmins() {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} ORDER BY name");
        $stmt->execute();
        return $stmt->fetchAll();
    }
    
    public function activate($id) {
        return $this->update($id, ['is_active' => 1]);
    }
    
    public function deactivate($id) {
        return $this->update($id, ['is_active' => 0]);
    }
    
    public function exists($telegram_id, $excludeId = null) {
        $sql = "SELECT COUNT(*) as count FROM {$this->table} WHERE telegram_id = ?";
        $params = [$telegram_id];
        
        if ($excludeId) {
            $sql .= " AND id != ?";
            $params[] = $excludeId;
        }
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $result = $stmt->fetch();
        
        return $result['count'] > 0;
    }
    
    public function addAdmin($telegram_id, $name) {
        return $this->create([
            'telegram_id' => $telegram_id,
            'name' => $name,
            'is_active' => 1
        ]);
    }
}