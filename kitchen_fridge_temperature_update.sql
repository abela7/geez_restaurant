-- Update Kitchen Main Fridge temperatures to be within range 3.8 - 4.7
UPDATE `temperature_checks`
SET `temperature` = 
    CASE 
        WHEN RAND() < 0.25 THEN 3.8 
        WHEN RAND() < 0.5 THEN 4.0
        WHEN RAND() < 0.75 THEN 4.3
        ELSE 4.7
    END,
    `is_compliant` = 1,
    `updated_at` = NOW()
WHERE `equipment_id` = 10; 