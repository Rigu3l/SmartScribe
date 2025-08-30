<?php
// Simple test to check profile picture display
echo "<!DOCTYPE html>
<html>
<head>
    <meta charset='UTF-8'>
    <title>Profile Picture Test</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 20px; }
        .test { margin: 20px 0; padding: 15px; border: 1px solid #ccc; }
        .success { background: #d4edda; border-color: #c3e6cb; }
        .error { background: #f8d7da; border-color: #f5c6cb; }
        .info { background: #d1ecf1; border-color: #bee5eb; }
        img { max-width: 100px; max-height: 100px; border: 1px solid #ddd; }
    </style>
</head>
<body>
    <h1>🔍 Profile Picture Display Test</h1>

    <div class='test info'>
        <h2>Step 1: Check Database</h2>
        <p>Testing if user data exists in database...</p>";

try {
    require_once 'api/config/database.php';
    $db = getDbConnection();

    // Get user with ID 23 (our test user)
    $stmt = $db->prepare("SELECT id, name, email, profile_picture FROM users WHERE id = ?");
    $stmt->execute([23]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        echo "<p class='success'>✅ User found: {$user['name']} ({$user['email']})</p>";
        echo "<p><strong>Profile Picture Path:</strong> " . ($user['profile_picture'] ?: 'NULL') . "</p>";

        if ($user['profile_picture']) {
            $imagePath = "uploads/profile_pictures/profile_23_1756393498.jpg";
            echo "<p><strong>Expected Image URL:</strong> /$imagePath</p>";

            if (file_exists(__DIR__ . '/' . $imagePath)) {
                echo "<p class='success'>✅ Image file exists on server</p>";
                echo "<img src='/$imagePath' alt='Profile Picture'>";
            } else {
                echo "<p class='error'>❌ Image file does NOT exist on server</p>";
            }
        }
    } else {
        echo "<p class='error'>❌ User with ID 23 not found</p>";
    }

} catch (Exception $e) {
    echo "<p class='error'>❌ Database error: " . $e->getMessage() . "</p>";
}

echo "</div>

    <div class='test info'>
        <h2>Step 2: Test API Endpoint</h2>
        <p>Testing the profile API endpoint...</p>
        <div id='api-test'>Loading...</div>
    </div>

    <div class='test info'>
        <h2>Step 3: Browser JavaScript Test</h2>
        <p>Run this in your browser console:</p>
        <pre>
// Check localStorage
console.log('localStorage user:', localStorage.getItem('user'));
console.log('localStorage token:', localStorage.getItem('token'));

// Parse user data
const userData = localStorage.getItem('user');
if (userData) {
    const user = JSON.parse(userData);
    console.log('User object:', user);
    console.log('Profile picture:', user.profilePicture);

    // Test image URL
    if (user.profilePicture) {
        const imageUrl = `/${user.profilePicture}?t=${Date.now()}`;
        console.log('Image URL:', imageUrl);

        // Test fetch
        fetch(imageUrl).then(r => {
            console.log('Image fetch status:', r.status);
            if (r.status === 200) {
                console.log('✅ Image is accessible');
            } else {
                console.log('❌ Image not accessible');
            }
        });
    }
}
        </pre>
    </div>

    <div class='test info'>
        <h2>Step 4: Manual Image Test</h2>
        <p>Direct image access test:</p>
        <img src='/uploads/profile_pictures/profile_23_1756393498.jpg?t=123' alt='Direct Image Test' onerror='this.style.display=\"none\"'>
        <p>If you can see the image above, the file is accessible via web.</p>
    </div>

    <script>
        // Test API endpoint
        fetch('http://localhost/SmartScribe-main/public/index.php?resource=auth&action=profile', {
            method: 'GET',
            headers: {
                'Content-Type': 'application/json',
                'X-User-ID': '23'
            }
        })
        .then(response => response.json())
        .then(data => {
            document.getElementById('api-test').innerHTML = '<pre>' + JSON.stringify(data, null, 2) + '</pre>';
        })
        .catch(error => {
            document.getElementById('api-test').innerHTML = '<p class=\"error\">❌ API Error: ' + error.message + '</p>';
        });
    </script>
</body>
</html>";
?>