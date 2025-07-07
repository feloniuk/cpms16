<?php

require_once __DIR__ . '/BaseRepository.php';

class RepairRepository extends BaseRepository {
    protected $table = 'repair_requests';
    
    public function getWithBranches($limit = 20, $offset = 0, $status = null) {
        $sql = "SELECT r.*, b.name as branch_name 
                FROM {$this->table} r 
                LEFT JOIN branches b ON r.branch_id = b.id";
        
        if ($status) {
            $sql .= " WHERE r.status = ?";
        }
        
        $sql .= " ORDER BY r.created_at DESC LIMIT ? OFFSET ?";
        
        $stmt = $this->db->prepare($sql);
        
        if ($status) {
            $stmt->execute([$status, $limit, $offset]);
        } else {
            $stmt->execute([$limit, $offset]);
        }
        
        return $stmt->fetchAll();
    }
    
    public function getByStatus($status) {
        $sql = "SELECT r.*, b.name as branch_name 
                FROM {$this->table} r 
                LEFT JOIN branches b ON r.branch_id = b.id 
                WHERE r.status = ? 
                ORDER BY r.created_at DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$status]);
        return $stmt->fetchAll();
    }
    
    public function getByUser($user_telegram_id) {
        $sql = "SELECT r.*, b.name as branch_name 
                FROM {$this->table} r 
                LEFT JOIN branches b ON r.branch_id = b.id 
                WHERE r.user_telegram_id = ? 
                ORDER BY r.created_at DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$user_telegram_id]);
        return $stmt->fetchAll();
    }
    
    public function getByBranch($branch_id) {
        $sql = "SELECT r.*, b.name as branch_name 
                FROM {$this->table} r 
                LEFT JOIN branches b ON r.branch_id = b.id 
                WHERE r.branch_id = ? 
                ORDER BY r.created_at DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$branch_id]);
        return $stmt->fetchAll();
    }
    
    public function getWithBranchInfo($id) {
        $sql = "SELECT r.*, b.name as branch_name 
                FROM {$this->table} r 
                LEFT JOIN branches b ON r.branch_id = b.id 
                WHERE r.id = ?";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$id]);
        return $stmt->fetch();
    }
    
    public function updateStatus($id, $status) {
        return $this->update($id, [
            'status' => $status,
            'updated_at' => date('Y-m-d H:i:s')
        ]);
    }
    
    public function getStatsByStatus() {
        $sql = "SELECT 
                    status,
                    COUNT(*) as count
                FROM {$this->table} 
                GROUP BY status 
                ORDER BY 
                    CASE status 
                        WHEN 'нова' THEN 1 
                        WHEN 'в_роботі' THEN 2 
                        WHEN 'виконана' THEN 3 
                        ELSE 4 
                    END";
        
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll();
    }
    
    public function getStatsByBranch() {
        $sql = "SELECT 
                    b.name as branch_name,
                    COUNT(*) as total_requests,
                    SUM(CASE WHEN r.status = 'нова' THEN 1 ELSE 0 END) as new_requests,
                    SUM(CASE WHEN r.status = 'в_роботі' THEN 1 ELSE 0 END) as in_progress_requests,
                    SUM(CASE WHEN r.status = 'виконана' THEN 1 ELSE 0 END) as completed_requests
                FROM {$this->table} r 
                LEFT JOIN branches b ON r.branch_id = b.id 
                GROUP BY r.branch_id, b.name 
                ORDER BY total_requests DESC";
        
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll();
    }
    
    public function getByDateRange($start_date, $end_date) {
        $sql = "SELECT r.*, b.name as branch_name 
                FROM {$this->table} r 
                LEFT JOIN branches b ON r.branch_id = b.id 
                WHERE DATE(r.created_at) BETWEEN ? AND ? 
                ORDER BY r.created_at DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$start_date, $end_date]);
        return $stmt->fetchAll();
    }
    
    public function createRequest($user_telegram_id, $username, $branch_id, $room_number, $description, $phone = null) {
        return $this->create([
            'user_telegram_id' => $user_telegram_id,
            'username' => $username,
            'branch_id' => $branch_id,
            'room_number' => $room_number,
            'description' => $description,
            'phone' => $phone,
            'status' => 'нова'
        ]);
    }
    
    public function getRecentRequests($limit = 10) {
        $sql = "SELECT r.*, b.name as branch_name 
                FROM {$this->table} r 
                LEFT JOIN branches b ON r.branch_id = b.id 
                ORDER BY r.created_at DESC 
                LIMIT ?";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$limit]);
        return $stmt->fetchAll();
    }
    
    public function getPendingRequests() {
        return $this->getByStatus('нова');
    }
    
    public function getInProgressRequests() {
        return $this->getByStatus('в_роботі');
    }
    
    public function getCompletedRequests() {
        return $this->getByStatus('виконана');
    }
}