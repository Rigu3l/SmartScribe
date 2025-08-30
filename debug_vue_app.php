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

    <div class='debug-section' style='background: #e8f5e8; border: 1px solid #4caf50;'>
        <h3 style='color: #2e7d32; margin-top: 0;'>✅ Recent Fixes Applied</h3>
        <ul style='color: #2e7d32; margin-bottom: 0;'>
            <li><strong>Theme System:</strong> Fixed undefined themeClasses error with reactive fallbacks</li>
            <li><strong>API Endpoints:</strong> Updated all URLs to match project structure</li>
            <li><strong>User Profile:</strong> Enhanced useUserProfile composable with better error handling</li>
            <li><strong>Image Loading:</strong> Fixed profile picture URL construction and caching</li>
            <li><strong>Dashboard:</strong> Resolved runtime errors and improved theme integration</li>
        </ul>
    </div>

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
fetch('http://localhost/SmartScribe-main/public/index.php?resource=auth&action=profile', {
    method: 'GET',
    headers: {
        'Content-Type': 'application/json',
        'X-User-ID': '22'  // Test with user ID 22 (Maki Palomares)
    }
})
.then(response => response.json())
.then(data => console.log('Profile API Response:', data))
.catch(error => console.error('Profile API Error:', error));
        </div>

        <h3>Test Notes API:</h3>
        <div class='code'>
// Test notes API call
fetch('http://localhost/SmartScribe-main/public/index.php?resource=notes', {
    method: 'GET',
    headers: {
        'Content-Type': 'application/json',
        'X-User-ID': '22'  // Test with user ID 22
    }
})
.then(response => response.json())
.then(data => console.log('Notes API Response:', data))
.catch(error => console.error('Notes API Error:', error));
        </div>

        <h3>Test Dashboard Stats API:</h3>
        <div class='code'>
// Test dashboard stats API call
fetch('http://localhost/SmartScribe-main/public/index.php?resource=dashboard&action=stats', {
    method: 'GET',
    headers: {
        'Content-Type': 'application/json',
        'X-User-ID': '22'  // Test with user ID 22
    }
})
.then(response => response.json())
.then(data => console.log('Dashboard Stats API Response:', data))
.catch(error => console.error('Dashboard Stats API Error:', error));
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
        // Get user ID from localStorage
        const userData = localStorage.getItem('user');
        let userId = '22'; // Default to user 22

        if (userData) {
            const user = JSON.parse(userData);
            userId = user.id || '22';
        }

        const response = await fetch('http://localhost/SmartScribe-main/public/index.php?resource=auth&action=profile', {
            headers: {
                'Content-Type': 'application/json',
                'X-User-ID': userId
            }
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
    // Test with actual profile picture from database
    const testImages = [
        '/uploads/profile_pictures/profile_22_1756393724.jpg',  // Maki Palomares
        '/uploads/profile_pictures/profile_23_1756393498.jpg',  // Test User
        '/uploads/profile_pictures/profile_17_1756308864.jpg'   // Other user
    ];

    testImages.forEach((imagePath, index) => {
        const img = document.createElement('img');
        img.src = imagePath + '?t=' + Date.now();
        img.style.width = '100px';
        img.style.margin = '5px';

        img.onload = () => {
            console.log(`✅ Image ${index + 1} loaded successfully:`, imagePath);
            img.style.border = '2px solid green';
        };

        img.onerror = () => {
            console.log(`❌ Image ${index + 1} failed to load:`, imagePath);
            img.style.border = '2px solid red';
        };

        document.body.appendChild(img);
    });
};

testImage();
        </div>
    </div>

    <div class='debug-section'>
        <h2>6. Most Common Issues & Solutions</h2>

        <h3>Issue 1: User data not loaded</h3>
        <p><strong>Solution:</strong> Check that the component is calling the API to fetch user data on mount.</p>

        <h3>Issue 2: Wrong API base URL</h3>
        <p><strong>Solution:</strong> Verify that api.js uses 'http://localhost/SmartScribe-main/public' not 'http://localhost/api' or 'http://localhost:3000/api'.</p>

        <h3>Issue 3: Missing authentication headers</h3>
        <p><strong>Solution:</strong> Ensure X-User-ID header is sent with API requests. Check that user ID is correctly retrieved from localStorage.</p>

        <h3>Issue 4: Vue.js reactivity issues</h3>
        <p><strong>Solution:</strong> Make sure user data is properly reactive and updates trigger re-renders. Check that computed properties are correctly defined.</p>

        <h3>Issue 5: Browser caching</h3>
        <p><strong>Solution:</strong> Hard refresh (Ctrl+F5) or clear browser cache. For images, check that cache-busting timestamps are working.</p>

        <h3>Issue 6: Profile picture path issues</h3>
        <p><strong>Solution:</strong> Ensure profile picture paths are relative URLs starting with '/'. Check that the uploads directory is accessible.</p>

        <h3>Issue 7: Theme system not working</h3>
        <p><strong>Solution:</strong> Verify that the Vuex store is properly initialized and theme getters are working. Check for undefined themeClasses.</p>
    </div>

    <div class='debug-section'>
        <h2>7. Step-by-Step Debugging</h2>
        <ol>
            <li>Open your Vue.js app in browser (http://localhost/SmartScribe-main/public)</li>
            <li>Open browser console (F12 → Console)</li>
            <li>Run the localStorage check code above</li>
            <li>Check if user data exists and has profilePicture field</li>
            <li>Test the API endpoints manually using the corrected URLs</li>
            <li>Check Network tab for failed requests (look for 404s or CORS errors)</li>
            <li>Verify X-User-ID headers are being sent in API requests</li>
            <li>Try the manual image test to check if profile pictures load</li>
            <li>Check Vue component console logs for themeClasses errors</li>
            <li>Verify that the Vuex store is properly initialized</li>
            <li>Check for any JavaScript errors in the console</li>
            <li>Test theme switching in Settings to ensure it works</li>
        </ol>
    </div>

    <div class='debug-section'>
        <h2>8. Quick Diagnostic Commands</h2>
        <p>Run these in browser console for quick diagnosis:</p>
        <div class='code'>
// Check if Vue app is loaded
console.log('Vue app loaded:', !!window.Vue);

// Check if store is available
console.log('Vuex store:', !!window.store);

// Check theme system
if (window.store) {
    console.log('Current theme:', window.store.getters['app/getCurrentTheme']);
    console.log('Theme classes:', window.store.getters['app/getThemeClasses']);
}

// Check API connectivity
fetch('http://localhost/SmartScribe-main/public/index.php?resource=auth&action=profile', {
    headers: { 'X-User-ID': '22' }
})
.then(r => r.json())
.then(d => console.log('API test result:', d))
.catch(e => console.error('API test error:', e));

// Check image loading
const img = new Image();
img.onload = () => console.log('✅ Test image loaded');
img.onerror = () => console.log('❌ Test image failed');
img.src = '/uploads/profile_pictures/profile_22_1756393724.jpg?t=' + Date.now();
        </div>
    </div>
</body>
</html>";
?>