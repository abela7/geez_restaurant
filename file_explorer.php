<?php
// Enable error reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

echo "<h1>Server File Explorer</h1>";

// Get the current directory to explore
$currentPath = isset($_GET['path']) ? $_GET['path'] : dirname(__FILE__);
$parentPath = dirname($currentPath);

echo "<h3>Current location: {$currentPath}</h3>";
echo "<p><a href='?path={$parentPath}'>Go to parent directory</a></p>";

// Function to search for files in directories and subdirectories
function searchFiles($directory, $keywords) {
    $results = [];
    
    // Get all files in the current directory
    $files = scandir($directory);
    
    foreach ($files as $file) {
        if ($file == '.' || $file == '..') continue;
        
        $path = $directory . '/' . $file;
        
        // Check if the file name contains any of the keywords
        foreach ($keywords as $keyword) {
            if (stripos($file, $keyword) !== false) {
                $results[] = $path;
                break;
            }
        }
        
        // If it's a directory, search inside it (recursive)
        if (is_dir($path)) {
            $subResults = searchFiles($path, $keywords);
            $results = array_merge($results, $subResults);
        }
    }
    
    return $results;
}

// Search form
echo "<div style='margin: 20px 0; padding: 10px; background-color: #f0f0f0;'>";
echo "<h3>Search for Files</h3>";
echo "<form method='post'>";
echo "<p>Enter keywords (separated by spaces): <input type='text' name='search' size='40' value='config database'></p>";
echo "<p>Start directory: <input type='text' name='search_path' size='40' value='" . realpath($_SERVER['DOCUMENT_ROOT'] . '/..') . "'></p>";
echo "<input type='submit' value='Search'>";
echo "</form>";
echo "</div>";

// Handle search
if (isset($_POST['search'])) {
    $searchTerms = explode(' ', $_POST['search']);
    $searchPath = !empty($_POST['search_path']) ? $_POST['search_path'] : $_SERVER['DOCUMENT_ROOT'];
    
    echo "<h3>Searching for: " . implode(', ', $searchTerms) . " in {$searchPath}</h3>";
    
    try {
        $results = searchFiles($searchPath, $searchTerms);
        
        if (count($results) > 0) {
            echo "<h4>Found " . count($results) . " matching files:</h4>";
            echo "<ul>";
            foreach ($results as $file) {
                echo "<li>";
                echo $file;
                if (is_dir($file)) {
                    echo " [directory] <a href='?path={$file}'>Explore</a>";
                } else {
                    echo " [file] <a href='file_content.php?file={$file}' target='_blank'>View content</a>";
                }
                echo "</li>";
            }
            echo "</ul>";
        } else {
            echo "<p>No files found matching your search terms.</p>";
        }
    } catch (Exception $e) {
        echo "<p style='color:red'>Error during search: " . $e->getMessage() . "</p>";
    }
}

// List directories and files
try {
    $files = scandir($currentPath);
    
    echo "<h3>Directories</h3>";
    echo "<ul>";
    foreach ($files as $file) {
        if ($file == '.' || $file == '..') continue;
        
        $path = $currentPath . '/' . $file;
        
        if (is_dir($path)) {
            echo "<li><a href='?path={$path}'>{$file}</a></li>";
        }
    }
    echo "</ul>";
    
    echo "<h3>Files</h3>";
    echo "<ul>";
    foreach ($files as $file) {
        if ($file == '.' || $file == '..') continue;
        
        $path = $currentPath . '/' . $file;
        
        if (!is_dir($path)) {
            echo "<li>{$file} <a href='file_content.php?file={$path}' target='_blank'>View content</a></li>";
        }
    }
    echo "</ul>";
} catch (Exception $e) {
    echo "<p style='color:red'>Error listing directory contents: " . $e->getMessage() . "</p>";
}
?> 