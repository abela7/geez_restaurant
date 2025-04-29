<?php
/**
 * CleaningLog Class
 * 
 * Handles cleaning log operations
 */
class CleaningLog {
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
     * Get cleaning log by ID
     * 
     * @param int $log_id Log ID
     * @return array|false Log data or false if not found
     */
    public function getById($log_id) {
        $sql = "SELECT cl.*, ct.description as task_description, ct.frequency, clo.name as name, u.full_name as recorded_by 
                FROM cleaning_log cl 
                JOIN cleaning_task ct ON cl.task_id = ct.task_id 
                JOIN cleaning_locations clo ON cl.location_id = clo.location_id 
                JOIN users u ON cl.completed_by_user_id = u.user_id 
                WHERE cl.log_id = ?";
        
        $log = $this->db->fetchRow($sql, [$log_id]);
        
        // Map is_verified to is_completed for code compatibility
        if ($log) {
            $log['is_completed'] = $log['is_verified'] ?? 0;
        }
        
        return $log;
    }
    
    /**
     * Get cleaning logs by date and location
     * 
     * @param string $date Date (YYYY-MM-DD)
     * @param int $location_id Location ID
     * @return array Cleaning logs
     */
    public function getByDateAndLocation($date, $location_id) {
        $sql = "SELECT cl.*, ct.description as task_description, ct.frequency, u.full_name as recorded_by 
                FROM cleaning_log cl 
                JOIN cleaning_task ct ON cl.task_id = ct.task_id 
                JOIN users u ON cl.completed_by_user_id = u.user_id 
                WHERE cl.completed_date = ? AND cl.location_id = ? 
                ORDER BY ct.description";
        
        $logs = $this->db->fetchAll($sql, [$date, $location_id]);
        
        // Map is_verified to is_completed for code compatibility
        foreach ($logs as &$log) {
            $log['is_completed'] = $log['is_verified'] ?? 0;
        }
        
        return $logs;
    }
    
    /**
     * Get all cleaning logs
     * 
     * @param string $start_date Start date (YYYY-MM-DD)
     * @param string $end_date End date (YYYY-MM-DD)
     * @param int $location_id Location ID (optional)
     * @return array Cleaning logs
     */
    public function getAll($start_date = null, $end_date = null, $location_id = null) {
        $params = [];
        $where = [];
        
        if ($start_date) {
            $where[] = "cl.completed_date >= ?";
            $params[] = $start_date;
        }
        
        if ($end_date) {
            $where[] = "cl.completed_date <= ?";
            $params[] = $end_date;
        }
        
        if ($location_id) {
            $where[] = "cl.location_id = ?";
            $params[] = $location_id;
        }
        
        $where_clause = !empty($where) ? "WHERE " . implode(" AND ", $where) : "";
        
        $sql = "SELECT cl.*, ct.description as task_description, clo.name as name, u.full_name as recorded_by 
                FROM cleaning_log cl 
                JOIN cleaning_task ct ON cl.task_id = ct.task_id 
                JOIN cleaning_locations clo ON cl.location_id = clo.location_id 
                JOIN users u ON cl.completed_by_user_id = u.user_id 
                {$where_clause} 
                ORDER BY cl.completed_date DESC, clo.name";
        
        $logs = $this->db->fetchAll($sql, $params);
        
        // Map is_verified to is_completed for code compatibility
        foreach ($logs as &$log) {
            $log['is_completed'] = $log['is_verified'] ?? 0;
        }
        
        return $logs;
    }
    
