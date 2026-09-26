<?php  

// Enable strict error reporting for mysqli to throw exceptions
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

// Load .env if it exists
if (file_exists(dirname(__FILE__) . '/.env')) {
    $env = parse_ini_file(dirname(__FILE__) . '/.env');
} else {
    $env = [];
}

// Load db_config.php if it exists to safely inject secrets and database configurations (fallback)
if (file_exists(dirname(__FILE__) . '/db_config.php')) {
    require_once dirname(__FILE__) . '/db_config.php';
}

// Default Database configuration (can be overridden by .env or db_config.php)
$sname = isset($env['DB_HOST']) ? $env['DB_HOST'] : (isset($db_sname) ? $db_sname : "localhost");
$uname = isset($env['DB_USER']) ? $env['DB_USER'] : (isset($db_uname) ? $db_uname : "root");
$password = isset($env['DB_PASS']) ? $env['DB_PASS'] : (isset($db_password) ? $db_password : "");
$db_name = isset($env['DB_NAME']) ? $env['DB_NAME'] : (isset($db_name_config) ? $db_name_config : "my_db");

try {
    $conn = mysqli_connect($sname, $uname, $password, $db_name);
} catch (Exception $e) {
    // Log the error securely and show a generic message to the user
    error_log($e->getMessage());
    die("Database connection failed. Please try again later.");
}

// ==========================================
// GOOGLE OAUTH 2.0 CONFIGURATION
// ==========================================
if (!defined('GOOGLE_CLIENT_ID')) {
    define('GOOGLE_CLIENT_ID', isset($env['GOOGLE_CLIENT_ID']) ? $env['GOOGLE_CLIENT_ID'] : '');
}
if (!defined('GOOGLE_CLIENT_SECRET')) {
    define('GOOGLE_CLIENT_SECRET', isset($env['GOOGLE_CLIENT_SECRET']) ? $env['GOOGLE_CLIENT_SECRET'] : '');
}

// Auto-detect the base URL for the redirect URI
if (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https') {
    $protocol = "https://";
} else {
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443)) ? "https://" : "http://";
}
$domainName = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : 'localhost';

// Since this file can be included from different directories, we construct the URI directly to actions/google.php
// We find the path of db_conn.php relative to the document root
$dbConnPath = str_replace('\\', '/', dirname(__FILE__));
$docRoot = str_replace('\\', '/', isset($_SERVER['DOCUMENT_ROOT']) ? $_SERVER['DOCUMENT_ROOT'] : '');
// Use case-insensitive replace for Windows paths
$projectSubfolder = str_ireplace($docRoot, '', $dbConnPath);
if ($projectSubfolder === '/') $projectSubfolder = '';

define('GOOGLE_REDIRECT_URI', $protocol . $domainName . $projectSubfolder . '/actions/google.php');
