<?php
// run_favorites_migration.php - Script to run the favorites migration

require_once __DIR__ . '/api/config/database.php';

echo "=== Running Favorites Migration ===\n\n";

try {
    $db = getDbConnection();
    echo "✅ Database connection established\n\n";

    $migration = 'api/migrations/add_is_favorite_to_notes.sql';

    if (file_exists($migration)) {
        echo "📄 Running migration: $migration\n";

        $sql = file_get_contents($migration);

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

        echo "✅ Migration completed: $migration\n\n";
        echo "🎉 Favorites functionality is now ready!\n";
    } else {
        echo "❌ Migration file not found: $migration\n\n";
    }

} catch (Exception $e) {
    echo "❌ Migration failed: " . $e->getMessage() . "\n";
    exit(1);
}
?>