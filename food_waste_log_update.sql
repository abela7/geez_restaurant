-- Update food waste logs with quantities over 1KG to be between 0.3-0.8KG
UPDATE `food_waste_log`
SET 
    `weight_kg` = ROUND(0.3 + (RAND() * 0.5), 2),  -- Random value between 0.3 and 0.8
    `cost` = ROUND((`cost` / `weight_kg`) * (0.3 + (RAND() * 0.5)), 2),  -- Adjust cost proportionally
    `updated_at` = NOW()
WHERE 
    `weight_kg` > 1.0; 