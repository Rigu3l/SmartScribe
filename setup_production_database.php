<?php
// setup_production_database.php - Production database setup for SmartScribe
echo "=== SmartScribe Production Database Setup ===\n\n";

try {
    // Database connection with production credentials
    $pdo = new PDO(
        "mysql:host=localhost",
        "root",  // Using root to create the database and user
        "",
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]
    );

    echo "✅ Connected to MySQL as root\n\n";

    // Create production database
    $pdo->exec("CREATE DATABASE IF NOT EXISTS smartscribe_prod");
    echo "✅ Created database 'smartscribe_prod'\n";

    // Create production user
    $pdo->exec("CREATE USER IF NOT EXISTS 'smartscribe_user'@'localhost' IDENTIFIED BY 'c88fd2ea19254de8eb25cf459854c899'");
    echo "✅ Created user 'smartscribe_user'\n";

    // Grant privileges
    $pdo->exec("GRANT ALL PRIVILEGES ON smartscribe_prod.* TO 'smartscribe_user'@'localhost'");
    $pdo->exec("FLUSH PRIVILEGES");
    echo "✅ Granted privileges to production user\n\n";

    // Connect with production credentials to create tables
    $prod_pdo = new PDO(
        "mysql:host=localhost;dbname=smartscribe_prod",
        "smartscribe_user",
        "c88fd2ea19254de8eb25cf459854c899",
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]
    );

    echo "✅ Connected to production database with production credentials\n\n";

    // List of all migration files to run in order
    $migrations = [
        'api/migrations/create_users_table.sql',
        'api/migrations/create_user_tokens_table.sql',
        'api/migrations/create_notes_table.sql',
        'api/migrations/add_keywords_to_notes.sql',
        'api/migrations/create_summaries_table.sql',
        'api/migrations/create_study_sessions_table.sql',
        'api/migrations/create_learning_goals_table.sql',
        'api/migrations/create_progress_metrics_table.sql',
        'api/migrations/create_achievements_table.sql',
        'api/migrations/create_quizzes_table.sql',
        'api/migrations/add_name_fields_to_users.sql',
        'api/migrations/add_profile_picture_to_users.sql',
        'api/migrations/cleanup_users_table.sql'
    ];

    $executedCount = 0;

    foreach ($migrations as $migration) {
        if (file_exists($migration)) {
            echo "📄 Running migration: $migration\n";

            $sql = file_get_contents($migration);
            $statements = array_filter(array_map('trim', explode(';', $sql)));

            foreach ($statements as $statement) {
                if (!empty($statement) && !preg_match('/^--/', $statement)) {
                    try {
                        $prod_pdo->exec($statement);
                        echo "  ✅ Executed: " . substr($statement, 0, 60) . "...\n";
                        $executedCount++;
                    } catch (Exception $e) {
                        if (strpos($e->getMessage(), 'Duplicate entry') !== false ||
                            strpos($e->getMessage(), 'Duplicate key name') !== false) {
                            echo "  ⚠️  Skipped (already exists): " . substr($statement, 0, 60) . "...\n";
                        } else {
                            echo "  ❌ Error: " . $e->getMessage() . "\n";
                        }
                    }
                }
            }

            echo "✅ Migration completed: $migration\n\n";
        } else {
            echo "❌ Migration file not found: $migration\n\n";
        }
    }

    echo "🎉 Production database setup completed!\n";
    echo "📊 Total statements executed: $executedCount\n\n";

    // Verify tables
    echo "=== Verifying Tables ===\n";
    $tables = $prod_pdo->query('SHOW TABLES')->fetchAll(PDO::FETCH_COLUMN);

    $expectedTables = [
        'users', 'user_tokens', 'notes', 'summaries', 'study_sessions',
        'learning_goals', 'progress_metrics', 'achievements', 'quizzes'
    ];

    foreach ($expectedTables as $table) {
        if (in_array($table, $tables)) {
            echo "✅ $table - EXISTS\n";
        } else {
            echo "❌ $table - MISSING\n";
        }
    }

    echo "\n🚀 Production database is ready!\n";
    echo "📝 Database Configuration:\n";
    echo "   Host: localhost\n";
    echo "   Database: smartscribe_prod\n";
    echo "   User: smartscribe_user\n";
    echo "   Password: c88fd2ea19254de8eb25cf459854c899\n";

} catch (Exception $e) {
    echo "❌ Setup failed: " . $e->getMessage() . "\n";
    echo "\n💡 Make sure:\n";
    echo "   1. MySQL server is running\n";
    echo "   2. Root user has no password set\n";
    echo "   3. You have sufficient privileges\n";
    exit(1);
}

echo "\n=== Next Steps ===\n";
echo "1. Update your .env file with production settings\n";
echo "2. Test the production configuration\n";
echo "3. Deploy your application\n";
?>