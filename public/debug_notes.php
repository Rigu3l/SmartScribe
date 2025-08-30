<?php
// Debug script to check notes display functionality
echo "<!DOCTYPE html>
<html>
<head>
    <meta charset='UTF-8'>
    <title>Notes Debug Test</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 20px; }
        .test { margin: 20px 0; padding: 15px; border: 1px solid #ccc; }
        .success { background: #d4edda; border-color: #c3e6cb; }
        .error { background: #f8d7da; border-color: #f5c6cb; }
        .info { background: #d1ecf1; border-color: #bee5eb; }
        .code { background: #2d3748; color: #e2e8f0; padding: 10px; border-radius: 4px; font-family: monospace; overflow-x: auto; }
        table { border-collapse: collapse; width: 100%; margin: 10px 0; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
    </style>
</head>
<body>
    <h1>🔍 Notes Display Debug Test</h1>

    <div class='test info'>
        <h2>1. Database Check</h2>
        <p>Checking if notes exist in the database...</p>";

try {
    require_once 'api/config/database.php';
    $db = getDbConnection();

    // Check total notes
    $stmt = $db->query("SELECT COUNT(*) as total FROM notes");
    $total = $stmt->fetch();
    echo "<p><strong>Total notes in database:</strong> " . $total['total'] . "</p>";

    // Check notes by user
    $stmt = $db->query("SELECT user_id, COUNT(*) as count FROM notes GROUP BY user_id");
    $userCounts = $stmt->fetchAll();
    echo "<p><strong>Notes by user:</strong></p>";
    echo "<table><tr><th>User ID</th><th>Note Count</th></tr>";
    foreach ($userCounts as $count) {
        echo "<tr><td>{$count['user_id']}</td><td>{$count['count']}</td></tr>";
    }
    echo "</table>";

    // Check recent notes
    $stmt = $db->query("SELECT id, user_id, title, created_at FROM notes ORDER BY created_at DESC LIMIT 5");
    $recentNotes = $stmt->fetchAll();
    echo "<p><strong>Recent notes:</strong></p>";
    echo "<table><tr><th>ID</th><th>User ID</th><th>Title</th><th>Created</th></tr>";
    foreach ($recentNotes as $note) {
        echo "<tr><td>{$note['id']}</td><td>{$note['user_id']}</td><td>{$note['title']}</td><td>{$note['created_at']}</td></tr>";
    }
    echo "</table>";

} catch (Exception $e) {
    echo "<p class='error'>❌ Database error: " . $e->getMessage() . "</p>";
}

echo "</div>

    <div class='test info'>
        <h2>2. API Endpoint Test</h2>
        <p>Testing the notes API endpoint...</p>
        <div id='api-test'>Loading...</div>
    </div>

    <div class='test info'>
        <h2>3. Browser Console Debug</h2>
        <p>Add this to your browser console to debug notes loading:</p>
        <div class='code'>
// Check if user is authenticated
console.log('localStorage user:', localStorage.getItem('user'));
console.log('localStorage token:', localStorage.getItem('token'));

// Test notes API call
fetch('http://localhost/SmartScribe-main/public/index.php?resource=notes', {
    method: 'GET',
    headers: {
        'Content-Type': 'application/json',
        'X-User-ID': '23'  // Test with user ID 23
    }
})
.then(response => response.json())
.then(data => {
    console.log('Notes API Response:', data);
    if (data.success && data.data) {
        console.log('✅ Notes loaded successfully:', data.data.length, 'notes');
    } else {
        console.log('❌ Notes API failed:', data.error || 'Unknown error');
    }
})
.catch(error => console.error('Notes API Error:', error));
        </div>
    </div>

    <div class='test info'>
        <h2>4. Vue.js Component Debug</h2>
        <p>Add this to your NotesView.vue component to debug:</p>
        <div class='code'>
// Add to NotesView.vue script section
console.log('NotesView mounted');
console.log('User data:', user.value);
console.log('Notes data:', notes.value);
console.log('Loading state:', loadingNotes.value);
console.log('Error state:', notesError.value);

// Test API call manually
const testApiCall = async () => {
    try {
        console.log('Testing API call...');
        const response = await fetch('http://localhost/SmartScribe-main/public/index.php?resource=notes', {
            headers: { 'X-User-ID': '23' }
        });
        const data = await response.json();
        console.log('Manual API test result:', data);
    } catch (error) {
        console.error('Manual API test error:', error);
    }
};

testApiCall();
        </div>
    </div>

    <div class='test info'>
        <h2>5. Common Issues & Solutions</h2>

        <h3>Issue 1: No notes displayed</h3>
        <p><strong>Possible causes:</strong></p>
        <ul>
            <li>User not authenticated (missing X-User-ID header)</li>
            <li>No notes exist for the current user</li>
            <li>API endpoint not responding</li>
            <li>JavaScript errors preventing rendering</li>
        </ul>

        <h3>Issue 2: Loading forever</h3>
        <p><strong>Solutions:</strong></p>
        <ul>
            <li>Check browser network tab for failed requests</li>
            <li>Verify API endpoint is accessible</li>
            <li>Check for CORS issues</li>
            <li>Look for JavaScript errors in console</li>
        </ul>

        <h3>Issue 3: Authentication errors</h3>
        <p><strong>Solutions:</strong></p>
        <ul>
            <li>Ensure user is logged in</li>
            <li>Check localStorage has user data</li>
            <li>Verify X-User-ID header is sent</li>
            <li>Test API with manual curl request</li>
        </ul>
    </div>

    <div class='test info'>
        <h2>6. Manual API Test</h2>
        <p>Test the API endpoint manually:</p>
        <div class='code'>
curl -X GET 'http://localhost/SmartScribe-main/public/index.php?resource=notes' \\
  -H 'Content-Type: application/json' \\
  -H 'X-User-ID: 23'
        </div>
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
                resultDiv.innerHTML = '<div class=\"success\">✅ API working! Found ' + (data.data ? data.data.length : 0) + ' notes</div>';
                if (data.debug) {
                    resultDiv.innerHTML += '<div class=\"code\">Debug info: ' + JSON.stringify(data.debug, null, 2) + '</div>';
                }
            } else {
                resultDiv.innerHTML = '<div class=\"error\">❌ API Error: ' + (data.error || 'Unknown error') + '</div>';
            }
        })
        .catch(error => {
            document.getElementById('api-test').innerHTML = '<div class=\"error\">❌ Network Error: ' + error.message + '</div>';
        });
    </script>
</body>
</html>";
?>