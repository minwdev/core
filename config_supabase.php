
<?php
// Gibbon: the flexible, open school platform
// Configuration file for local development with SQLite

// System Configuration
$guid = 'gibbon-' . uniqid();

// Database connection using SQLite (no server required)
$databaseServer = '';
$databaseName = './gibbon_dev.db';
$databaseUsername = '';
$databasePassword = '';
$databasePort = '';

// Get the current URL dynamically
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$host = $_SERVER['HTTP_HOST'] ?? 'localhost:5000';
$absoluteURL = $protocol . '://' . $host;
$absolutePath = __DIR__;

// System settings
$systemName = 'Gibbon';
$organisationName = 'Your School Name';
$organisationNameShort = 'Your School';
$timezone = 'UTC';

// Installation check - set to false to trigger installer
$installed = false;
$installing = true;

// Version
$version = 'v26.0.00';

// Database settings for SQLite
$databaseEngine = 'sqlite';
?>
