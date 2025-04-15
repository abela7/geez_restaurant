<?php
/**
 * FoodWasteLog Class
 * 
 * Handles food waste log operations
 */
class FoodWasteLog {
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
     * Get food waste log by ID
     * 
     * @param int $waste_id Waste ID
     * @return array|false Waste log data or false if not found
     */
    public function getById($waste_id) {
        $sql = "SELECT fw.waste_id, fw.food_item as item_description, fw.waste_type, fw.reason as reason_for_waste, 
                fw.weight_kg as quantity, 'kg' as unit_of_measure, fw.cost as total_cost, 
                (fw.cost / fw.weight_kg) as cost_per_unit, fw.waste_date, fw.action_taken, fw.notes, 
                fw.recorded_by_user_id, fw.created_at as waste_timestamp, fw.created_at as recorded_at, fw.updated_at,
                u.full_name as recorded_by
                FROM food_waste_log fw 
                JOIN users u ON fw.recorded_by_user_id = u.user_id 
                WHERE fw.waste_id = ?";
        return $this->db->fetchRow($sql, [$waste_id]);
    }
    
    /**
     * Get all food waste logs
     * 
     * @param string $start_date Start date (YYYY-MM-DD)
     * @param string $end_date End date (YYYY-MM-DD)
     * @return array Food waste logs
     */
    public function getAll($start_date = null, $end_date = null) {
        $params = [];
        $where = [];
        
        if ($start_date) {
            $where[] = "fw.waste_date >= ?";
            $params[] = $start_date;
        }
        
        if ($end_date) {
            $where[] = "fw.waste_date <= ?";
            $params[] = $end_date;
        }
        
        $where_clause = !empty($where) ? "WHERE " . implode(" AND ", $where) : "";
        
        $sql = "SELECT fw.waste_id, fw.food_item as item_description, fw.waste_type, fw.reason as reason_for_waste, 
                fw.weight_kg as quantity, 'kg' as unit_of_measure, fw.cost as total_cost, 
                (fw.cost / fw.weight_kg) as cost_per_unit, fw.waste_date, fw.action_taken, fw.notes, 
                fw.recorded_by_user_id, fw.created_at as waste_timestamp, fw.created_at as recorded_at, fw.updated_at,
                u.full_name as recorded_by
                FROM food_waste_log fw 
                JOIN users u ON fw.recorded_by_user_id = u.user_id 
                {$where_clause} 
                ORDER BY fw.created_at DESC";
        
        return $this->db->fetchAll($sql, $params);
    }
    
    /**
     * Create new food waste log entry
     * 
     * @param array $data Waste log data
     * @return int|false New waste ID or false on failure
     */
    public function create($data) {
        // Map input data to database columns
        $insertData = [
            'food_item' => $data['item_description'] ?? null,
            'waste_type' => $data['waste_type'] ?? 'General',
            'reason' => $data['reason_for_waste'] ?? null,
            'weight_kg' => $data['quantity'] ?? 0,
            'cost' => $data['total_cost'] ?? 0,
            'waste_date' => $data['waste_date'] ?? date('Y-m-d'),
            'action_taken' => $data['action_taken'] ?? null,
            'notes' => $data['notes'] ?? null,
            'recorded_by_user_id' => $data['recorded_by_user_id'] ?? null,
            'created_at' => $data['created_at'] ?? date('Y-m-d H:i:s'),
            'updated_at' => $data['updated_at'] ?? date('Y-m-d H:i:s')
        ];
        
        // Calculate total cost if unit cost is provided
        if (isset($data['cost_per_unit']) && isset($data['quantity']) && $data['cost_per_unit'] > 0) {
            $insertData['cost'] = $data['cost_per_unit'] * $data['quantity'];
        }
        
        return $this->db->insert('food_waste_log', $insertData);
    }
    
