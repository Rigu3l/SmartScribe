<?php
// public/index.php - Frontend entry point for production
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Handle preflight OPTIONS requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Serve Vue.js app
$dist_path = __DIR__ . '/../dist';
$index_file = $dist_path . '/index.html';

if (file_exists($index_file)) {
    // Production build exists - serve the Vue.js app
    $content = file_get_contents($index_file);

    // Replace API base URL for production (remove localhost references)
    $content = str_replace(
        'http://localhost/api',
        '/api',
        $content
    );

    // Set proper content type
    header('Content-Type: text/html');
    echo $content;
} else {
    // Development fallback - show build instructions
    http_response_code(200);
    header('Content-Type: text/html');
    echo '
    <!DOCTYPE html>
    <html>
    <head>
        <title>SmartScribe - Deployment</title>
        <style>
            body { font-family: Arial, sans-serif; margin: 40px; }
            .container { max-width: 600px; margin: 0 auto; }
            .warning { background: #fff3cd; border: 1px solid #ffeaa7; padding: 15px; border-radius: 5px; }
            .success { background: #d4edda; border: 1px solid #c3e6cb; padding: 15px; border-radius: 5px; }
        </style>
    </head>
    <body>
        <div class="container">
            <h1>🚀 SmartScribe</h1>
            <div class="warning">
                <h3>Frontend Build Required</h3>
                <p>Please build the frontend first:</p>
                <code>npm run build</code>
                <p>This will create the <code>dist/</code> folder with production files.</p>
            </div>
            <div class="success">
                <h3>✅ Deployment Files Created</h3>
                <p>Your deployment configuration is ready:</p>
                <ul>
                    <li><code>railway.toml</code> - Railway configuration</li>
                    <li><code>.htaccess</code> - Production routing</li>
                    <li><code>public/index.php</code> - Frontend entry point</li>
                </ul>
            </div>
        </div>
    </body>
    </html>';
}
?>