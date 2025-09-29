<?php
/**
 * Apache Configuration Test
 * Tests various Apache modules and configurations
 */

header('Content-Type: text/plain; charset=utf-8');

echo "=== APACHE CONFIGURATION TEST ===\n";
echo "Timestamp: " . date('Y-m-d H:i:s T') . "\n\n";

// Test 1: Check if mod_rewrite is loaded
echo "1. MOD_REWRITE TEST:\n";
if (function_exists('apache_get_modules') && in_array('mod_rewrite', apache_get_modules())) {
    echo "   ✅ mod_rewrite is loaded\n";
} else {
    echo "   ⚠️ mod_rewrite may not be loaded\n";
}

// Test 2: Check .htaccess functionality
echo "\n2. HTACCESS TEST:\n";
$test_file = '.htaccess_test_' . time();
file_put_contents($test_file, 'Test file for .htaccess testing');
if (file_exists($test_file)) {
    echo "   ✅ Can create files in web root\n";
    unlink($test_file);
} else {
    echo "   ❌ Cannot create files in web root\n";
}

// Test 3: Check PHP functionality
echo "\n3. PHP FUNCTIONALITY TEST:\n";
echo "   ✅ PHP Version: " . phpversion() . "\n";
echo "   ✅ GD Library: " . (extension_loaded('gd') ? 'Loaded' : 'Not loaded') . "\n";
echo "   ✅ PDO: " . (extension_loaded('pdo') ? 'Loaded' : 'Not loaded') . "\n";
echo "   ✅ MySQLi: " . (extension_loaded('mysqli') ? 'Loaded' : 'Not loaded') . "\n";

// Test 4: Check file permissions
echo "\n4. FILE PERMISSIONS TEST:\n";
$files_to_test = ['.', 'public', 'dist'];
foreach ($files_to_test as $path) {
    if (is_dir($path)) {
        $perms = substr(sprintf('%o', fileperms($path)), -4);
        echo "   $path: $perms\n";
    } else {
        echo "   $path: NOT FOUND\n";
    }
}

// Test 5: Check environment
echo "\n5. ENVIRONMENT TEST:\n";
echo "   Document Root: " . ($_SERVER['DOCUMENT_ROOT'] ?? 'Not set') . "\n";
echo "   Script Name: " . ($_SERVER['SCRIPT_NAME'] ?? 'Not set') . "\n";
echo "   Request URI: " . ($_SERVER['REQUEST_URI'] ?? 'Not set') . "\n";

// Test 6: Check for common issues
echo "\n6. COMMON ISSUES CHECK:\n";
$issues = [];

// Check for missing dist directory
if (!is_dir('dist') || !file_exists('dist/index.html')) {
    $issues[] = "Missing or incomplete dist/ directory - run 'npm run build'";
}

// Check for missing public/index.php
if (!file_exists('public/index.php')) {
    $issues[] = "Missing public/index.php";
}

// Check for overly restrictive .htaccess
if (file_exists('.htaccess')) {
    $htaccess = file_get_contents('.htaccess');
    if (strpos($htaccess, 'Require all denied') !== false) {
        $issues[] = "Found 'Require all denied' in .htaccess - may be too restrictive";
    }
}

if (empty($issues)) {
    echo "   ✅ No common issues detected\n";
} else {
    foreach ($issues as $issue) {
        echo "   ⚠️ $issue\n";
    }
}

echo "\n=== TEST COMPLETE ===\n";
echo "If you're still having issues, check:\n";
echo "1. Railway dashboard logs\n";
echo "2. File permissions (755 for dirs, 644 for files)\n";
echo "3. Apache error logs\n";
echo "4. PHP error logs\n";
?>