<?php
/**
 * CleaningLocation Class
 * 
 * Handles cleaning locations management
 */
class CleaningLocation {
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
     * Get cleaning location by ID
     * 
     * @param int $location_id Location ID
     * @return array|false Location data or false if not found
     */
    public function getById($location_id) {
        $sql = "SELECT * FROM cleaning_locations WHERE location_id = ?";
        return $this->db->fetchRow($sql, [$location_id]);
    }
    
    /**
     * Get all active cleaning locations
     * 
     * @return array All active cleaning locations
     */
    public function getAllActive() {
        $sql = "SELECT * FROM cleaning_locations WHERE is_active = 1 ORDER BY name";
        return $this->db->fetchAll($sql);
    }
    
    /**
     * Get all cleaning locations
     * 
     * @return array All cleaning locations
     */
    public function getAll() {
        $sql = "SELECT * FROM cleaning_locations ORDER BY name";
        return $this->db->fetchAll($sql);
    }
    
    /**
     * Create new cleaning location
     * 
     * @param array $data Location data
     * @return int|false New location ID or false on failure
     */
    public function create($data) {
        return $this->db->insert('cleaning_locations', $data);
    }
    
    /**
     * Update cleaning location
     * 
     * @param int $location_id Location ID
     * @param array $data Location data
     * @return int Number of affected rows
     */
    public function update($location_id, $data) {
        return $this->db->update('cleaning_locations', $data, 'location_id = ?', [$location_id]);
    }
    
    /**
     * Delete cleaning location
     * 
     * @param int $location_id Location ID
     * @return int Number of affected rows
     */
    public function delete($location_id) {
        return $this->db->delete('cleaning_locations', 'location_id = ?', [$location_id]);
    }
    
    /**
     * Toggle cleaning location active status
     * 
     * @param int $location_id Location ID
     * @param bool $is_active New active status
     * @return int Number of affected rows
     */
    public function toggleActive($location_id, $is_active) {
        $data = ['is_active' => $is_active ? 1 : 0];
        return $this->db->update('cleaning_locations', $data, 'location_id = ?', [$location_id]);
    }
    
    /**
     * Get location by name
     * 
     * @param string $name Location name
     * @return array|false Location data or false if not found
     */
    public function getByName($name) {
        $sql = "SELECT * FROM cleaning_locations WHERE name = ?";
        return $this->db->fetchRow($sql, [$name]);
    }
}
