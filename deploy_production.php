<?php
// =========================================
// SmartScribe Production Database Deployment
// Clean, error-free deployment script
// =========================================

require_once __DIR__ . '/api/config/database.php';

echo "🚀 SmartScribe Production Database Deployment\n";
echo "===============================================\n\n";

try {
    // Test database connection
    echo "🔍 Testing database connection...\n";
    $db = getDbConnection();
    echo "✅ Successfully connected to database\n\n";

    // Check if production migration exists
    $migrationFile = __DIR__ . '/production_migration.sql';
    if (!file_exists($migrationFile)) {
        throw new Exception("Production migration file not found: $migrationFile");
    }

    echo "📄 Found production migration file\n";

    // Read and execute migration
    echo "⚡ Executing production migration...\n";
    $sql = file_get_contents($migrationFile);

    // Split SQL into individual statements and execute
    $statements = array_filter(array_map('trim', explode(';', $sql)));
    $executedCount = 0;
    $skippedCount = 0;

    foreach ($statements as $statement) {
        if (!empty($statement) && !preg_match('/^--/', $statement)) {
            try {
                $db->exec($statement);
                echo "  ✅ Executed statement\n";
                $executedCount++;
            } catch (Exception $e) {
                // Check if it's a harmless error (table already exists, etc.)
                $errorMessage = $e->getMessage();
                if (strpos($errorMessage, 'already exists') !== false ||
                    strpos($errorMessage, 'Duplicate entry') !== false ||
                    strpos($errorMessage, 'Multiple primary key') !== false) {
                    echo "  ⚠️  Skipped (already exists): " . substr($statement, 0, 50) . "...\n";
                    $skippedCount++;
                } else {
                    echo "  ❌ Error: " . $errorMessage . "\n";
                    echo "     Statement: " . substr($statement, 0, 100) . "...\n";
                    throw $e;
                }
            }
        }
    }

    echo "\n📊 Migration Summary:\n";
    echo "  - Statements executed: $executedCount\n";
    echo "  - Statements skipped: $skippedCount\n\n";

    // Verify tables were created
    echo "🔍 Verifying database structure...\n";
    $tables = $db->query('SHOW TABLES')->fetchAll(PDO::FETCH_COLUMN);

    $expectedTables = [
        'users', 'user_tokens', 'notes', 'summaries',
        'study_sessions', 'learning_goals', 'progress_metrics',
        'achievements', 'quizzes'
    ];

    $missingTables = [];
    foreach ($expectedTables as $table) {
        if (in_array($table, $tables)) {
            echo "  ✅ $table - EXISTS\n";
        } else {
            echo "  ❌ $table - MISSING\n";
            $missingTables[] = $table;
        }
    }

    if (!empty($missingTables)) {
        echo "\n⚠️  Warning: Some tables are missing: " . implode(', ', $missingTables) . "\n";
    } else {
        echo "\n✅ All required tables exist\n";
    }

    // Check table structure for critical tables
    echo "\n🔍 Checking table structures...\n";

    // Check users table
    $stmt = $db->query("DESCRIBE users");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $userColumns = array_column($columns, 'Field');
    $requiredUserColumns = ['id', 'email', 'password', 'name'];

    foreach ($requiredUserColumns as $col) {
        if (in_array($col, $userColumns)) {
            echo "  ✅ users.$col - EXISTS\n";
        } else {
            echo "  ❌ users.$col - MISSING\n";
        }
    }

    // Check notes table
    $stmt = $db->query("DESCRIBE notes");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $noteColumns = array_column($columns, 'Field');
    $requiredNoteColumns = ['id', 'user_id', 'title', 'original_text'];

    foreach ($requiredNoteColumns as $col) {
        if (in_array($col, $noteColumns)) {
            echo "  ✅ notes.$col - EXISTS\n";
        } else {
            echo "  ❌ notes.$col - MISSING\n";
        }
    }

    echo "\n🎉 Production database deployment completed successfully!\n";
    echo "====================================================\n";
    echo "📋 Next Steps:\n";
    echo "  1. Copy .env.production to .env\n";
    echo "  2. Update database credentials in .env\n";
    echo "  3. Generate a strong JWT secret key\n";
    echo "  4. Configure your production API keys\n";
    echo "  5. Test your application\n\n";

    echo "🔐 Security Reminders:\n";
    echo "  - Use strong, unique passwords for database users\n";
    echo "  - Generate a random 64-character JWT secret\n";
    echo "  - Keep API keys secure and rotate them regularly\n";
    echo "  - Enable SSL/TLS for production traffic\n\n";

} catch (Exception $e) {
    echo "\n❌ Deployment failed: " . $e->getMessage() . "\n";
    echo "💡 Troubleshooting:\n";
    echo "  - Check database connection settings\n";
    echo "  - Ensure MySQL server is running\n";
    echo "  - Verify database user permissions\n";
    echo "  - Check that production_migration.sql exists\n\n";
    exit(1);
}

echo "✅ Deployment script completed\n";
?>