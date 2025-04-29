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
     * Get all temperature checks with optional filtering and pagination
     * 
     * @param string $start_date Start date (YYYY-MM-DD)
     * @param string $end_date End date (YYYY-MM-DD)
     * @param int $equipment_id Equipment ID (optional)
     * @param int $page Page number (optional, default 1)
     * @param int $records_per_page Records per page (optional, default 20)
     * @return array Associative array containing temperature checks and pagination info
     */
    public function getAll($start_date = null, $end_date = null, $equipment_id = null, $page = 1, $records_per_page = 20) {
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
        
        // Count total records for pagination
        $count_sql = "SELECT COUNT(*) as total 
                      FROM temperature_checks tc 
                      JOIN equipment e ON tc.equipment_id = e.equipment_id 
                      JOIN users u ON tc.checked_by_user_id = u.user_id 
                      {$where_clause}";
        
        $count_result = $this->db->fetchOne($count_sql, $params);
        $total_records = $count_result['total'] ?? 0;
        
        // Calculate pagination values
        $page = max(1, (int)$page); // Ensure page is at least 1
        $records_per_page = max(1, (int)$records_per_page); // Ensure records_per_page is at least 1
        $offset = ($page - 1) * $records_per_page;
        $total_pages = ceil($total_records / $records_per_page);
        
        // Main query with pagination
        $sql = "SELECT tc.*, CONCAT(tc.check_date, ' ', tc.check_time) as check_timestamp, 
                tc.temperature as temperature_reading, tc.created_at as recorded_at,
                e.name as equipment_name, u.full_name as recorded_by 
                FROM temperature_checks tc 
                JOIN equipment e ON tc.equipment_id = e.equipment_id 
                JOIN users u ON tc.checked_by_user_id = u.user_id 
                {$where_clause} 
                ORDER BY tc.check_date DESC, tc.check_time DESC
                LIMIT {$offset}, {$records_per_page}";
        
        $records = $this->db->fetchAll($sql, $params);
        
        // Return both records and pagination info
        return [
            'records' => $records,
            'pagination' => [
                'total_records' => $total_records,
                'total_pages' => $total_pages,
                'current_page' => $page,
                'records_per_page' => $records_per_page,
                'has_previous' => $page > 1,
                'has_next' => $page < $total_pages
            ]
        ];
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
     * Get temperature checks grouped by time period
     * 
     * @param string $time_period Period type (year, month, week)
     * @param int $equipment_id Equipment ID (optional)
     * @return array Grouped temperature checks
     */
    public function getGroupedBy($time_period, $equipment_id = null) {
        $group_format = '';
        $group_field = '';
        
        switch ($time_period) {
            case 'year':
                $group_format = '%Y';
                $group_field = 'YEAR(tc.check_date) as period';
                break;
            case 'month':
                $group_format = '%Y-%m';
                $group_field = "DATE_FORMAT(tc.check_date, '%Y-%m') as period";
                break;
            case 'week':
                $group_format = '%Y-%u';
                $group_field = "DATE_FORMAT(tc.check_date, '%Y-%u') as period, CONCAT('Week ', WEEK(tc.check_date), ', ', YEAR(tc.check_date)) as period_label";
                break;
            default:
                $group_format = '%Y-%m-%d';
                $group_field = "tc.check_date as period";
                break;
        }
        
        $where = [];
        $params = [];
        
        if ($equipment_id) {
            $where[] = "tc.equipment_id = ?";
            $params[] = $equipment_id;
        }
        
        $where_clause = !empty($where) ? "WHERE " . implode(" AND ", $where) : "";
        
        $sql = "SELECT {$group_field}, 
                COUNT(tc.check_id) as check_count,
                MIN(tc.check_date) as start_date,
                MAX(tc.check_date) as end_date
                FROM temperature_checks tc 
                JOIN equipment e ON tc.equipment_id = e.equipment_id 
                {$where_clause} 
                GROUP BY DATE_FORMAT(tc.check_date, '{$group_format}')
                ORDER BY tc.check_date DESC";
        
        return $this->db->fetchAll($sql, $params);
    }

    /**
     * Get temperature checks for a specific time period
     * 
     * @param string $period Period value (e.g., 2023-01 for month)
     * @param string $period_type Period type (year, month, week)
     * @param int $equipment_id Equipment ID (optional)
     * @return array Temperature checks
     */
    public function getByPeriod($period, $period_type, $equipment_id = null) {
        $params = [];
        $where = [];
        
        switch ($period_type) {
            case 'year':
                $where[] = "YEAR(tc.check_date) = ?";
                $params[] = $period;
                break;
            case 'month':
                list($year, $month) = explode('-', $period);
                $where[] = "YEAR(tc.check_date) = ? AND MONTH(tc.check_date) = ?";
                $params[] = $year;
                $params[] = $month;
                break;
            case 'week':
                list($year, $week) = explode('-', $period);
                $where[] = "YEAR(tc.check_date) = ? AND WEEK(tc.check_date) = ?";
                $params[] = $year;
                $params[] = $week;
                break;
            default:
                $where[] = "tc.check_date = ?";
                $params[] = $period;
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
}
