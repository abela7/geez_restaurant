<?php
/**
 * Equipment Class
 * 
 * Handles equipment management for temperature checks
 */
class Equipment {
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
     * Get equipment by ID
     * 
     * @param int $equipment_id Equipment ID
     * @return array|false Equipment data or false if not found
     */
    public function getById($equipment_id) {
        $sql = "SELECT * FROM equipment WHERE equipment_id = ?";
        return $this->db->fetchRow($sql, [$equipment_id]);
    }
    
    /**
     * Get all active equipment
     * 
     * @return array All active equipment
     */
    public function getAllActive() {
        $sql = "SELECT * FROM equipment WHERE is_active = 1 ORDER BY name";
        return $this->db->fetchAll($sql);
    }
    
    /**
     * Get all equipment
     * 
     * @return array All equipment
     */
    public function getAll() {
        $sql = "SELECT * FROM equipment ORDER BY name";
        return $this->db->fetchAll($sql);
    }
    
    /**
     * Create new equipment
     * 
     * @param array $data Equipment data
     * @return int|false New equipment ID or false on failure
     */
    public function create($data) {
        return $this->db->insert('equipment', $data);
    }
    
    /**
     * Update equipment
     * 
     * @param int $equipment_id Equipment ID
     * @param array $data Equipment data
     * @return int Number of affected rows
     */
    public function update($equipment_id, $data) {
        return $this->db->update('equipment', $data, 'equipment_id = ?', [$equipment_id]);
    }
    
    /**
     * Delete equipment
     * 
     * @param int $equipment_id Equipment ID
     * @return int Number of affected rows
     */
    public function delete($equipment_id) {
        return $this->db->delete('equipment', 'equipment_id = ?', [$equipment_id]);
    }
    
    /**
     * Toggle equipment active status
     * 
     * @param int $equipment_id Equipment ID
     * @param bool $is_active New active status
     * @return int Number of affected rows
     */
    public function toggleActive($equipment_id, $is_active) {
        $data = ['is_active' => $is_active ? 1 : 0];
        return $this->db->update('equipment', $data, 'equipment_id = ?', [$equipment_id]);
    }
}
