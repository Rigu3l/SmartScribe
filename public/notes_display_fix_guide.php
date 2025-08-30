<?php
// Notes Display Fix Guide
echo "<!DOCTYPE html>
<html>
<head>
    <meta charset='UTF-8'>
    <title>Notes Display Fix Guide</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 20px; max-width: 1200px; margin: 0 auto; }
        .section { margin: 30px 0; padding: 20px; border: 1px solid #ddd; border-radius: 8px; }
        .success { background: #d4edda; border-color: #c3e6cb; }
        .error { background: #f8d7da; border-color: #f5c6cb; }
        .info { background: #d1ecf1; border-color: #bee5eb; }
        .warning { background: #fff3cd; border-color: #ffeaa7; }
        .code { background: #2d3748; color: #e2e8f0; padding: 15px; border-radius: 4px; font-family: monospace; overflow-x: auto; margin: 10px 0; }
        .button { background: #007bff; color: white; padding: 10px 20px; border: none; border-radius: 4px; cursor: pointer; margin: 5px; }
        .button:hover { background: #0056b3; }
        .step { margin: 15px 0; }
        .step-number { display: inline-block; width: 30px; height: 30px; background: #007bff; color: white; border-radius: 50%; text-align: center; line-height: 30px; margin-right: 10px; }
        h1, h2, h3 { color: #333; }
        ul { padding-left: 20px; }
        li { margin: 5px 0; }
    </style>
</head>
<body>
    <h1>🔧 Notes Display Fix Guide</h1>
    <p>This guide will help you fix the notes display issue in your SmartScribe application.</p>

    <div class='section info'>
        <h2>📋 Problem Summary</h2>
        <p><strong>Issue:</strong> Notes are not displaying in the Notes view, even though the API is working correctly.</p>
        <p><strong>Root Cause:</strong> User authentication issues preventing the frontend from properly fetching and displaying notes.</p>
        <p><strong>Solution:</strong> Apply the authentication and display fixes provided below.</p>
    </div>

    <div class='section success'>
        <h2>✅ Step 1: Verify Database Setup</h2>
        <p>Let's first check if your database has the correct data:</p>";

try {
    require_once 'api/config/database.php';
    $db = getDbConnection();

    // Check notes count
    $stmt = $db->query("SELECT COUNT(*) as total FROM notes");
    $total = $stmt->fetch();
    echo "<p><strong>✅ Total notes in database:</strong> " . $total['total'] . "</p>";

    // Check users
    $stmt = $db->query("SELECT id, name, email FROM users LIMIT 5");
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo "<p><strong>✅ Users in database:</strong></p><ul>";
    foreach ($users as $user) {
        echo "<li>User ID: {$user['id']}, Name: {$user['name']}, Email: {$user['email']}</li>";
    }
    echo "</ul>";

    // Check API endpoint
    echo "<p><strong>✅ API Test:</strong> Testing notes endpoint...</p>";
    $apiTest = shell_exec('curl -s "http://localhost/SmartScribe-main/public/index.php?resource=notes" -H "X-User-ID: 23"');
    $apiData = json_decode($apiTest, true);
    if ($apiData && $apiData['success']) {
        echo "<p><strong>✅ API Working:</strong> Found " . count($apiData['data']) . " notes for user 23</p>";
    } else {
        echo "<p><strong>❌ API Issue:</strong> " . ($apiData['error'] ?? 'Unknown error') . "</p>";
    }

} catch (Exception $e) {
    echo "<p class='error'>❌ Database error: " . $e->getMessage() . "</p>";
}

echo "</div>

    <div class='section warning'>
        <h2>🔧 Step 2: Apply the Authentication Fix</h2>
        <p>The main issue is that the frontend is not properly sending authentication headers. Here's how to fix it:</p>

        <div class='step'>
            <span class='step-number'>1</span>
            <strong>Add the authentication fix script to your HTML:</strong>
            <div class='code'>
<!-- Add this to your public/index.html before the closing </body> tag -->
<script src='/fix_notes_authentication.js'></script>
            </div>
        </div>

        <div class='step'>
            <span class='step-number'>2</span>
            <strong>Or add this JavaScript directly to your browser console:</strong>
            <div class='code'>
// Copy and paste this into your browser console on the notes page
" . str_replace('</script>', '<\\/script>', file_get_contents('fix_notes_authentication.js')) . "
            </div>
        </div>

        <div class='step'>
            <span class='step-number'>3</span>
            <strong>Test the fix:</strong>
            <div class='code'>
// Run these commands in your browser console
checkAuthentication()
testAPIAuthentication(localStorage.getItem('user') ? JSON.parse(localStorage.getItem('user')).id : null)
debugVueComponents()
            </div>
        </div>
    </div>

    <div class='section info'>
        <h2>🔍 Step 3: Debug the Issue</h2>
        <p>Use these debugging tools to identify what's wrong:</p>

        <h3>Browser Console Commands:</h3>
        <div class='code'>
// Check user authentication
console.log('User data:', localStorage.getItem('user'));
console.log('Token:', localStorage.getItem('token'));

// Test API manually
fetch('http://localhost/SmartScribe-main/public/index.php?resource=notes', {
    headers: { 'X-User-ID': '23' }
})
.then(r => r.json())
.then(d => console.log('API Response:', d));

// Check Vue.js components
document.querySelectorAll('[data-v-]').length; // Count Vue components
document.querySelector('.grid.grid-cols-1.md\\\\:grid-cols-2.lg\\\\:grid-cols-3'); // Find notes container
        </div>

        <h3>Common Issues & Solutions:</h3>
        <ul>
            <li><strong>No user data:</strong> Clear localStorage and login again</li>
            <li><strong>Missing X-User-ID:</strong> The authentication fix script will handle this</li>
            <li><strong>Vue.js not loading:</strong> Check browser console for JavaScript errors</li>
            <li><strong>API not responding:</strong> Verify Apache/XAMPP is running</li>
            <li><strong>CORS issues:</strong> The fix script includes CORS handling</li>
        </ul>
    </div>

    <div class='section success'>
        <h2>🚀 Step 4: Quick Fix for Immediate Testing</h2>
        <p>If you want to test the fix immediately, follow these steps:</p>

        <div class='step'>
            <span class='step-number'>1</span>
            <strong>Open your browser and go to the notes page</strong>
        </div>

        <div class='step'>
            <span class='step-number'>2</span>
            <strong>Open Developer Tools (F12) and go to the Console tab</strong>
        </div>

        <div class='step'>
            <span class='step-number'>3</span>
            <strong>Copy and paste this code into the console:</strong>
            <div class='code'>
// Quick authentication test
const userData = localStorage.getItem('user');
if (userData) {
    const user = JSON.parse(userData);
    console.log('User:', user);

    // Test notes API
    fetch('http://localhost/SmartScribe-main/public/index.php?resource=notes', {
        headers: {
            'Content-Type': 'application/json',
            'X-User-ID': user.id
        }
    })
    .then(r => r.json())
    .then(data => {
        console.log('Notes API Response:', data);
        if (data.success) {
            console.log('✅ API working! Found', data.data.length, 'notes');
        } else {
            console.log('❌ API error:', data.error);
        }
    });
} else {
    console.log('❌ No user data found - please login first');
}
            </div>
        </div>

        <div class='step'>
            <span class='step-number'>4</span>
            <strong>Check the console output for results</strong>
        </div>
    </div>

    <div class='section info'>
        <h2>📋 Step 5: Permanent Fix Implementation</h2>
        <p>To make the fix permanent, update your Vue.js application:</p>

        <h3>Update src/services/api.js:</h3>
        <div class='code'>
// Add this to your api.js interceptors
api.interceptors.request.use(config => {
  // ... existing code ...

  // Add user ID from localStorage for authentication
  const userData = localStorage.getItem('user');
  if (userData) {
    try {
      const user = JSON.parse(userData);
      if (user && user.id) {
        config.headers['X-User-ID'] = user.id;
        console.log('API: Added user ID to request:', user.id);
      }
    } catch (error) {
      console.error('Error parsing user data:', error);
    }
  }

  return config;
});
        </div>

        <h3>Update your Vue components:</h3>
        <div class='code'>
// Add this to your NotesView.vue setup() function
const user = computed(() => {
  const userData = localStorage.getItem('user');
  if (userData) {
    try {
      return JSON.parse(userData);
    } catch (error) {
      console.error('Error parsing user data:', error);
    }
  }
  return { name: 'User', email: 'user@example.com' };
});
        </div>
    </div>

    <div class='section success'>
        <h2>🎯 Expected Results</h2>
        <p>After applying the fix, you should see:</p>
        <ul>
            <li>✅ Notes displaying correctly in the grid layout</li>
            <li>✅ User profile pictures in the header</li>
            <li>✅ Proper authentication headers in API requests</li>
            <li>✅ No more 'network error' messages</li>
            <li>✅ Working note creation, editing, and deletion</li>
        </ul>
    </div>

    <div class='section warning'>
        <h2>🔧 Troubleshooting</h2>

        <h3>If notes still don't display:</h3>
        <ul>
            <li>Check browser console for JavaScript errors</li>
            <li>Verify user is logged in with valid data</li>
            <li>Test API endpoint manually with curl</li>
            <li>Check if Vue.js components are mounting properly</li>
            <li>Clear browser cache and localStorage</li>
        </ul>

        <h3>If you see authentication errors:</h3>
        <ul>
            <li>Clear localStorage and login again</li>
            <li>Check if user ID exists in localStorage</li>
            <li>Verify API endpoint accepts X-User-ID header</li>
            <li>Test with different user accounts</li>
        </ul>

        <h3>If Vue.js components aren't working:</h3>
        <ul>
            <li>Check if Vue.js library is loaded</li>
            <li>Verify component templates are correct</li>
            <li>Check for syntax errors in JavaScript</li>
            <li>Test with browser developer tools</li>
        </ul>
    </div>

    <div class='section info'>
        <h2>📞 Support</h2>
        <p>If you're still having issues after following this guide:</p>
        <ul>
            <li>Check the browser console for detailed error messages</li>
            <li>Test the API endpoints manually using the curl commands provided</li>
            <li>Verify your XAMPP/Apache server is running correctly</li>
            <li>Check PHP error logs for server-side issues</li>
            <li>Ensure all database migrations have been applied</li>
        </ul>
    </div>

    <script>
        // Add some interactive debugging
        function runQuickTest() {
            console.log('=== QUICK NOTES TEST ===');

            const userData = localStorage.getItem('user');
            if (!userData) {
                console.error('❌ No user data - please login first');
                return;
            }

            const user = JSON.parse(userData);
            console.log('✅ User found:', user);

            fetch('http://localhost/SmartScribe-main/public/index.php?resource=notes', {
                headers: {
                    'Content-Type': 'application/json',
                    'X-User-ID': user.id
                }
            })
            .then(r => r.json())
            .then(data => {
                console.log('✅ API Response:', data);
                if (data.success) {
                    console.log('🎉 SUCCESS: Notes API working! Found', data.data.length, 'notes');
                } else {
                    console.error('❌ API Error:', data.error);
                }
            })
            .catch(error => {
                console.error('❌ Network Error:', error);
            });
        }

        // Make function available globally
        window.runQuickTest = runQuickTest;
    </script>

    <button class='button' onclick='runQuickTest()'>Run Quick Test</button>
    <p><small>Click the button above to run a quick test of the notes API</small></p>
</body>
</html>";
?>