<?php
// Debug script to check Vue.js application state
echo "<!DOCTYPE html>
<html lang='en'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>Vue.js App Debug</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .debug-section { background: #f0f0f0; padding: 15px; margin: 10px 0; border-radius: 5px; }
        .code { background: #2d3748; color: #e2e8f0; padding: 10px; border-radius: 4px; font-family: monospace; overflow-x: auto; }
        pre { margin: 10px 0; }
    </style>
</head>
<body>
    <h1>🔧 Vue.js Application Debug</h1>

    <div class='debug-section'>
        <h2>1. Check localStorage User Data</h2>
        <p>This will show what's stored in the browser's localStorage for the user.</p>
        <div class='code'>
// Run this in browser console:
console.log('localStorage user data:', localStorage.getItem('user'));
console.log('localStorage token:', localStorage.getItem('token'));

if (localStorage.getItem('user')) {
    const user = JSON.parse(localStorage.getItem('user'));
    console.log('Parsed user object:', user);
    console.log('User ID:', user.id);
    console.log('User name:', user.name);
    console.log('Profile picture:', user.profilePicture);
}
        </div>
    </div>

    <div class='debug-section'>
        <h2>2. Test API Endpoints</h2>
        <p>Test the API endpoints that the Vue app uses.</p>

        <h3>Test Profile API:</h3>
        <div class='code'>
// Test profile API call
fetch('http://localhost/api/auth/profile', {
    method: 'GET',
    headers: {
        'Content-Type': 'application/json',
        'X-User-ID': '23'  // Test with user ID 23
    }
})
.then(response => response.json())
.then(data => console.log('Profile API Response:', data))
.catch(error => console.error('Profile API Error:', error));
        </div>

        <h3>Test Settings API:</h3>
        <div class='code'>
// Test settings API call
fetch('http://localhost/api/settings', {
    method: 'GET',
    headers: {
        'Content-Type': 'application/json',
        'X-User-ID': '23'
    }
})
.then(response => response.json())
.then(data => console.log('Settings API Response:', data))
.catch(error => console.error('Settings API Error:', error));
        </div>
    </div>

    <div class='debug-section'>
        <h2>3. Vue.js Component Debug</h2>
        <p>Add this to any Vue component to debug the user data loading.</p>
        <div class='code'>
// Add this to the script section of any Vue component
import { onMounted } from 'vue';

onMounted(async () => {
    console.log('Component mounted');

    // Check user data
    const userData = localStorage.getItem('user');
    console.log('localStorage user:', userData);

    if (userData) {
        const user = JSON.parse(userData);
        console.log('Parsed user:', user);
        console.log('Profile picture path:', user.profilePicture);

        // Test image URL construction
        if (user.profilePicture) {
            const imageUrl = `/${user.profilePicture}?t=${Date.now()}`;
            console.log('Constructed image URL:', imageUrl);

            // Test if image loads
            const img = new Image();
            img.onload = () => console.log('✅ Image loaded successfully');
            img.onerror = () => console.log('❌ Image failed to load');
            img.src = imageUrl;
        }
    }

    // Test API call
    try {
        const response = await fetch('http://localhost/api/auth/profile', {
            headers: { 'X-User-ID': user?.id || '23' }
        });
        const data = await response.json();
        console.log('API Response:', data);
    } catch (error) {
        console.error('API Error:', error);
    }
});
        </div>
    </div>

    <div class='debug-section'>
        <h2>4. Browser Network Inspection</h2>
        <p>How to inspect network requests in browser:</p>
        <ol>
            <li>Open browser DevTools (F12)</li>
            <li>Go to Network tab</li>
            <li>Navigate to your Vue.js app</li>
            <li>Look for failed image requests (status codes other than 200)</li>
            <li>Check the request URLs for profile pictures</li>
            <li>Verify X-User-ID headers are being sent</li>
        </ol>
    </div>

    <div class='debug-section'>
        <h2>5. Quick Fix Test</h2>
        <p>If images still don't load, try this manual test:</p>
        <div class='code'>
// Manual test in browser console
const testImage = () => {
    const img = document.createElement('img');
    img.src = '/uploads/profile_pictures/profile_23_1756393498.jpg?t=' + Date.now();
    img.onload = () => console.log('✅ Manual image test successful');
    img.onerror = () => console.log('❌ Manual image test failed');
    document.body.appendChild(img);
};

testImage();
        </div>
    </div>

    <div class='debug-section'>
        <h2>6. Most Common Issues & Solutions</h2>

        <h3>Issue 1: User data not loaded</h3>
        <p><strong>Solution:</strong> Check that the component is calling the API to fetch user data on mount.</p>

        <h3>Issue 2: Wrong API base URL</h3>
        <p><strong>Solution:</strong> Verify that api.js uses 'http://localhost/api' not 'http://localhost:3000/api'.</p>

        <h3>Issue 3: Missing authentication headers</h3>
        <p><strong>Solution:</strong> Ensure X-User-ID header is sent with API requests.</p>

        <h3>Issue 4: Vue.js reactivity issues</h3>
        <p><strong>Solution:</strong> Make sure user data is properly reactive and updates trigger re-renders.</p>

        <h3>Issue 5: Browser caching</h3>
        <p><strong>Solution:</strong> Hard refresh (Ctrl+F5) or clear browser cache.</p>
    </div>

    <div class='debug-section'>
        <h2>7. Step-by-Step Debugging</h2>
        <ol>
            <li>Open your Vue.js app in browser</li>
            <li>Open browser console (F12 → Console)</li>
            <li>Run the localStorage check code above</li>
            <li>Check if user data exists and has profilePicture</li>
            <li>Test the API endpoints manually</li>
            <li>Check Network tab for failed requests</li>
            <li>Try the manual image test</li>
            <li>If still failing, check Vue component console logs</li>
        </ol>
    </div>
</body>
</html>";
?>