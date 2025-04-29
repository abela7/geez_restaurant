<?php
// Enable error reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Get the file path from the URL
$file = isset($_GET['file']) ? $_GET['file'] : '';

if (!file_exists($file)) {
    die("File not found: $file");
}

if (is_dir($file)) {
    die("$file is a directory, not a file.");
}

// Check if file is a PHP file to show in formatted view
$extension = pathinfo($file, PATHINFO_EXTENSION);
$isPHP = strtolower($extension) === 'php';

echo "<h1>File Content: " . basename($file) . "</h1>";
echo "<p><strong>Full path:</strong> $file</p>";

// Get file information
$fileInfo = stat($file);
echo "<p><strong>Last modified:</strong> " . date('Y-m-d H:i:s', $fileInfo['mtime']) . "</p>";
echo "<p><strong>Size:</strong> " . number_format($fileInfo['size']) . " bytes</p>";

// If it's a PHP file, provide option to copy just the file path
if ($isPHP) {
    echo "<p><input type='text' value='$file' size='60' readonly onclick='this.select()'> <button onclick='copyPath()'>Copy Path</button></p>";
    echo "<script>
    function copyPath() {
        var path = document.querySelector('input');
        path.select();
        document.execCommand('copy');
        alert('Path copied to clipboard!');
    }
    </script>";
}

// Display file content
echo "<h3>Content:</h3>";

// For binary files or images, don't try to display content
$binaryExtensions = ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'ico', 'pdf', 'doc', 'docx', 'xls', 'xlsx', 'zip', 'rar', 'exe', 'dll'];
if (in_array(strtolower($extension), $binaryExtensions)) {
    echo "<p>This appears to be a binary file. Content cannot be displayed.</p>";
    if (in_array(strtolower($extension), ['jpg', 'jpeg', 'png', 'gif', 'bmp'])) {
        echo "<p><img src='" . str_replace($_SERVER['DOCUMENT_ROOT'], '', $file) . "' style='max-width: 300px;'></p>";
    }
} else {
    // Read file content
    $content = file_get_contents($file);
    
    // Display file content
    echo "<div style='background-color: #f5f5f5; padding: 10px; border: 1px solid #ddd; white-space: pre-wrap; font-family: monospace; max-height: 600px; overflow: auto;'>";
    echo htmlspecialchars($content);
    echo "</div>";
}

// Add a back link
echo "<p><a href='javascript:history.back()'>Back to file explorer</a></p>";
?> 