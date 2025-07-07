<?php

require_once __DIR__ . '/BaseRepository.php';

class CartridgeRepository extends BaseRepository {
    protected $table = 'cartridge_replacements';
    
    public function getWithBranches($limit = 20, $offset = 0) {
        $sql = "SELECT c.*, b.name as branch_name 
                FROM {$this->table} c 
                LEFT JOIN branches b ON c.branch_id = b.id 
                ORDER BY c.created_at DESC 
                LIMIT ? OFFSET ?";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$limit, $offset]);
        return $stmt->fetchAll();
    }
    
    public function getByBranch($branch_id, $limit = null) {
        $sql = "SELECT c.*, b.name as branch_name 
                FROM {$this->table} c 
                LEFT JOIN branches b ON c.branch_id = b.id 
                WHERE c.branch_id = ? 
                ORDER BY c.replacement_date DESC";
        
        if ($limit) {
            $sql .= " LIMIT ?";
        }
        
        $stmt = $this->db->prepare($sql);
        $params = [$branch_id];
        
        if ($limit) {
            $params[] = $limit;
        }
        
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
    
    public function getByDateRange($start_date, $end_date) {
        $sql = "SELECT c.*, b.name as branch_name 
                FROM {$this->table} c 
                LEFT JOIN branches b ON c.branch_id = b.id 
                WHERE c.replacement_date BETWEEN ? AND ? 
                ORDER BY c.replacement_date DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$start_date, $end_date]);
        return $stmt->fetchAll();
    }
    
    public function getByPrinter($printer_inventory_id) {
        $sql = "SELECT c.*, b.name as branch_name 
                FROM {$this->table} c 
                LEFT JOIN branches b ON c.branch_id = b.id 
                WHERE c.printer_inventory_id = ? 
                ORDER BY c.replacement_date DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$printer_inventory_id]);
        return $stmt->fetchAll();
    }
    
    public function getStatsByBranch() {
        $sql = "SELECT 
                    b.name as branch_name,
                    COUNT(*) as total_replacements,
                    COUNT(DISTINCT c.cartridge_type) as unique_cartridge_types
                FROM {$this->table} c 
                LEFT JOIN branches b ON c.branch_id = b.id 
                GROUP BY c.branch_id, b.name 
                ORDER BY total_replacements DESC";
        
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll();
    }
    
    public function getStatsByCartridgeType() {
        $sql = "SELECT 
                    cartridge_type,
                    COUNT(*) as count
                FROM {$this->table} 
                GROUP BY cartridge_type 
                ORDER BY count DESC";
        
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll();
    }
    
    public function getRecentReplacements($limit = 10) {
        $sql = "SELECT c.*, b.name as branch_name 
                FROM {$this->table} c 
                LEFT JOIN branches b ON c.branch_id = b.id 
                ORDER BY c.created_at DESC 
                LIMIT ?";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$limit]);
        return $stmt->fetchAll();
    }
    
    public function createReplacement($user_telegram_id, $username, $branch_id, $room_number, $printer_inventory_id, $printer_info, $cartridge_type, $replacement_date = null, $notes = null) {
        $replacement_date = $replacement_date ?: date('Y-m-d');
        
        return $this->create([
            'user_telegram_id' => $user_telegram_id,
            'username' => $username,
            'branch_id' => $branch_id,
            'room_number' => $room_number,
            'printer_inventory_id' => $printer_inventory_id,
            'printer_info' => $printer_info,
            'cartridge_type' => $cartridge_type,
            'replacement_date' => $replacement_date,
            'notes' => $notes
        ]);
    }
}