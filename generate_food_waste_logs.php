<?php
// Enable error reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Set higher time limit and memory limit
set_time_limit(300); // 5 minutes
ini_set('memory_limit', '256M');

echo "<h1>Food Waste Log Generator</h1>";

// Use the existing database configuration
require_once 'config/database.php';

echo "<p>Using database configuration from config/database.php</p>";

try {
    // Connect directly to database using the existing configuration
    $conn = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "<p style='color:green'>Database connection successful!</p>";
    
    // Set start and end dates
    $startDate = new DateTime('2023-08-12');
    $endDate = new DateTime(); // Today
    $currentDate = clone $startDate;
    
    echo "<p>Date range: " . $startDate->format('Y-m-d') . " to " . $endDate->format('Y-m-d') . "</p>";
    
    // ONLY get specific users (5, 6, and 8)
    $allowedUsers = [5, 6, 8];
    $placeholders = implode(',', array_fill(0, count($allowedUsers), '?'));
    $userQuery = "SELECT user_id FROM users WHERE user_id IN ($placeholders) AND is_active = 1";
    $userStmt = $conn->prepare($userQuery);
    $userStmt->execute($allowedUsers);
    $users = $userStmt->fetchAll(PDO::FETCH_COLUMN);
    echo "<p>Found " . count($users) . " active users (IDs: " . implode(', ', $users) . ")</p>";
    
    // Check if we have users
    if (empty($users)) {
        die("<p style='color:red'>No specified users found in the database.</p>");
    }
    
    // Get all active locations (Kitchen, Storage, etc.)
    $locationQuery = "SELECT location_id, name FROM cleaning_locations WHERE is_active = 1";
    $locationStmt = $conn->prepare($locationQuery);
    $locationStmt->execute();
    $locations = $locationStmt->fetchAll(PDO::FETCH_ASSOC);
    echo "<p>Found " . count($locations) . " locations</p>";
    
    // Menu items for waste logs based on actual restaurant menu with prices - REMOVED BEVERAGES
    $menuItems = [
        // Main dishes
        ['item' => 'Doro Wot', 'price' => 13.99, 'description' => 'Traditional spicy chicken stew with boiled egg in berbere sauce', 'frequency' => 'regular'],
        ['item' => 'Kitfo', 'price' => 14.99, 'description' => 'Ethiopian style steak tartare, seasoned with mitmita and herb butter', 'frequency' => 'regular'],
        ['item' => 'Bozena Shiro', 'price' => 12.99, 'description' => 'Spiced chickpea flour stew with diced beef', 'frequency' => 'regular'],
        ['item' => 'Key Wot', 'price' => 11.99, 'description' => 'Spicy beef stew in rich berbere sauce', 'frequency' => 'regular'],
        ['item' => 'Special Tibs', 'price' => 15.99, 'description' => 'Prime beef cubes pan-fried with rosemary, onions, tomatoes and peppers', 'frequency' => 'regular'],
        ['item' => 'Awaze Tibs', 'price' => 13.99, 'description' => 'Spicy marinated beef cubes with special awaze sauce', 'frequency' => 'regular'],
        ['item' => 'Dulet', 'price' => 13.99, 'description' => 'Minced meat cooked with liver, tripe and Ethiopian spices', 'frequency' => 'regular'],
        ['item' => 'Gored Gored', 'price' => 13.99, 'description' => 'Cubed raw beef with berbere and spiced butter', 'frequency' => 'regular'],
        ['item' => 'Zilzil Tibs', 'price' => 14.99, 'description' => 'Strips of beef sautéed with onions, tomatoes and jalapeños', 'frequency' => 'regular'],
        ['item' => 'Lamb Tibs', 'price' => 14.99, 'description' => 'Sautéed lamb cubes with herbs, onions and peppers', 'frequency' => 'regular'],
        ['item' => 'Fish Dulet', 'price' => 12.99, 'description' => 'Minced fish cooked with Ethiopian herbs and spices', 'frequency' => 'regular'],
        ['item' => 'Rice with Beef', 'price' => 12.99, 'description' => 'Seasoned rice with slow-cooked beef', 'frequency' => 'regular'],
        
        // Vegan/Vegetarian dishes
        ['item' => 'Beyaynetu', 'price' => 11.99, 'description' => 'Mixed vegan platter with various vegetable and legume dishes', 'frequency' => 'regular'],
        ['item' => 'Shiro', 'price' => 10.99, 'description' => 'Smooth chickpea flour stew with Ethiopian spices', 'frequency' => 'regular'],
        ['item' => 'Misir Wot', 'price' => 9.99, 'description' => 'Red lentil stew spiced with berbere', 'frequency' => 'regular'],
        ['item' => 'Kik Alicha', 'price' => 9.99, 'description' => 'Yellow split pea stew with turmeric and herbs', 'frequency' => 'regular'],
        ['item' => 'Gomen', 'price' => 8.99, 'description' => 'Sautéed collard greens with garlic and spices', 'frequency' => 'regular'],
        ['item' => 'Atkilt Wot', 'price' => 9.99, 'description' => 'Mixed vegetable stew with potatoes, carrots and cabbage', 'frequency' => 'regular'],
        ['item' => 'Fosolia', 'price' => 8.99, 'description' => 'Green beans and carrots sautéed with onions', 'frequency' => 'regular'],
        ['item' => 'Injera Firfir', 'price' => 9.99, 'description' => 'Torn injera pieces mixed with berbere sauce and spices', 'frequency' => 'regular'],
        ['item' => 'Azifa', 'price' => 6.99, 'description' => 'Cold green lentil salad with mustard, lemon and herbs', 'frequency' => 'regular'],
        
        // Combination platters
        ['item' => 'Mahberawi', 'price' => 44.99, 'description' => 'Mixed meat platter for two with doro wot, key wot and tibs', 'frequency' => 'regular'],
        ['item' => 'Yefsik Mahberawi', 'price' => 54.99, 'description' => 'Family combination platter for four people with various meat dishes', 'frequency' => 'regular'],
        ['item' => 'Ge\'ez Special', 'price' => 41.99, 'description' => 'Signature platter with chef\'s selection of meat and vegetable dishes', 'frequency' => 'regular'],
        ['item' => 'Vegetarian Combo', 'price' => 34.99, 'description' => 'Combination of all vegan dishes for two people', 'frequency' => 'regular'],
        
        // Ingredients and prepped items - MARKED AS RARE
        ['item' => 'Berbere Spice', 'price' => 8.50, 'description' => 'House-made Ethiopian chili spice blend', 'frequency' => 'rare'],
        ['item' => 'Injera', 'price' => 3.99, 'description' => 'Fermented teff flour flatbread', 'frequency' => 'regular'],
        ['item' => 'Niter Kibbeh', 'price' => 7.50, 'description' => 'Clarified butter infused with Ethiopian herbs and spices', 'frequency' => 'rare'],
        ['item' => 'Mitmita Spice', 'price' => 8.50, 'description' => 'Spicy chili powder blend with cardamom and cloves', 'frequency' => 'rare'],
        ['item' => 'Shiro Powder', 'price' => 9.99, 'description' => 'Ground chickpea flour with Ethiopian spices', 'frequency' => 'rare'],
        ['item' => 'Teff Flour', 'price' => 12.99, 'description' => 'Fine Ethiopian grain flour for injera', 'frequency' => 'rare'],
        ['item' => 'Kocho', 'price' => 11.99, 'description' => 'Fermented enset (false banana) bread', 'frequency' => 'rare'],
        ['item' => 'Awaze Sauce', 'price' => 5.99, 'description' => 'Spicy paste made with berbere and Ethiopian spices', 'frequency' => 'rare'],
        
        // Appetizers and sides
        ['item' => 'Sambusa', 'price' => 5.99, 'description' => 'Crispy pastry filled with lentils or meat', 'frequency' => 'regular'],
        ['item' => 'Kategna', 'price' => 6.99, 'description' => 'Toasted injera with berbere and niter kibbeh', 'frequency' => 'regular'],
        ['item' => 'Ayib', 'price' => 4.99, 'description' => 'Mild Ethiopian cottage cheese', 'frequency' => 'regular'],
        ['item' => 'Fitfit', 'price' => 8.99, 'description' => 'Torn injera mixed with berbere sauce and vegetables', 'frequency' => 'regular']
    ];
    
    // Possible reasons for waste with realistic distribution - INCREASED CUSTOMER RETURN PROBABILITY
    $wasteReasons = [
        'Expired' => 10,
        'Spoiled' => 15,
        'Overproduction' => 20,
        'Preparation Error' => 15,
        'Customer Return' => 30, // Made this the most common reason (30%)
        'Quality Control' => 5,
        'Contamination' => 3,
        'Equipment Failure' => 1, 
        'Power Outage' => 1
    ];
    
    // Form with options for confirmation
    echo "<div style='margin-top:20px; padding:15px; background-color:#f0f0f0;'>";
    echo "<h3>Generate Food Waste Logs</h3>";
    echo "<p><strong>Important Information:</strong></p>";
    echo "<ul>";
    echo "<li>This will create realistic food waste logs for your Ethiopian restaurant</li>";
    echo "<li>Waste events will occur infrequently (max 2 per week, no more than 5 per month)</li>";
    echo "<li>Only users 5, 6, and 8 will be assigned as recorders</li>";
    echo "<li>Various waste reasons will be used with customer returns being most common</li>";
    echo "<li>Flour and staple ingredients will be wasted very rarely (max once every 2 months)</li>";
    echo "</ul>";
    echo "<p>Date range: " . $startDate->format('Y-m-d') . " to " . $endDate->format('Y-m-d') . "</p>";
    
    // Form with options
    echo "<form method='post'>";
    echo "<p><input type='checkbox' name='clear_logs' value='1' checked> Clear existing food waste logs before generating new ones</p>";
    echo "<input type='hidden' name='confirm' value='yes'>";
    echo "<input type='submit' value='Generate Food Waste Logs' style='background-color: #4CAF50; color: white; padding: 10px 15px; border: none; cursor: pointer;'>";
    echo "</form>";
    echo "</div>";
    
    // Only proceed if confirmed
    if (isset($_POST['confirm']) && $_POST['confirm'] === 'yes') {
        
        // Check if we should clear existing logs
        if (isset($_POST['clear_logs']) && $_POST['clear_logs'] == '1') {
            echo "<p>Clearing existing food waste logs...</p>";
            $conn->exec("DELETE FROM food_waste_log");
            $conn->exec("ALTER TABLE food_waste_log AUTO_INCREMENT = 1");
            echo "<p style='color:green'>Existing food waste logs cleared.</p>";
        }
        
        // Begin transaction for faster inserts
        $conn->beginTransaction();
        
        $totalInserted = 0;
        
        try {
            echo "<p>Starting food waste log generation...</p>";
            
            // Prepare the insert statement
            $insertStmt = $conn->prepare("
                INSERT INTO food_waste_log 
                (food_item, waste_type, reason, weight_kg, cost, waste_date, action_taken, 
                notes, recorded_by_user_id, created_at, updated_at)
                VALUES 
                (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            
            // Determine number of days to process
            $totalDays = $endDate->diff($startDate)->days + 1;
            
            // Initialize an array to track the last waste date for each item
            // to ensure we don't waste the same items too frequently
            $lastWasteDates = [];
            foreach ($menuItems as $item) {
                $lastWasteDates[$item['item']] = null;
            }
            
            // Keep track of waste events per month to limit to 5
            $wasteCountsByMonth = [];
            $currentMonth = $startDate->format('Y-m');
            $wasteCountsByMonth[$currentMonth] = 0;
            
            // Keep track of waste events per week to limit to 2
            $wasteCountsByWeek = [];
            $currentWeek = $startDate->format('YW');
            $wasteCountsByWeek[$currentWeek] = 0;
            
            // Create random waste logs with realistic patterns
            while ($currentDate <= $endDate) {
                $dateString = $currentDate->format('Y-m-d');
                $currentMonth = $currentDate->format('Y-m');
                $currentWeek = $currentDate->format('YW'); // ISO week number
                
                // Initialize counters if not set
                if (!isset($wasteCountsByMonth[$currentMonth])) {
                    $wasteCountsByMonth[$currentMonth] = 0;
                }
                if (!isset($wasteCountsByWeek[$currentWeek])) {
                    $wasteCountsByWeek[$currentWeek] = 0;
                }
                
                // Skip if we've already hit our monthly limit
                if ($wasteCountsByMonth[$currentMonth] >= 5) {
                    $currentDate->modify('+1 day');
                    continue;
                }
                
                // Skip if we've already hit our weekly limit
                if ($wasteCountsByWeek[$currentWeek] >= 2) {
                    $currentDate->modify('+1 day');
                    continue;
                }
                
                // Determine if waste happens on this day (much lower probability now)
                // Base chance is now just 10%
                $wasteChance = 10;
                
                // Higher chance on weekends (busier restaurant days)
                $dayOfWeek = $currentDate->format('N'); // 1 (Monday) to 7 (Sunday)
                if ($dayOfWeek >= 6) {
                    $wasteChance += 5; // Additional 5% chance on weekends
                }
                
                if (mt_rand(1, 100) <= $wasteChance) {
                    // Determine how many waste entries for this day (1 most of the time, rarely 2)
                    $numWasteEntries = (mt_rand(1, 100) <= 15) ? 2 : 1;
                    
                    // Make sure we don't exceed weekly limit
                    $numWasteEntries = min($numWasteEntries, 2 - $wasteCountsByWeek[$currentWeek]);
                    
                    // Make sure we don't exceed monthly limit
                    $numWasteEntries = min($numWasteEntries, 5 - $wasteCountsByMonth[$currentMonth]);
                    
                    // Shuffle menu items to randomize selection
                    shuffle($menuItems);
                    
                    for ($i = 0; $i < $numWasteEntries; $i++) {
                        // First, filter out rare items that were recently wasted (in last 60 days)
                        $availableItems = array_filter($menuItems, function($item) use ($currentDate, $lastWasteDates) {
                            // Skip rare items (like flour) if wasted in the last 60 days
                            if ($item['frequency'] === 'rare' && 
                                isset($lastWasteDates[$item['item']]) && 
                                $lastWasteDates[$item['item']] !== null && 
                                $currentDate->diff($lastWasteDates[$item['item']])->days < 60) {
                                return false;
                            }
                            
                            // Regular items need at least 14 days between waste events
                            if ($item['frequency'] === 'regular' && 
                                isset($lastWasteDates[$item['item']]) && 
                                $lastWasteDates[$item['item']] !== null && 
                                $currentDate->diff($lastWasteDates[$item['item']])->days < 14) {
                                return false;
                            }
                            
                            return true;
                        });
                        
                        // If no items are available, skip this waste entry
                        if (empty($availableItems)) {
                            continue;
                        }
                        
                        // Select random item from available ones
                        $selectedItem = $availableItems[array_rand($availableItems)];
                        
                        // Record this waste date for the item
                        $lastWasteDates[$selectedItem['item']] = clone $currentDate;
                        
                        // Select random waste reason based on weighted probabilities
                        $rand = mt_rand(1, 100);
                        $cumulativeProbability = 0;
                        $selectedReason = 'Other';
                        
                        foreach ($wasteReasons as $reason => $probability) {
                            $cumulativeProbability += $probability;
                            if ($rand <= $cumulativeProbability) {
                                $selectedReason = $reason;
                                break;
                            }
                        }
                        
                        // Generate random weight (realistic for food waste: 0.1 - 2.0 kg, smaller amounts now)
                        $weight = round(mt_rand(10, 200) / 100, 2);
                        
                        // Calculate total cost based on weight and cost per unit
                        $totalCost = round($weight * $selectedItem['price'], 2);
                        
                        // Determine waste type (mostly preparation or customer return)
                        $wasteTypes = ['Preparation', 'Service', 'Storage'];
                        if ($selectedReason === 'Customer Return') {
                            $wasteType = 'Service';
                        } else {
                            $wasteType = $wasteTypes[array_rand($wasteTypes)];
                        }
                        
                        // Generate a random time for the waste (between 8am and 10pm)
                        $hour = mt_rand(8, 22);
                        $minute = mt_rand(0, 59);
                        $second = mt_rand(0, 59);
                        $wasteDateTime = $dateString . ' ' . sprintf('%02d:%02d:%02d', $hour, $minute, $second);
                        
                        // Generate appropriate action taken based on reason
                        $actionTaken = '';
                        switch ($selectedReason) {
                            case 'Expired':
                            case 'Spoiled':
                                $actionTaken = 'Discarded according to waste protocol';
                                break;
                            case 'Overproduction':
                                $actionTaken = mt_rand(1, 100) <= 70 ? 
                                    'Staff meal' : 'Discarded according to waste protocol';
                                break;
                            case 'Preparation Error':
                                $actionTaken = 'Re-trained staff on proper preparation techniques';
                                break;
                            case 'Customer Return':
                                $actionTaken = 'Reviewed preparation process and adjusted seasoning';
                                break;
                            case 'Quality Control':
                                $actionTaken = 'Updated storage procedures';
                                break;
                            case 'Contamination':
                                $actionTaken = 'Deep cleaned affected area and re-trained staff';
                                break;
                            case 'Equipment Failure':
                                $actionTaken = 'Scheduled maintenance for ' . $currentDate->modify('+3 days')->format('Y-m-d');
                                $currentDate->modify('-3 days'); // Reset date modification
                                break;
                            case 'Power Outage':
                                $actionTaken = 'Reviewed backup power procedures';
                                break;
                            default:
                                $actionTaken = 'Reviewed and documented for future prevention';
                        }
                        
                        // Random notes based on reason
                        $notes = '';
                        switch ($selectedReason) {
                            case 'Expired':
                                $noteOptions = [
                                    'Item past use-by date during inventory check',
                                    'Berbere spice mix lost potency and aroma',
                                    'Prepped ingredients stored too long'
                                ];
                                $notes = $noteOptions[array_rand($noteOptions)];
                                break;
                            case 'Spoiled':
                                $noteOptions = [
                                    'Visual inspection showed signs of spoilage',
                                    'Raw meat showed discoloration',
                                    'Off odor detected in prepared stew'
                                ];
                                $notes = $noteOptions[array_rand($noteOptions)];
                                break;
                            case 'Overproduction':
                                $noteOptions = [
                                    'Excess prepared for slower than expected service',
                                    'Too many combination platters prepped for quiet evening',
                                    'Excess prepared for anticipated large group that canceled'
                                ];
                                $notes = $noteOptions[array_rand($noteOptions)];
                                break;
                            case 'Preparation Error':
                                $noteOptions = [
                                    'Tibs overcooked and dried out',
                                    'Kitfo over-seasoned with mitmita',
                                    'Berbere added in excess to stew',
                                    'Injera too thin and tore during cooking',
                                    'Shiro consistency too thick to serve'
                                ];
                                $notes = $noteOptions[array_rand($noteOptions)];
                                break;
                            case 'Customer Return':
                                $noteOptions = [
                                    'Customer reported Awaze Tibs too spicy',
                                    'Doro Wot returned for being undercooked',
                                    'Customer claimed Kitfo was not fresh',
                                    'Platter returned due to dietary restrictions not accommodated',
                                    'Incorrect dish served and returned untouched'
                                ];
                                $notes = $noteOptions[array_rand($noteOptions)];
                                break;
                            case 'Quality Control':
                                $noteOptions = [
                                    'Injera texture not meeting standards',
                                    'Berbere batch inconsistent with house flavor profile',
                                    'Stew viscosity too thin for service standards'
                                ];
                                $notes = $noteOptions[array_rand($noteOptions)];
                                break;
                            case 'Contamination':
                                $noteOptions = [
                                    'Cross-contamination between raw and cooked meat',
                                    'Allergen contamination in vegetarian dish preparation',
                                    'Foreign object found during final inspection'
                                ];
                                $notes = $noteOptions[array_rand($noteOptions)];
                                break;
                            case 'Equipment Failure':
                                $noteOptions = [
                                    'Refrigeration unit malfunction affected stored items',
                                    'Injera griddle temperature inconsistency ruined batch'
                                ];
                                $notes = $noteOptions[array_rand($noteOptions)];
                                break;
                            case 'Power Outage':
                                $noteOptions = [
                                    'Brief power cut affected refrigeration',
                                    'Extended outage required disposal of temperature-sensitive items'
                                ];
                                $notes = $noteOptions[array_rand($noteOptions)];
                                break;
                            default:
                                $notes = 'Documented for inventory tracking purposes';
                        }
                        
                        // Choose a random user
                        $recordedByUserId = $users[array_rand($users)];
                        
                        // Insert the waste log record
                        $created_at = $wasteDateTime;
                        $updated_at = $created_at;
                        
                        $insertStmt->execute([
                            $selectedItem['item'],
                            $wasteType,
                            $selectedReason,
                            $weight,
                            $totalCost,
                            $dateString,
                            $actionTaken,
                            $notes,
                            $recordedByUserId,
                            $created_at,
                            $updated_at
                        ]);
                        
                        $totalInserted++;
                        
                        // Update our weekly and monthly counters
                        $wasteCountsByWeek[$currentWeek]++;
                        $wasteCountsByMonth[$currentMonth]++;
                    }
                }
                
                // Move to next day
                $currentDate->modify('+1 day');
                
                // Output progress every 30 days
                $daysPassed = $startDate->diff($currentDate)->days;
                if ($daysPassed % 30 === 0) {
                    $percentComplete = ($currentDate->getTimestamp() - $startDate->getTimestamp()) / 
                                    ($endDate->getTimestamp() - $startDate->getTimestamp()) * 100;
                    echo "<p>Processed " . $daysPassed . " days (" . 
                         number_format($percentComplete, 1) . "% complete)...</p>";
                    flush();
                    ob_flush();
                }
            }
            
            // Commit the transaction
            $conn->commit();
            
            // Calculate average waste events per week
            $totalWeeks = ceil($totalDays / 7);
            $avgWastePerWeek = round($totalInserted / $totalWeeks, 1);
            
            // Calculate average waste events per month
            $totalMonths = ceil($totalDays / 30);
            $avgWastePerMonth = round($totalInserted / $totalMonths, 1);
            
            echo "<h2 style='color:green'>Success!</h2>";
            echo "<p>Successfully created {$totalInserted} food waste log entries over {$totalWeeks} weeks.</p>";
            echo "<p>Average of {$avgWastePerWeek} waste events per week.</p>";
            echo "<p>Average of {$avgWastePerMonth} waste events per month.</p>";
            echo "<p><a href='modules/waste/index.php' class='btn' style='background-color:#007bff; color:white; padding:5px 10px; text-decoration:none; display:inline-block;'>View Food Waste Logs</a></p>";
            
        } catch (Exception $e) {
            // Roll back the transaction on error
            $conn->rollBack();
            echo "<h2 style='color:red'>Error</h2>";
            echo "<p>An error occurred: " . $e->getMessage() . "</p>";
        }
    }
} catch (PDOException $e) {
    echo "<p style='color:red'>Database connection failed: " . $e->getMessage() . "</p>";
} 