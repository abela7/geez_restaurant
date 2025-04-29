<?php
/**
 * TempCheck Class
 * 
 * Handles temperature check operations
 */
class TempCheck {
    private $db;
    
    /**
     * Constructor
     * 
     * @param Database $db Database instance
     */
    public function __construct($db) {
        $this->db = $db;
    }
    
    /**
     * Get temperature check by ID
     * 
     * @param int $check_id Check ID
     * @return array|false Temperature check data or false if not found
     */
    public function getById($check_id) {
        $sql = "SELECT tc.*, CONCAT(tc.check_date, ' ', tc.check_time) as check_timestamp, 
                tc.temperature as temperature_reading, tc.created_at as recorded_at,
                e.name as equipment_name, u.full_name as recorded_by 
                FROM temperature_checks tc 
                JOIN equipment e ON tc.equipment_id = e.equipment_id 
                JOIN users u ON tc.checked_by_user_id = u.user_id 
                WHERE tc.check_id = ?";
        return $this->db->fetchRow($sql, [$check_id]);
    }
    
    /**
     * Get all temperature checks
     * 
     * @param string $start_date Start date (YYYY-MM-DD)
     * @param string $end_date End date (YYYY-MM-DD)
     * @param int $equipment_id Equipment ID (optional)
     * @return array Temperature checks
     */
    public function getAll($start_date = null, $end_date = null, $equipment_id = null) {
        $params = [];
        $where = [];
        
        if ($start_date) {
            $where[] = "tc.check_date >= ?";
            $params[] = $start_date;
        }
        
        if ($end_date) {
            $where[] = "tc.check_date <= ?";
            $params[] = $end_date;
        }
        
        if ($equipment_id) {
            $where[] = "tc.equipment_id = ?";
            $params[] = $equipment_id;
        }
        
        $where_clause = !empty($where) ? "WHERE " . implode(" AND ", $where) : "";
        
        $sql = "SELECT tc.*, CONCAT(tc.check_date, ' ', tc.check_time) as check_timestamp, 
                tc.temperature as temperature_reading, tc.created_at as recorded_at,
                e.name as equipment_name, u.full_name as recorded_by 
                FROM temperature_checks tc 
                JOIN equipment e ON tc.equipment_id = e.equipment_id 
                JOIN users u ON tc.checked_by_user_id = u.user_id 
                {$where_clause} 
                ORDER BY tc.check_date DESC, tc.check_time DESC";
        
        return $this->db->fetchAll($sql, $params);
    }
    
    /**
     * Create new temperature check
     * 
     * @param array $data Temperature check data
     * @return int|false New check ID or false on failure
     */
    public function create($data) {
        $newData = $this->prepareData($data);
        return $this->db->insert('temperature_checks', $newData);
    }
    
    /**
     * Update temperature check
     * 
     * @param int $check_id Check ID
     * @param array $data Temperature check data
     * @return int Number of affected rows
     */
    public function update($check_id, $data) {
        $newData = $this->prepareData($data);
        return $this->db->update('temperature_checks', $newData, 'check_id = ?', [$check_id]);
    }
    
    /**
     * Prepare data for database operations
     * 
     * @param array $data Original data
     * @return array Prepared data
     */
    private function prepareData($data) {
        $newData = [];
        
        // Map fields
        if (isset($data['equipment_id'])) $newData['equipment_id'] = $data['equipment_id'];
        
        if (isset($data['temperature_reading'])) {
            $newData['temperature'] = $data['temperature_reading'];
        } elseif (isset($data['temperature'])) {
            $newData['temperature'] = $data['temperature'];
        }
        
        if (isset($data['check_timestamp'])) {
            $timestamp = strtotime($data['check_timestamp']);
            $newData['check_date'] = date('Y-m-d', $timestamp);
            $newData['check_time'] = date('H:i:s', $timestamp);
        } else {
            if (isset($data['check_date'])) $newData['check_date'] = $data['check_date'];
            if (isset($data['check_time'])) $newData['check_time'] = $data['check_time'];
        }
        
        if (isset($data['is_compliant'])) $newData['is_compliant'] = $data['is_compliant'];
        if (isset($data['corrective_action'])) $newData['corrective_action'] = $data['corrective_action'];
        
        if (isset($data['checked_by_user_id'])) {
            $newData['checked_by_user_id'] = $data['checked_by_user_id'];
        } elseif (isset($data['recorded_by_user_id'])) {
            $newData['checked_by_user_id'] = $data['recorded_by_user_id'];
        }

        // Add created_at timestamp to fix the database error
        $newData['created_at'] = date('Y-m-d H:i:s');
        
        return $newData;
    }
    
