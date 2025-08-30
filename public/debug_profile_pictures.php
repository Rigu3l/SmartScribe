<?php
// Comprehensive debug script for profile picture display issues
require_once 'api/config/database.php';

echo "<!DOCTYPE html>
<html lang='en'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>Profile Picture Debug Test</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }
        .test-section { background: white; padding: 20px; margin: 20px 0; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        .success { color: #10b981; }
        .error { color: #ef4444; }
        .warning { color: #f59e0b; }
        .info { color: #3b82f6; }
        .code { background: #f3f4f6; padding: 10px; border-radius: 4px; font-family: monospace; margin: 10px 0; }
        img { max-width: 100px; max-height: 100px; border: 1px solid #ddd; border-radius: 4px; }
        table { border-collapse: collapse; width: 100%; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
    </style>
</head>
<body>
    <h1>🔍 Profile Picture Debug Test</h1>
    <p>This test will help identify issues with profile picture display in the headers.</p>
";

// Test 1: Database Connection and User Data
echo "<div class='test-section'>
    <h2>🗄️ Test 1: Database Connection & User Data</h2>";

try {
    $db = getDbConnection();
    echo "<p class='success'>✅ Database connection successful</p>";

    // Get all users
    $stmt = $db->query("SELECT id, name, email, profile_picture FROM users ORDER BY id");
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo "<h3>Users in Database:</h3>";
    echo "<table>";
    echo "<tr><th>ID</th><th>Name</th><th>Email</th><th>Profile Picture Path</th><th>Status</th></tr>";

    foreach ($users as $user) {
        $status = $user['profile_picture'] ? "<span class='success'>✅ Has Picture</span>" : "<span class='warning'>⚠️ No Picture</span>";
        echo "<tr>";
        echo "<td>{$user['id']}</td>";
        echo "<td>{$user['name']}</td>";
        echo "<td>{$user['email']}</td>";
        echo "<td>{$user['profile_picture']}</td>";
        echo "<td>$status</td>";
        echo "</tr>";
    }
    echo "</table>";

} catch (Exception $e) {
    echo "<p class='error'>❌ Database error: " . $e->getMessage() . "</p>";
}

echo "</div>";

// Test 2: File System Check
echo "<div class='test-section'>
    <h2>📁 Test 2: File System Check</h2>";

$uploadDir = __DIR__ . '/public/uploads/profile_pictures/';
echo "<p><strong>Upload Directory:</strong> $uploadDir</p>";

if (is_dir($uploadDir)) {
    echo "<p class='success'>✅ Upload directory exists</p>";
    echo "<p><strong>Directory Permissions:</strong> " . substr(sprintf('%o', fileperms($uploadDir)), -4) . "</p>";
    echo "<p><strong>Directory Writable:</strong> " . (is_writable($uploadDir) ? "<span class='success'>✅ Yes</span>" : "<span class='error'>❌ No</span>") . "</p>";

    $files = scandir($uploadDir);
    $imageFiles = array_filter($files, function($file) {
        return !in_array($file, ['.', '..']) && preg_match('/\.(jpg|jpeg|png|gif|webp)$/i', $file);
    });

    echo "<h3>Profile Picture Files:</h3>";
    if (count($imageFiles) > 0) {
        echo "<table>";
        echo "<tr><th>Filename</th><th>Size</th><th>Permissions</th><th>Web URL</th><th>Preview</th></tr>";

        foreach ($imageFiles as $file) {
            $filePath = $uploadDir . $file;
            $fileSize = filesize($filePath);
            $permissions = substr(sprintf('%o', fileperms($filePath)), -4);
            $webUrl = "/uploads/profile_pictures/$file";

            echo "<tr>";
            echo "<td>$file</td>";
            echo "<td>" . number_format($fileSize) . " bytes</td>";
            echo "<td>$permissions</td>";
            echo "<td><a href='$webUrl' target='_blank'>$webUrl</a></td>";
            echo "<td><img src='$webUrl' alt='$file' onerror=\"this.style.display='none'\"></td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<p class='warning'>⚠️ No profile picture files found</p>";
    }
} else {
    echo "<p class='error'>❌ Upload directory does not exist</p>";
}

echo "</div>";

// Test 3: API Endpoint Test
echo "<div class='test-section'>
    <h2>🌐 Test 3: API Endpoint Test</h2>
    <p>Testing the /api/auth/profile endpoint...</p>";

$userId = 23; // Test with user ID 23
$apiUrl = "http://localhost/api/auth/profile";

echo "<div class='code'>
// Simulated API call for user ID: $userId
// URL: $apiUrl
// Headers: X-User-ID: $userId
</div>";

echo "<p><strong>Expected Response:</strong></p>
<div class='code'>
{
  \"success\": true,
  \"user\": {
    \"id\": $userId,
    \"name\": \"Test User\",
    \"email\": \"test@example.com\",
    \"profile_picture\": \"uploads/profile_pictures/profile_23_1756393498.jpg\"
  }
}
</div>";

echo "</div>";

// Test 4: Vue.js Implementation Test
echo "<div class='test-section'>
    <h2>⚛️ Test 4: Vue.js Implementation Test</h2>
    <p>Testing the Vue.js profile picture URL construction...</p>";

$profilePicturePath = "uploads/profile_pictures/profile_23_1756393498.jpg";
$timestamp = time();
$constructedUrl = "/$profilePicturePath?t=$timestamp";

echo "<div class='code'>
// Vue.js getProfilePictureUrl function simulation
const profilePicturePath = '$profilePicturePath';
const timestamp = $timestamp;
const imageUrl = '/\${profilePicturePath}?t=\${timestamp}';

// Result: $constructedUrl
</div>";

echo "<p><strong>Constructed Image URL:</strong> <a href='$constructedUrl' target='_blank'>$constructedUrl</a></p>";

// Test if the URL is accessible
$fullPath = __DIR__ . "/public/$profilePicturePath";
if (file_exists($fullPath)) {
    echo "<p class='success'>✅ Image file exists and should be accessible</p>";
    echo "<img src='$constructedUrl' alt='Test Image' onerror=\"this.outerHTML='<p class=\\'error\\'>❌ Image failed to load</p>'\">";
} else {
    echo "<p class='error'>❌ Image file does not exist</p>";
}

echo "</div>";

// Test 5: Browser Console Debug Info
echo "<div class='test-section'>
    <h2>🔧 Test 5: Browser Console Debug Info</h2>
    <p>Add this JavaScript to your browser console to debug profile picture loading:</p>

    <div class='code'>
// Check user data in localStorage
const userData = localStorage.getItem('user');
console.log('User data from localStorage:', userData);

// Check if user has profile picture
if (userData) {
    const user = JSON.parse(userData);
    console.log('User profile picture path:', user.profilePicture);

    // Test image URL construction
    const imageUrl = user.profilePicture ? `/\${user.profilePicture}?t=\${Date.now()}` : null;
    console.log('Constructed image URL:', imageUrl);

    // Test if image loads
    if (imageUrl) {
        const img = new Image();
        img.onload = () => console.log('✅ Image loaded successfully:', imageUrl);
        img.onerror = () => console.log('❌ Image failed to load:', imageUrl);
        img.src = imageUrl;
    }
}
    </div>
</div>";

// Test 6: Recommendations
echo "<div class='test-section'>
    <h2>💡 Test 6: Troubleshooting Recommendations</h2>

    <h3>If profile pictures still don't display:</h3>
    <ol>
        <li><strong>Check Browser Console:</strong> Open DevTools (F12) and look for image loading errors</li>
        <li><strong>Clear Browser Cache:</strong> Hard refresh (Ctrl+F5) to clear cached images</li>
        <li><strong>Check Network Tab:</strong> Verify image requests are successful (200 status)</li>
        <li><strong>Test Direct URL:</strong> Try accessing image URLs directly in browser</li>
        <li><strong>Verify User Authentication:</strong> Ensure user is properly logged in with X-User-ID header</li>
        <li><strong>Check Vue.js Reactivity:</strong> Verify user data is reactive and updating properly</li>
    </ol>

    <h3>Quick Debug Steps:</h3>
    <div class='code'>
// 1. Check if user data exists
console.log('Current user:', user.value);

// 2. Check profile picture path
console.log('Profile picture path:', user.value?.profilePicture);

// 3. Test URL construction
const testUrl = getProfilePictureUrl(user.value?.profilePicture);
console.log('Test URL:', testUrl);

// 4. Test image loading
if (testUrl) {
    fetch(testUrl).then(r => console.log('Image fetch result:', r.status));
}
    </div>
</div>";

echo "</body></html>";
?>