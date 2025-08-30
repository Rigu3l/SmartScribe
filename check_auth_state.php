<?php
// Debug script to check current authentication state
echo "<!DOCTYPE html>
<html>
<head>
    <meta charset='UTF-8'>
    <title>Auth State Debug</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 20px; }
        .debug { background: #f8f9fa; padding: 15px; border: 1px solid #dee2e6; margin: 10px 0; }
        .code { background: #2d3748; color: #e2e8f0; padding: 10px; border-radius: 4px; font-family: monospace; }
    </style>
</head>
<body>
    <h1>🔍 Authentication State Debug</h1>

    <div class='debug'>
        <h2>Current localStorage State</h2>
        <p>Add this to your browser console to check authentication:</p>
        <div class='code'>
// Check what's in localStorage
console.log('User from localStorage:', localStorage.getItem('user'));
console.log('Token from localStorage:', localStorage.getItem('token'));

// Parse user data
const userData = localStorage.getItem('user');
if (userData) {
    try {
        const user = JSON.parse(userData);
        console.log('Parsed user object:', user);
        console.log('User ID:', user.id);
        console.log('User email:', user.email);
    } catch (e) {
        console.error('Error parsing user data:', e);
    }
} else {
    console.log('No user data found in localStorage');
}
        </div>
    </div>

    <div class='debug'>
        <h2>API Test with Current User</h2>
        <p>Use this JavaScript to test the API with your current user:</p>
        <div class='code'>
// Get current user and test API
const userData = localStorage.getItem('user');
if (userData) {
    const user = JSON.parse(userData);
    console.log('Testing API with user ID:', user.id);

    fetch('http://localhost/SmartScribe-main/public/index.php?resource=notes', {
        method: 'GET',
        headers: {
            'Content-Type': 'application/json',
            'X-User-ID': user.id.toString()
        }
    })
    .then(response => response.json())
    .then(data => {
        console.log('API Response:', data);
        if (data.success) {
            console.log('✅ Success! Found', data.data.length, 'notes');
        } else {
            console.log('❌ API Error:', data.error);
        }
    })
    .catch(error => console.error('Network Error:', error));
} else {
    console.log('❌ No user found - please log in first');
}
        </div>
    </div>
</body>
</html>";
?>