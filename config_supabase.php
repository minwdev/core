
<?php
// Gibbon: the flexible, open school platform
// Configuration file for Supabase

// Supabase Configuration (placeholder - replace with actual values)
$supabaseUrl = '';
$supabaseKey = '';
$supabaseServiceKey = '';

// System Configuration
$guid = 'gibbon-' . uniqid();

// Database connection for development
$databaseServer = 'localhost';
$databaseName = 'gibbon_dev';
$databaseUsername = 'gibbon';
$databasePassword = 'gibbon123';
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

// Installation check
$installed = true;
$installing = false;

// Version
$version = 'v26.0.00';
?>
