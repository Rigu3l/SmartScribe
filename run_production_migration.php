<?php
// run_production_migration.php - Script to run production database migration

require_once __DIR__ . '/api/config/database.php';

echo "=== SmartScribe Production Database Migration ===\n\n";

try {
    $db = getDbConnection();
    echo "✅ Database connection established\n\n";

    $migrationFile = 'production_migration.sql';

    if (file_exists($migrationFile)) {
        echo "📄 Running production migration: $migrationFile\n";

        $sql = file_get_contents($migrationFile);

        // Split SQL into individual statements
        $statements = array_filter(array_map('trim', explode(';', $sql)));

        foreach ($statements as $statement) {
            if (!empty($statement) && !preg_match('/^--/', $statement)) {
                try {
                    $db->exec($statement);
                    echo "  ✅ Executed: " . substr($statement, 0, 50) . "...\n";
                } catch (Exception $e) {
                    echo "  ❌ Error executing statement: " . $e->getMessage() . "\n";
                    echo "     Statement: " . substr($statement, 0, 100) . "...\n";
                }
            }
        }

        echo "✅ Production migration completed successfully!\n\n";

        // Now run additional migrations if needed
        echo "📄 Running additional migrations...\n";
        $additionalMigrations = [
            'api/migrations/add_name_fields_to_users.sql',
            'api/migrations/add_profile_picture_to_users.sql',
            'api/migrations/add_format_to_notes.sql',
            'api/migrations/add_format_to_summaries.sql',
            'api/migrations/add_keywords_to_notes.sql',
            'api/migrations/add_note_title_to_quizzes.sql',
            'api/migrations/add_title_to_quizzes.sql',
            'api/migrations/cleanup_users_table.sql'
        ];

        foreach ($additionalMigrations as $migration) {
            if (file_exists($migration)) {
                echo "  📄 Running: $migration\n";
                $sql = file_get_contents($migration);
                $statements = array_filter(array_map('trim', explode(';', $sql)));

                foreach ($statements as $statement) {
                    if (!empty($statement) && !preg_match('/^--/', $statement)) {
                        $db->exec($statement);
                    }
                }
                echo "    ✅ Completed\n";
            }
        }

        echo "🎉 All migrations completed successfully!\n";

    } else {
        echo "❌ Migration file not found: $migrationFile\n";
    }

} catch (Exception $e) {
    echo "❌ Migration failed: " . $e->getMessage() . "\n";
    exit(1);
}

echo "\n🚀 Your SmartScribe production database is now ready!\n";
?>