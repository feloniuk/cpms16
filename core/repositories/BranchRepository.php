<?php

require_once __DIR__ . '/BaseRepository.php';

class BranchRepository extends BaseRepository {
    protected $table = 'branches';
    
    public function getActive() {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE is_active = 1 ORDER BY name");
        $stmt->execute();
        return $stmt->fetchAll();
    }
    
    public function getAll() {
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
    
    public function findByName($name) {
        return $this->findOneBy('name', $name);
    }
    
    public function exists($name, $excludeId = null) {
        $sql = "SELECT COUNT(*) as count FROM {$this->table} WHERE name = ?";
        $params = [$name];
        
        if ($excludeId) {
            $sql .= " AND id != ?";
            $params[] = $excludeId;
        }
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $result = $stmt->fetch();
        
        return $result['count'] > 0;
    }
}