    /**
     * Update food waste log entry
     * 
     * @param int $waste_id Waste ID
     * @param array $data Waste log data
     * @return int Number of affected rows
     */
    public function update($waste_id, $data) {
        // Map input data to database columns
        $updateData = [];
        
        if (isset($data['item_description'])) {
            $updateData['food_item'] = $data['item_description'];
        }
        
        if (isset($data['waste_type'])) {
            $updateData['waste_type'] = $data['waste_type'];
        }
        
        if (isset($data['reason_for_waste'])) {
            $updateData['reason'] = $data['reason_for_waste'];
        }
        
        if (isset($data['quantity'])) {
            $updateData['weight_kg'] = $data['quantity'];
        }
        
        if (isset($data['total_cost'])) {
            $updateData['cost'] = $data['total_cost'];
        }
        
        if (isset($data['waste_date'])) {
            $updateData['waste_date'] = $data['waste_date'];
        }
        
        if (isset($data['action_taken'])) {
            $updateData['action_taken'] = $data['action_taken'];
        }
        
        if (isset($data['notes'])) {
            $updateData['notes'] = $data['notes'];
        }
        
        if (isset($data['recorded_by_user_id'])) {
            $updateData['recorded_by_user_id'] = $data['recorded_by_user_id'];
        }
        
        $updateData['updated_at'] = date('Y-m-d H:i:s');
        
        // Calculate total cost if unit cost is provided
        if (isset($data['cost_per_unit']) && isset($data['quantity']) && $data['cost_per_unit'] > 0) {
            $updateData['cost'] = $data['cost_per_unit'] * $data['quantity'];
        }
        
        return $this->db->update('food_waste_log', $updateData, 'waste_id = ?', [$waste_id]);
    }
    
    /**
     * Delete food waste log entry
     * 
     * @param int $waste_id Waste ID
     * @return int Number of affected rows
     */
    public function delete($waste_id) {
        return $this->db->delete('food_waste_log', 'waste_id = ?', [$waste_id]);
    }
    
    /**
     * Get food waste summary by date range
     * 
     * @param string $start_date Start date (YYYY-MM-DD)
     * @param string $end_date End date (YYYY-MM-DD)
     * @return array Summary data
     */
    public function getSummaryByDateRange($start_date, $end_date) {
        $sql = "SELECT 
                    waste_date as waste_date,
                    SUM(weight_kg) as total_quantity,
                    SUM(cost) as total_cost
                FROM food_waste_log
                WHERE waste_date BETWEEN ? AND ?
                GROUP BY waste_date
                ORDER BY waste_date";
        
        return $this->db->fetchAll($sql, [$start_date, $end_date]);
    }
    
    /**
     * Get food waste summary by item
     * 
     * @param string $start_date Start date (YYYY-MM-DD)
     * @param string $end_date End date (YYYY-MM-DD)
     * @return array Summary data
     */
    public function getSummaryByItem($start_date, $end_date) {
        $sql = "SELECT 
                    food_item as item_description,
                    SUM(weight_kg) as total_quantity,
                    SUM(cost) as total_cost
                FROM food_waste_log
                WHERE waste_date BETWEEN ? AND ?
                GROUP BY food_item
                ORDER BY total_cost DESC";
        
        return $this->db->fetchAll($sql, [$start_date, $end_date]);
    }
    
    /**
     * Get food waste summary by reason
     * 
     * @param string $start_date Start date (YYYY-MM-DD)
     * @param string $end_date End date (YYYY-MM-DD)
     * @return array Summary data
     */
    public function getSummaryByReason($start_date, $end_date) {
        $sql = "SELECT 
                    reason as reason_for_waste,
                    COUNT(*) as count,
                    SUM(weight_kg) as total_quantity,
                    SUM(cost) as total_cost
                FROM food_waste_log
                WHERE waste_date BETWEEN ? AND ?
                GROUP BY reason
                ORDER BY total_cost DESC";
        
        return $this->db->fetchAll($sql, [$start_date, $end_date]);
    }
}
