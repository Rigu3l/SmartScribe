<?php
// Test script to verify notes display fix
echo "<!DOCTYPE html>
<html>
<head>
    <meta charset='UTF-8'>
    <title>Test Notes Fix</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 20px; }
        .test { margin: 20px 0; padding: 15px; border: 1px solid #ccc; border-radius: 8px; }
        .success { background: #d4edda; border-color: #c3e6cb; }
        .error { background: #f8d7da; border-color: #f5c6cb; }
        .info { background: #d1ecf1; border-color: #bee5eb; }
        .code { background: #2d3748; color: #e2e8f0; padding: 10px; border-radius: 4px; font-family: monospace; margin: 10px 0; }
        table { border-collapse: collapse; width: 100%; margin: 10px 0; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
    </style>
</head>
<body>
    <h1>🧪 Test Notes Display Fix</h1>

    <div class='test info'>
        <h2>1. Database Status</h2>";

try {
    require_once 'api/config/database.php';
    $db = getDbConnection();

    // Check notes count
    $stmt = $db->query("SELECT COUNT(*) as total FROM notes");
    $total = $stmt->fetch();
    echo "<p><strong>✅ Total notes in database:</strong> " . $total['total'] . "</p>";

    // Check users
    $stmt = $db->query("SELECT id, name, email, profile_picture FROM users LIMIT 5");
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo "<p><strong>✅ Users in database:</strong></p><table><tr><th>ID</th><th>Name</th><th>Email</th><th>Profile Picture</th></tr>";
    foreach ($users as $user) {
        echo "<tr><td>{$user['id']}</td><td>{$user['name']}</td><td>{$user['email']}</td><td>" . ($user['profile_picture'] ?: 'NULL') . "</td></tr>";
    }
    echo "</table>";

    // Check notes for user 23
    $stmt = $db->prepare("SELECT id, title, created_at FROM notes WHERE user_id = ?");
    $stmt->execute([23]);
    $userNotes = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo "<p><strong>✅ Notes for user 23:</strong> " . count($userNotes) . " notes</p>";
    if (count($userNotes) > 0) {
        echo "<table><tr><th>ID</th><th>Title</th><th>Created</th></tr>";
        foreach ($userNotes as $note) {
            echo "<tr><td>{$note['id']}</td><td>{$note['title']}</td><td>{$note['created_at']}</td></tr>";
        }
        echo "</table>";
    }

} catch (Exception $e) {
    echo "<p class='error'>❌ Database error: " . $e->getMessage() . "</p>";
}

echo "</div>

    <div class='test success'>
        <h2>2. API Endpoint Test</h2>
        <div id='api-test'>Testing API...</div>
    </div>

    <div class='test info'>
        <h2>3. Browser Test Instructions</h2>
        <p><strong>To test the notes display fix:</strong></p>
        <ol>
            <li>Open your browser and navigate to <code>http://localhost/SmartScribe-main/</code></li>
            <li>Log in with user credentials (ID: 23)</li>
            <li>Navigate to the Notes page</li>
            <li>Open browser Developer Tools (F12)</li>
            <li>Check the Console tab for debug messages</li>
            <li>Look for messages like 'Notes fetched successfully' and 'User data from localStorage'</li>
        </ol>

        <h3>Expected Console Output:</h3>
        <div class='code'>
✅ User data from localStorage: {id: 23, name: 'Test User', ...}
✅ Notes API response: {data: {...}, success: true}
✅ Notes fetched successfully: 3 notes
✅ Processing notes data: [...]
        </div>
    </div>

    <div class='test info'>
        <h2>4. Manual API Test</h2>
        <p>Test the API directly:</p>
        <div class='code'>
curl -X GET 'http://localhost/SmartScribe-main/public/index.php?resource=notes' \\
  -H 'Content-Type: application/json' \\
  -H 'X-User-ID: 23'
        </div>
        <p><strong>Expected response:</strong> JSON with 3 notes for user 23</p>
    </div>

    <div class='test success'>
        <h2>5. Vue.js Component Test</h2>
        <p>Add this to your browser console to test Vue components:</p>
        <div class='code'>
// Test if Vue components are working
console.log('Vue components test:');
console.log('Notes container:', document.querySelector('.grid.grid-cols-1.md\\\\:grid-cols-2.lg\\\\:grid-cols-3'));
console.log('Note cards:', document.querySelectorAll('.bg-gray-800.rounded-lg').length);

// Test user profile in header
console.log('User profile in header:');
console.log('Profile image:', document.querySelector('header img[alt=\"Profile\"]'));
console.log('User name:', document.querySelector('header .text-sm.text-gray-300'));
        </div>
    </div>

    <div class='test info'>
        <h2>6. Troubleshooting</h2>

        <h3>If notes still don't show:</h3>
        <ul>
            <li><strong>Check browser console</strong> for JavaScript errors</li>
            <li><strong>Verify user authentication</strong> - ensure you're logged in</li>
            <li><strong>Test API manually</strong> using the curl command above</li>
            <li><strong>Check localStorage</strong> for user data</li>
            <li><strong>Clear browser cache</strong> and reload the page</li>
        </ul>

        <h3>If profile picture doesn't sync:</h3>
        <ul>
            <li><strong>Check image URL</strong> in browser network tab</li>
            <li><strong>Verify image file exists</strong> on server</li>
            <li><strong>Test direct image access</strong> in browser</li>
            <li><strong>Check getProfilePictureUrl function</strong> in console</li>
        </ul>
    </div>

    <script>
        // Test API endpoint
        fetch('http://localhost/SmartScribe-main/public/index.php?resource=notes', {
            method: 'GET',
            headers: {
                'Content-Type': 'application/json',
                'X-User-ID': '23'
            }
        })
        .then(response => response.json())
        .then(data => {
            const resultDiv = document.getElementById('api-test');
            if (data.success) {
                resultDiv.innerHTML = '<div class=\"success\">✅ API Working! Found ' + (data.data ? data.data.length : 0) + ' notes for user 23</div>';
                if (data.data && data.data.length > 0) {
                    resultDiv.innerHTML += '<div class=\"code\">Sample note: ' + data.data[0].title + '</div>';
                }
            } else {
                resultDiv.innerHTML = '<div class=\"error\">❌ API Error: ' + (data.error || 'Unknown error') + '</div>';
            }
        })
        .catch(error => {
            document.getElementById('api-test').innerHTML = '<div class=\"error\">❌ Network Error: ' + error.message + '</div>';
        });

        // Add test functions to window
        window.testNotesAPI = function() {
            console.log('=== MANUAL API TEST ===');
            fetch('http://localhost/SmartScribe-main/public/index.php?resource=notes', {
                headers: { 'X-User-ID': '23' }
            })
            .then(r => r.json())
            .then(data => console.log('API Test Result:', data));
        };

        window.testUserProfile = function() {
            console.log('=== USER PROFILE TEST ===');
            const userData = localStorage.getItem('user');
            if (userData) {
                const user = JSON.parse(userData);
                console.log('User from localStorage:', user);
            } else {
                console.error('No user data in localStorage');
            }
        };

        console.log('🔧 Test functions available:');
        console.log('- testNotesAPI()');
        console.log('- testUserProfile()');
    </script>

    <button onclick='testNotesAPI()' style='margin: 10px; padding: 10px 20px; background: #007bff; color: white; border: none; border-radius: 4px; cursor: pointer;'>Test Notes API</button>
    <button onclick='testUserProfile()' style='margin: 10px; padding: 10px 20px; background: #28a745; color: white; border: none; border-radius: 4px; cursor: pointer;'>Test User Profile</button>
</body>
</html>";
?>