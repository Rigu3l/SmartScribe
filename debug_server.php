<?php
/**
 * Server Debugging Script
 * Deploy this file to your Railway server to diagnose issues
 */

// Start output buffering to capture all output
ob_start();

// Set error reporting to maximum
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

// Prevent caching
header('Cache-Control: no-cache, no-store, must-revalidate');
header('Pragma: no-cache');
header('Expires: 0');

echo "<h1>🚀 SmartScribe Server Debug Report</h1>";
echo "<pre>";
echo "Generated at: " . date('Y-m-d H:i:s T') . "\n";
echo "Server IP: " . ($_SERVER['SERVER_ADDR'] ?? 'Unknown') . "\n";
echo "Document Root: " . ($_SERVER['DOCUMENT_ROOT'] ?? 'Unknown') . "\n";
echo "Current Working Directory: " . getcwd() . "\n";
echo "Script Filename: " . __FILE__ . "\n";
echo "</pre>";

echo "<h2>📁 File System Check</h2>";
echo "<pre>";

// Check if key files exist
$files_to_check = [
    '.htaccess',
    'public/index.php',
    'dist/index.html',
    'api/index.php',
    'composer.json'
];

foreach ($files_to_check as $file) {
    $exists = file_exists($file);
    $readable = is_readable($file);
    $size = $exists ? filesize($file) : 0;
    echo sprintf("%-20s | Exists: %s | Readable: %s | Size: %d bytes\n",
        $file, $exists ? '✅' : '❌', $readable ? '✅' : '❌', $size);
}

// Check directory permissions
$dirs_to_check = ['.', 'public', 'dist', 'api'];
foreach ($dirs_to_check as $dir) {
    if (is_dir($dir)) {
        $perms = substr(sprintf('%o', fileperms($dir)), -4);
        $readable = is_readable($dir);
        $writable = is_writable($dir);
        echo sprintf("Dir %-15s | Perms: %s | Readable: %s | Writable: %s\n",
            $dir, $perms, $readable ? '✅' : '❌', $writable ? '✅' : '❌');
    } else {
        echo sprintf("Dir %-15s | NOT FOUND ❌\n", $dir);
    }
}

echo "</pre>";

echo "<h2>🌐 Server Configuration</h2>";
echo "<pre>";
echo "Server Software: " . ($_SERVER['SERVER_SOFTWARE'] ?? 'Unknown') . "\n";
echo "PHP Version: " . phpversion() . "\n";
echo "Loaded PHP Modules: " . implode(', ', get_loaded_extensions()) . "\n";
echo "Request Method: " . ($_SERVER['REQUEST_METHOD'] ?? 'Unknown') . "\n";
echo "Request URI: " . ($_SERVER['REQUEST_URI'] ?? 'Unknown') . "\n";
echo "User Agent: " . ($_SERVER['HTTP_USER_AGENT'] ?? 'Unknown') . "\n";
echo "</pre>";

echo "<h2>🔧 PHP Configuration</h2>";
echo "<pre>";
$php_config = [
    'memory_limit', 'max_execution_time', 'upload_max_filesize',
    'post_max_size', 'max_file_uploads', 'max_input_time'
];

foreach ($php_config as $setting) {
    echo sprintf("%-20s: %s\n", $setting, ini_get($setting));
}
echo "</pre>";

echo "<h2>📊 Environment Variables</h2>";
echo "<pre>";
foreach ($_ENV as $key => $value) {
    // Hide sensitive values
    if (strpos(strtolower($key), 'password') !== false ||
        strpos(strtolower($key), 'secret') !== false ||
        strpos(strtolower($key), 'key') !== false) {
        echo "$key: [HIDDEN]\n";
    } else {
        echo "$key: $value\n";
    }
}
echo "</pre>";

echo "<h2>🧪 File Upload Test</h2>";
echo "<pre>";
$test_file = 'test_upload.txt';
file_put_contents($test_file, 'Test file created at ' . date('Y-m-d H:i:s'));
if (file_exists($test_file)) {
    echo "✅ File creation: SUCCESS\n";
    unlink($test_file);
} else {
    echo "❌ File creation: FAILED\n";
}
echo "</pre>";

echo "<h2>🔗 Network Connections</h2>";
echo "<pre>";
try {
    $database_check = @fsockopen('localhost', 3306, $errno, $errstr, 5);
    if ($database_check) {
        echo "✅ Database (MySQL): CONNECTED\n";
        fclose($database_check);
    } else {
        echo "❌ Database (MySQL): FAILED - $errstr ($errno)\n";
    }
} catch (Exception $e) {
    echo "❌ Database check failed: " . $e->getMessage() . "\n";
}
echo "</pre>";

echo "<h2>📝 Recent Errors</h2>";
echo "<pre>";
echo "Last PHP error: " . (error_get_last() ? print_r(error_get_last(), true) : 'None') . "\n";
echo "</pre>";

echo "<h2>🎯 Recommendations</h2>";
echo "<ul>";
if (!file_exists('dist/index.html')) {
    echo "<li>❌ Missing dist/index.html - Run 'npm run build' first</li>";
}
if (!is_readable('public/index.php')) {
    echo "<li>❌ public/index.php not readable - Check file permissions</li>";
}
if (!file_exists('.htaccess')) {
    echo "<li>⚠️ No .htaccess file found</li>";
}
echo "<li>✅ Deploy this debug file to https://smartscribe.up.railway.app/debug_server.php</li>";
echo "<li>✅ Check Railway dashboard logs for more details</li>";
echo "</ul>";

echo "<hr>";
echo "<p><strong>Debug file created by SmartScribe Server Diagnostics</strong></p>";
echo "<p>If you can see this page, your PHP server is working correctly!</p>";

// Get the full output buffer
$output = ob_get_clean();

// Set content type and output
header('Content-Type: text/html; charset=utf-8');
echo $output;
?>