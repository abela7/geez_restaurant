-- Update food waste logs with realistic pricing and consistent calculations

-- Set realistic cost per unit values based on food type
UPDATE `food_waste_log` 
SET `cost` = ROUND(`weight_kg` * 5.50, 2),
    `updated_at` = NOW()
WHERE `food_item` IN ('Shiro', 'Shiro Powder', 'Misir Wot', 'Kik Alicha', 'Fosolia', 'Gomen', 'Atkilt Wot', 'Azifa', 'Ayib');

UPDATE `food_waste_log` 
SET `cost` = ROUND(`weight_kg` * 8.75, 2),
    `updated_at` = NOW()
WHERE `food_item` IN ('Injera', 'Injera Firfir', 'Kategna', 'Beyaynetu', 'Awaze Sauce', 'Mitmita Spice', 'Berbere Spice', 'Niter Kibbeh');

UPDATE `food_waste_log` 
SET `cost` = ROUND(`weight_kg` * 12.50, 2),
    `updated_at` = NOW()
WHERE `food_item` IN ('Sambusa', 'Kocho', 'Dulet', 'Fish Dulet');

UPDATE `food_waste_log` 
SET `cost` = ROUND(`weight_kg` * 18.50, 2),
    `updated_at` = NOW()
WHERE `food_item` IN ('Kitfo', 'Lamb Tibs', 'Zilzil Tibs', 'Special Tibs', 'Awaze Tibs', 'Rice with Beef');

UPDATE `food_waste_log` 
SET `cost` = ROUND(`weight_kg` * 22.00, 2),
    `updated_at` = NOW()
WHERE `food_item` IN ('Mahberawi', 'Vegetarian Combo', 'Ge\'ez Special', 'Yefsik Mahberawi');

-- Make all weights between 0.15 and 0.85kg, keeping heavier weights for expensive platters
UPDATE `food_waste_log`
SET `weight_kg` = ROUND(0.15 + (RAND() * 0.3), 2), -- 0.15-0.45kg for sides/sauces
    `cost` = ROUND(`cost` * (0.15 + (RAND() * 0.3)) / `weight_kg`, 2),
    `updated_at` = NOW()
WHERE `food_item` IN ('Shiro Powder', 'Berbere Spice', 'Mitmita Spice', 'Awaze Sauce', 'Niter Kibbeh');

UPDATE `food_waste_log`
SET `weight_kg` = ROUND(0.20 + (RAND() * 0.4), 2), -- 0.20-0.60kg for regular dishes
    `cost` = ROUND(`cost` * (0.20 + (RAND() * 0.4)) / `weight_kg`, 2),
    `updated_at` = NOW()
WHERE `food_item` IN ('Misir Wot', 'Kik Alicha', 'Fosolia', 'Gomen', 'Atkilt Wot', 'Azifa', 'Ayib', 'Injera', 'Injera Firfir', 'Kategna', 'Sambusa', 'Kocho', 'Shiro');

UPDATE `food_waste_log`
SET `weight_kg` = ROUND(0.30 + (RAND() * 0.55), 2), -- 0.30-0.85kg for meat dishes & combos
    `cost` = ROUND(`cost` * (0.30 + (RAND() * 0.55)) / `weight_kg`, 2),
    `updated_at` = NOW()
WHERE `food_item` IN ('Kitfo', 'Lamb Tibs', 'Zilzil Tibs', 'Special Tibs', 'Awaze Tibs', 'Rice with Beef', 'Dulet', 'Fish Dulet', 'Mahberawi', 'Vegetarian Combo', 'Ge\'ez Special', 'Yefsik Mahberawi', 'Beyaynetu');

-- Make sure cost is properly calculated based on weight and price per unit for every item
UPDATE `food_waste_log`
SET `cost` = CASE
    WHEN `food_item` IN ('Shiro', 'Shiro Powder', 'Misir Wot', 'Kik Alicha', 'Fosolia', 'Gomen', 'Atkilt Wot', 'Azifa', 'Ayib') 
        THEN ROUND(`weight_kg` * 5.50, 2)
    WHEN `food_item` IN ('Injera', 'Injera Firfir', 'Kategna', 'Beyaynetu', 'Awaze Sauce', 'Mitmita Spice', 'Berbere Spice', 'Niter Kibbeh')
        THEN ROUND(`weight_kg` * 8.75, 2)
    WHEN `food_item` IN ('Sambusa', 'Kocho', 'Dulet', 'Fish Dulet')
        THEN ROUND(`weight_kg` * 12.50, 2)
    WHEN `food_item` IN ('Kitfo', 'Lamb Tibs', 'Zilzil Tibs', 'Special Tibs', 'Awaze Tibs', 'Rice with Beef')
        THEN ROUND(`weight_kg` * 18.50, 2)
    WHEN `food_item` IN ('Mahberawi', 'Vegetarian Combo', 'Ge\'ez Special', 'Yefsik Mahberawi')
        THEN ROUND(`weight_kg` * 22.00, 2)
    ELSE ROUND(`weight_kg` * 10.00, 2) -- Default case
    END,
    `updated_at` = NOW();

-- Update Kitfo specifically with more realistic pricing
UPDATE `food_waste_log`
SET `weight_kg` = 0.45,
    `cost` = 8.33, -- 0.45kg × £18.50 = £8.33
    `updated_at` = NOW()
WHERE `waste_id` = 16; -- The Kitfo record from 27/12/23 