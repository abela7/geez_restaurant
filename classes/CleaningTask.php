<?php
/**
 * CleaningTask Class
 * 
 * Handles cleaning tasks management
 */
class CleaningTask {
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
     * Get cleaning task by ID
     * 
     * @param int $task_id Task ID
     * @return array|false Task data or false if not found
     */
    public function getById($task_id) {
        $sql = "SELECT * FROM cleaning_task WHERE task_id = ?";
        return $this->db->fetchRow($sql, [$task_id]);
    }
    
    /**
     * Get all active cleaning tasks
     * 
     * @param string $frequency Filter by frequency (optional)
     * @return array All active cleaning tasks
     */
    public function getAllActive($frequency = null) {
        $params = [];
        $where = ["is_active = 1"];
        
        if ($frequency) {
            $where[] = "frequency = ?";
            $params[] = $frequency;
        }
        
        $where_clause = implode(" AND ", $where);
        
        $sql = "SELECT * FROM cleaning_task WHERE {$where_clause} ORDER BY description";
        return $this->db->fetchAll($sql, $params);
    }
    
    /**
     * Get all cleaning tasks
     * 
     * @return array All cleaning tasks
     */
    public function getAll() {
        $sql = "SELECT * FROM cleaning_task ORDER BY description";
        return $this->db->fetchAll($sql);
    }
    
    /**
     * Get all active cleaning tasks for a specific location
     * 
     * @param int $location_id Location ID
     * @return array Tasks for the location
     */
    public function getByLocation($location_id) {
        // Note: This assumes a direct link (location_id column) exists in cleaning_task table.
        // If tasks are not directly linked to locations, this query needs adjustment.
        // For example, fetch all active tasks if no direct link exists.
        $sql = "SELECT * 
                FROM cleaning_task 
                WHERE is_active = 1 AND location_id = ? 
                ORDER BY description"; // Add ordering if needed, e.g., display_order or description
        
        // Check if location_id column exists - if not, maybe fetch all active?
        // If the location_id column DOES NOT exist in cleaning_task, 
        // you might want to return getAllActive() instead, or adjust the schema.
        // For now, assuming the column exists based on the function need.
        
        try {
            return $this->db->fetchAll($sql, [$location_id]);
        } catch (Exception $e) {
            // Check if the error is due to missing column 'location_id'
            if (strpos($e->getMessage(), 'Unknown column') !== false && strpos($e->getMessage(), 'location_id') !== false) {
                // Fallback: If location_id column doesn't exist, fetch all active tasks instead.
                // Log this situation as it indicates a schema mismatch.
                error_log("Warning: location_id column not found in cleaning_task table. Fetching all active tasks instead for location filter.");
                return $this->getAllActive();
            } else {
                // Re-throw other errors
                throw $e;
            }
        }
    }
    
    /**
     * Create new cleaning task
     * 
     * @param array $data Task data
     * @return int|false New task ID or false on failure
     */
    public function create($data) {
        return $this->db->insert('cleaning_task', $data);
    }
    
    /**
     * Update cleaning task
     * 
     * @param int $task_id Task ID
     * @param array $data Task data
     * @return int Number of affected rows
     */
    public function update($task_id, $data) {
        return $this->db->update('cleaning_task', $data, 'task_id = ?', [$task_id]);
    }
    
    /**
     * Delete cleaning task
     * 
     * @param int $task_id Task ID
     * @return int Number of affected rows
     */
    public function delete($task_id) {
        return $this->db->delete('cleaning_task', 'task_id = ?', [$task_id]);
    }
    
    /**
     * Toggle cleaning task active status
     * 
     * @param int $task_id Task ID
     * @param bool $is_active New active status
     * @return int Number of affected rows
     */
    public function toggleActive($task_id, $is_active) {
        $data = ['is_active' => $is_active ? 1 : 0];
        return $this->db->update('cleaning_task', $data, 'task_id = ?', [$task_id]);
    }
    
    /**
     * Update task display order
     * 
     * @param int $task_id Task ID
     * @param int $display_order New display order
     * @return int Number of affected rows
     */
    /* This method is disabled as the display_order column doesn't exist in the database table
    public function updateDisplayOrder($task_id, $display_order) {
        $data = ['display_order' => $display_order];
        return $this->db->update('cleaning_task', $data, 'task_id = ?', [$task_id]);
    }
    */
}
