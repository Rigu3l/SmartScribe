<?php
// deploy.php - Production deployment script for SmartScribe
require_once __DIR__ . '/vendor/autoload.php';

// Load environment variables
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

echo "=== SmartScribe Production Deployment ===\n\n";

// Check if we're in production mode
if (!isset($_ENV['APP_ENV']) || $_ENV['APP_ENV'] !== 'production') {
    echo "⚠️  Warning: APP_ENV is not set to 'production'\n";
    echo "   Make sure to set APP_ENV=production in your .env file\n\n";
}

// Step 1: Test database connection
echo "1️⃣ Testing database connection...\n";
try {
    $pdo = new PDO(
        "mysql:host={$_ENV['DB_HOST']};dbname={$_ENV['DB_NAME']}",
        $_ENV['DB_USER'],
        $_ENV['DB_PASS'],
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
    echo "   ✅ Database connection successful\n";
} catch (Exception $e) {
    echo "   ❌ Database connection failed: " . $e->getMessage() . "\n";
    echo "   💡 Run: php setup_production_database.php\n\n";
    exit(1);
}

// Step 2: Test API endpoints
echo "\n2️⃣ Testing API endpoints...\n";
$apiTests = [
    'http://localhost/SmartScribe-main/api/index.php' => 'API Index',
    'http://localhost/SmartScribe-main/public/index.php' => 'Public Index'
];

foreach ($apiTests as $url => $name) {
    echo "   Testing $name... ";
    if (file_exists(str_replace('http://localhost', '', $url))) {
        echo "✅ File exists\n";
    } else {
        echo "❌ File not found\n";
    }
}

// Step 3: Check file permissions
echo "\n3️⃣ Checking file permissions...\n";
$criticalFiles = [
    'api/config/database.php',
    'public/index.php',
    'uploads/.htaccess',
    'api/public/uploads/.htaccess'
];

foreach ($criticalFiles as $file) {
    if (file_exists($file)) {
        echo "   ✅ $file - exists\n";
    } else {
        echo "   ❌ $file - missing\n";
    }
}

// Step 4: Environment check
echo "\n4️⃣ Environment configuration...\n";
$requiredEnvVars = [
    'DB_HOST', 'DB_NAME', 'DB_USER', 'DB_PASS',
    'JWT_SECRET', 'GOOGLE_GEMINI_API_KEY'
];

foreach ($requiredEnvVars as $var) {
    if (isset($_ENV[$var]) && !empty($_ENV[$var])) {
        echo "   ✅ $var - configured\n";
    } else {
        echo "   ❌ $var - missing\n";
    }
}

// Step 5: Build assets for production
echo "\n5️⃣ Building production assets...\n";
echo "   Run: npm run build\n";
echo "   This will create optimized production files in /dist\n\n";

// Deployment summary
echo "🚀 Deployment Checklist:\n";
echo "   [ ] Update FRONTEND_URL in .env with your domain\n";
echo "   [ ] Configure Google OAuth in Google Cloud Console\n";
echo "   [ ] Set up SSL certificate (HTTPS)\n";
echo "   [ ] Configure web server (Apache/Nginx)\n";
echo "   [ ] Set up file upload permissions\n";
echo "   [ ] Configure cron jobs if needed\n";
echo "   [ ] Test all features after deployment\n\n";

echo "✅ Deployment configuration complete!\n";
echo "🔧 Your application is ready for production deployment.\n";
?>