    /**
     * Delete temperature check
     * 
     * @param int $check_id Check ID
     * @return int Number of affected rows
     */
    public function delete($check_id) {
        return $this->db->delete('temperature_checks', 'check_id = ?', [$check_id]);
    }
    
    /**
     * Get temperature checks for a specific date
     * 
     * @param string $date Date (YYYY-MM-DD)
     * @return array Temperature checks for the date
     */
    public function getByDate($date) {
        $sql = "SELECT tc.*, CONCAT(tc.check_date, ' ', tc.check_time) as check_timestamp, 
                tc.temperature as temperature_reading, tc.created_at as recorded_at,
                e.name as equipment_name, u.full_name as recorded_by 
                FROM temperature_checks tc 
                JOIN equipment e ON tc.equipment_id = e.equipment_id 
                JOIN users u ON tc.checked_by_user_id = u.user_id 
                WHERE tc.check_date = ? 
                ORDER BY tc.check_time DESC";
        
        return $this->db->fetchAll($sql, [$date]);
    }
    
    /**
     * Get temperature checks for a specific equipment
     * 
     * @param int $equipment_id Equipment ID
     * @param int $limit Limit number of results (optional)
     * @return array Temperature checks for the equipment
     */
    public function getByEquipment($equipment_id, $limit = null) {
        $limit_clause = $limit ? "LIMIT " . (int)$limit : "";
        
        $sql = "SELECT tc.*, CONCAT(tc.check_date, ' ', tc.check_time) as check_timestamp, 
                tc.temperature as temperature_reading, tc.created_at as recorded_at,
                e.name as equipment_name, u.full_name as recorded_by 
                FROM temperature_checks tc 
                JOIN equipment e ON tc.equipment_id = e.equipment_id 
                JOIN users u ON tc.checked_by_user_id = u.user_id 
                WHERE tc.equipment_id = ? 
                ORDER BY tc.check_date DESC, tc.check_time DESC 
                {$limit_clause}";
        
        return $this->db->fetchAll($sql, [$equipment_id]);
    }
    
    /**
     * Get temperature checks with pagination and period filtering
     * 
     * @param int $limit Records per page
     * @param int $offset Offset for pagination
     * @param string $period Period filter (week, month, year or null for all)
     * @param int $equipment_id Equipment ID (optional)
     * @return array Result with checks and total count
     */
    public function getAllPaginated($limit = 20, $offset = 0, $period = null, $equipment_id = null) {
        $params = [];
        $where = [];
        
        // Apply period filter
        if ($period) {
            $today = date('Y-m-d');
            if ($period == 'week') {
                $where[] = "tc.check_date >= DATE_SUB(?, INTERVAL 1 WEEK)";
                $params[] = $today;
            } elseif ($period == 'month') {
                $where[] = "tc.check_date >= DATE_SUB(?, INTERVAL 1 MONTH)";
                $params[] = $today;
            } elseif ($period == 'year') {
                $where[] = "tc.check_date >= DATE_SUB(?, INTERVAL 1 YEAR)";
                $params[] = $today;
            }
        }
        
        if ($equipment_id) {
            $where[] = "tc.equipment_id = ?";
            $params[] = $equipment_id;
        }
        
        $where_clause = !empty($where) ? "WHERE " . implode(" AND ", $where) : "";
        
        // Get total count
        $count_sql = "SELECT COUNT(*) as total 
                     FROM temperature_checks tc 
                     JOIN equipment e ON tc.equipment_id = e.equipment_id 
                     JOIN users u ON tc.checked_by_user_id = u.user_id 
                     {$where_clause}";
        
        $total_count = $this->db->fetchRow($count_sql, $params)['total'];
        
        // Clone params for main query
        $query_params = $params;
        
        // Get paginated results
        $sql = "SELECT tc.*, CONCAT(tc.check_date, ' ', tc.check_time) as check_timestamp, 
                tc.temperature as temperature_reading, tc.created_at as recorded_at,
                e.name as equipment_name, u.full_name as recorded_by 
                FROM temperature_checks tc 
                JOIN equipment e ON tc.equipment_id = e.equipment_id 
                JOIN users u ON tc.checked_by_user_id = u.user_id 
                {$where_clause} 
                ORDER BY tc.check_date DESC, tc.check_time DESC
                LIMIT ? OFFSET ?";
        
        $query_params[] = (int)$limit;
        $query_params[] = (int)$offset;
        
        $results = $this->db->fetchAll($sql, $query_params);
        
        return [
            'records' => $results,
            'total' => $total_count
        ];
    }
}
