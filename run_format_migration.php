<?php
// Simple script to run the format migration
require_once 'api/config/database.php';

try {
    // Get database connection
    $db = getDbConnection();

    echo "=== Running Format Migration ===\n";

    // Read and execute the migration file
    $migrationSQL = file_get_contents('api/migrations/add_format_to_summaries.sql');

    if (!$migrationSQL) {
        throw new Exception("Could not read migration file");
    }

    // Split SQL commands and execute them
    $commands = array_filter(array_map('trim', explode(';', $migrationSQL)));

    foreach ($commands as $command) {
        if (!empty($command)) {
            echo "Executing: " . substr($command, 0, 50) . "...\n";
            $db->exec($command);
        }
    }

    echo "✅ Format migration completed successfully!\n";
    echo "📊 Added 'format' column to summaries table\n";

} catch (Exception $e) {
    echo "❌ Migration failed: " . $e->getMessage() . "\n";
    exit(1);
}
?>