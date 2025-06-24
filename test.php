
<?php
echo "<h1>PHP Test Page</h1>";
echo "<p>PHP Version: " . phpversion() . "</p>";
echo "<p>SQLite Support: " . (extension_loaded('sqlite3') ? 'Yes' : 'No') . "</p>";
echo "<p>PDO SQLite Support: " . (extension_loaded('pdo_sqlite') ? 'Yes' : 'No') . "</p>";
echo "<p>Current Directory: " . __DIR__ . "</p>";
echo "<p>Database file exists: " . (file_exists('./gibbon_dev.db') ? 'Yes' : 'No') . "</p>";

if (file_exists('./gibbon_dev.db')) {
    echo "<p>Database file size: " . filesize('./gibbon_dev.db') . " bytes</p>";
}

echo "<hr>";
echo "<p><a href='/'>Back to Gibbon</a></p>";
echo "<p><a href='/installer/install.php'>Go to Installer</a></p>";
?>
