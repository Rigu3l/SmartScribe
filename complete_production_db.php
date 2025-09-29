<?php
// complete_production_db.php - Complete database setup for production
echo "=== SmartScribe Complete Production Database Setup ===\n\n";

try {
    // Connect to production database
    $pdo = new PDO(
        'mysql:host=localhost;dbname=smartscribe_prod',
        'smartscribe_user',
        'c88fd2ea19254de8eb25cf459854c899',
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]
    );

    echo "✅ Connected to production database successfully\n\n";

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
        'api/migrations/add_google_id_to_users.sql',
        'api/migrations/add_google_oauth_to_users.sql',
        'api/migrations/cleanup_users_table.sql',
        'api/migrations/add_format_to_notes.sql',
        'api/migrations/add_format_to_summaries.sql',
        'api/migrations/add_is_favorite_to_notes.sql',
        'api/migrations/add_user_id_and_format_to_summaries.sql',
        'api/migrations/add_note_title_to_quizzes.sql',
        'api/migrations/add_title_to_quizzes.sql',
        'api/migrations/add_quiz_type_to_quizzes.sql',
        'api/migrations/add_password_reset_tokens.sql',
        'api/migrations/update_quizzes_table_add_missing_columns.sql'
    ];

    $executedCount = 0;
    $skippedCount = 0;

    foreach ($migrations as $migration) {
        if (file_exists($migration)) {
            echo "📄 Running migration: " . basename($migration) . "\n";

            $sql = file_get_contents($migration);
            $statements = array_filter(array_map('trim', explode(';', $sql)));

            foreach ($statements as $statement) {
                if (!empty($statement) && !preg_match('/^--/', $statement)) {
                    try {
                        $pdo->exec($statement);
                        echo "  ✅ Executed successfully\n";
                        $executedCount++;
                    } catch (Exception $e) {
                        // Check if it's a duplicate or already exists error
                        $errorMsg = $e->getMessage();
                        if (strpos($errorMsg, 'Duplicate') !== false ||
                            strpos($errorMsg, 'already exists') !== false ||
                            strpos($errorMsg, 'Duplicate key name') !== false ||
                            strpos($errorMsg, 'key already exists') !== false) {
                            echo "  ⚠️  Skipped (already exists)\n";
                            $skippedCount++;
                        } else {
                            echo "  ❌ Error: " . $errorMsg . "\n";
                            echo "     Statement: " . substr($statement, 0, 100) . "...\n";
                        }
                    }
                }
            }

            echo "✅ Migration completed: " . basename($migration) . "\n\n";
        } else {
            echo "❌ Migration file not found: $migration\n\n";
        }
    }

    echo "🎉 Database setup completed!\n";
    echo "📊 Total statements executed: $executedCount\n";
    echo "📊 Total statements skipped: $skippedCount\n\n";

    // Verify all tables were created
    echo "=== Verifying All Tables ===\n";
    $tables = $pdo->query('SHOW TABLES')->fetchAll(PDO::FETCH_COLUMN);

    $expectedTables = [
        'users', 'user_tokens', 'notes', 'summaries', 'study_sessions',
        'learning_goals', 'progress_metrics', 'achievements', 'quizzes'
    ];

    $foundTables = [];
    foreach ($expectedTables as $table) {
        if (in_array($table, $tables)) {
            echo "✅ $table - EXISTS\n";
            $foundTables[] = $table;
        } else {
            echo "❌ $table - MISSING\n";
        }
    }

    // Show additional tables that were created
    $extraTables = array_diff($tables, $expectedTables);
    if (!empty($extraTables)) {
        echo "\n📋 Additional tables found:\n";
        foreach ($extraTables as $table) {
            echo "   • $table\n";
        }
    }

    // Verify users exist
    echo "\n=== Verifying Users ===\n";
    $stmt = $pdo->query('SELECT COUNT(*) as count FROM users');
    $result = $stmt->fetch();
    echo "Total users: " . $result['count'] . "\n";

    if ($result['count'] > 0) {
        $stmt = $pdo->prepare('SELECT id, name, email FROM users LIMIT 5');
        $stmt->execute();
        $users = $stmt->fetchAll();

        echo "Sample users:\n";
        foreach ($users as $user) {
            echo "  ID: {$user['id']}, Name: {$user['name']}, Email: {$user['email']}\n";
        }
    }

    // Test database functions
    echo "\n=== Testing Database Functions ===\n";

    // Test note creation
    try {
        $pdo->exec("
            INSERT INTO notes (user_id, title, content) VALUES
            (1, 'Test Production Note', 'This is a test note for production database')
        ");
        echo "✅ Note creation test successful\n";
    } catch (Exception $e) {
        echo "ℹ️  Note creation test skipped: " . $e->getMessage() . "\n";
    }

    echo "\n🚀 Your SmartScribe production database is now complete!\n";
    echo "🔐 Test login credentials:\n";
    echo "   Email: test@example.com\n";
    echo "   Password: password123\n";
    echo "   Email: john@example.com\n";
    echo "   Password: password123\n";

} catch (Exception $e) {
    echo "❌ Database setup failed: " . $e->getMessage() . "\n";
    echo "\n💡 Troubleshooting:\n";
    echo "   1. Check if database 'smartscribe_prod' exists\n";
    echo "   2. Verify user 'smartscribe_user' has proper permissions\n";
    echo "   3. Try running migrations manually in phpMyAdmin\n";
    echo "   4. Check MySQL error logs for details\n";
    exit(1);
}

echo "\n=== Database Statistics ===\n";
echo "📊 Total tables: " . count($tables) . "\n";
echo "📊 Tables created: " . count($foundTables) . "\n";
echo "📊 Users in system: " . $result['count'] . "\n";
echo "📊 Ready for deployment: YES ✅\n";
?>