    /**
     * Create new cleaning log entry
     * 
     * @param array $data Log data
     * @return int|false New log ID or false on failure
     */
    public function create($data) {
        // Map field names to table column names if needed
        $mappedData = [];
        
        if (isset($data['cleaning_location_id'])) {
            $mappedData['location_id'] = $data['cleaning_location_id'];
        } elseif (isset($data['location_id'])) {
            $mappedData['location_id'] = $data['location_id'];
        }
        
        if (isset($data['task_id'])) {
            $mappedData['task_id'] = $data['task_id'];
        }
        
        if (isset($data['check_date'])) {
            $mappedData['completed_date'] = $data['check_date'];
        } elseif (isset($data['cleaning_date'])) {
            $mappedData['completed_date'] = $data['cleaning_date'];
        }
        
        if (isset($data['cleaning_time'])) {
            $mappedData['completed_time'] = $data['cleaning_time'];
        } else {
            $mappedData['completed_time'] = date('H:i:s');
        }
        
        if (isset($data['is_completed'])) {
            $mappedData['is_verified'] = $data['is_completed'];
        }
        
        if (isset($data['notes'])) {
            $mappedData['notes'] = $data['notes'];
        }
        
        if (isset($data['recorded_by_user_id'])) {
            $mappedData['completed_by_user_id'] = $data['recorded_by_user_id'];
        } elseif (isset($data['completed_by_user_id'])) {
            $mappedData['completed_by_user_id'] = $data['completed_by_user_id'];
        }
        
        // Add created_at timestamp
        $mappedData['created_at'] = date('Y-m-d H:i:s');
        
        return $this->db->insert('cleaning_log', $mappedData);
    }
    
    /**
     * Update cleaning log entry
     * 
     * @param int $log_id Log ID
     * @param array $data Log data
     * @return int Number of affected rows
     */
    public function update($log_id, $data) {
        // Map field names if needed
        $mappedData = [];
        
        if (isset($data['is_completed'])) {
            $mappedData['is_verified'] = $data['is_completed'];
        }
        
        if (isset($data['notes'])) {
            $mappedData['notes'] = $data['notes'];
        }
        
        if (isset($data['recorded_by_user_id'])) {
            $mappedData['completed_by_user_id'] = $data['recorded_by_user_id'];
        } elseif (isset($data['completed_by_user_id'])) {
            $mappedData['completed_by_user_id'] = $data['completed_by_user_id'];
        }
        
        return $this->db->update('cleaning_log', $mappedData, 'log_id = ?', [$log_id]);
    }
    
    /**
     * Delete cleaning log entry
     * 
     * @param int $log_id Log ID
     * @return int Number of affected rows
     */
    public function delete($log_id) {
        return $this->db->delete('cleaning_log', 'log_id = ?', [$log_id]);
    }
    
    /**
     * Get or create cleaning log entry
     * 
     * @param int $location_id Location ID
     * @param int $task_id Task ID
     * @param string $date Date (YYYY-MM-DD)
     * @param int $user_id User ID
     * @return array|false Log data or false on failure
     */
    public function getOrCreate($location_id, $task_id, $date, $user_id) {
        // Check if log entry exists
        $sql = "SELECT * FROM cleaning_log 
                WHERE location_id = ? AND task_id = ? AND completed_date = ?";
        
        $log = $this->db->fetchRow($sql, [$location_id, $task_id, $date]);
        
        if ($log) {
            // Map is_verified to is_completed for code compatibility
            $log['is_completed'] = $log['is_verified'] ?? 0;
            return $log;
        }
        
        // Create new log entry
        $data = [
            'location_id' => $location_id,
            'task_id' => $task_id,
            'completed_date' => $date,
            'completed_time' => date('H:i:s'),
            'is_completed' => 0,
            'completed_by_user_id' => $user_id,
            'notes' => null
        ];
        
        $log_id = $this->create($data);
        
        if ($log_id) {
            $log = $this->getById($log_id);
            // Map is_verified to is_completed for code compatibility
            if ($log) {
                $log['is_completed'] = $log['is_verified'] ?? 0;
            }
            return $log;
        }
        
        return false;
    }
    
    /**
     * Toggle cleaning task completion status
     * 
     * @param int $log_id Log ID
     * @param bool $is_completed New completion status
     * @param string $notes Optional notes
     * @param int $user_id User ID
     * @return int Number of affected rows
     */
    public function toggleCompletion($log_id, $is_completed, $notes = null, $user_id = null) {
        $data = [
            'is_completed' => $is_completed ? 1 : 0,
            'notes' => $notes
        ];
        
        if ($user_id) {
            $data['completed_by_user_id'] = $user_id;
        }
        
        return $this->update($log_id, $data);
    }
}
