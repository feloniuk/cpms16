<?php

require_once __DIR__ . '/BaseRepository.php';

class InventoryRepository extends BaseRepository {
    protected $table = 'room_inventory';
    
    public function searchByQuery($query) {
        $sql = "SELECT i.*, b.name as branch_name 
                FROM {$this->table} i 
                LEFT JOIN branches b ON i.branch_id = b.id 
                WHERE i.inventory_number LIKE ? 
                   OR i.serial_number LIKE ? 
                   OR i.equipment_type LIKE ? 
                   OR i.brand LIKE ? 
                   OR i.model LIKE ?
                ORDER BY i.created_at DESC
                LIMIT 20";
        
        $search_term = "%$query%";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$search_term, $search_term, $search_term, $search_term, $search_term]);
        return $stmt->fetchAll();
    }
    
    public function getByBranchAndRoom($branch_id, $room_number) {
        $sql = "SELECT i.*, b.name as branch_name 
                FROM {$this->table} i 
                LEFT JOIN branches b ON i.branch_id = b.id 
                WHERE i.branch_id = ? AND i.room_number = ?
                ORDER BY i.equipment_type, i.brand, i.model";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$branch_id, $room_number]);
        return $stmt->fetchAll();
    }
    
    public function getByInventoryNumber($inventory_number) {
        return $this->findOneBy('inventory_number', $inventory_number);
    }
    
    public function getBySerialNumber($serial_number) {
        return $this->findOneBy('serial_number', $serial_number);
    }
    
    public function getPrintersBySearch($search_term) {
        $sql = "SELECT i.*, b.name as branch_name 
                FROM {$this->table} i 
                LEFT JOIN branches b ON i.branch_id = b.id 
                WHERE i.equipment_type LIKE '%принтер%' 
                  AND (i.inventory_number LIKE ? OR i.serial_number LIKE ?)
                ORDER BY i.created_at DESC
                LIMIT 10";
        
        $search = "%$search_term%";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$search, $search]);
        return $stmt->fetchAll();
    }
    
    public function getInventoryByBranch($branch_id) {
        $sql = "SELECT * FROM {$this->table} 
                WHERE branch_id = ? 
                ORDER BY room_number, equipment_type";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$branch_id]);
        return $stmt->fetchAll();
    }
    
    public function getInventoryStats() {
        $sql = "SELECT 
                    COUNT(*) as total_items,
                    COUNT(DISTINCT branch_id) as total_branches,
                    COUNT(DISTINCT CONCAT(branch_id, '-', room_number)) as total_rooms,
                    COUNT(DISTINCT equipment_type) as total_types
                FROM {$this->table}";
        
        $stmt = $this->db->query($sql);
        return $stmt->fetch();
    }
    
    public function getInventoryByType() {
        $sql = "SELECT 
                    equipment_type,
                    COUNT(*) as count
                FROM {$this->table} 
                GROUP BY equipment_type 
                ORDER BY count DESC";
        
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll();
    }
    
    public function addEquipment($admin_telegram_id, $branch_id, $room_number, $equipment_type, $brand, $model, $serial_number, $inventory_number, $notes = null, $template_id = null) {
        return $this->create([
            'admin_telegram_id' => $admin_telegram_id,
            'branch_id' => $branch_id,
            'room_number' => $room_number,
            'template_id' => $template_id,
            'equipment_type' => $equipment_type,
            'brand' => $brand,
            'model' => $model,
            'serial_number' => $serial_number,
            'inventory_number' => $inventory_number,
            'notes' => $notes
        ]);
    }
    
    public function inventoryNumberExists($inventory_number, $exclude_id = null) {
        $sql = "SELECT COUNT(*) as count FROM {$this->table} WHERE inventory_number = ?";
        $params = [$inventory_number];
        
        if ($exclude_id) {
            $sql .= " AND id != ?";
            $params[] = $exclude_id;
        }
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $result = $stmt->fetch();
        
        return $result['count'] > 0;
    }
}