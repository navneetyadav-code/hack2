<?php  

// Enable strict error reporting for mysqli to throw exceptions
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

$sname = "localhost";
$uname = "root";
$password = "";

$db_name = "my_db";

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
// PASTE YOUR GOOGLE CLIENT ID AND SECRET HERE (OR USE db_config.php)
if (file_exists(dirname(__FILE__) . '/db_config.php')) {
    require_once dirname(__FILE__) . '/db_config.php';
}
if (!defined('GOOGLE_CLIENT_ID')) {
    define('GOOGLE_CLIENT_ID', 'YOUR_GOOGLE_CLIENT_ID_HERE');
}
if (!defined('GOOGLE_CLIENT_SECRET')) {
    define('GOOGLE_CLIENT_SECRET', 'YOUR_GOOGLE_CLIENT_SECRET_HERE');
}

// Auto-detect the base URL for the redirect URI
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
$domainName = $_SERVER['HTTP_HOST'];

// Since this file can be included from different directories, we construct the URI directly to actions/google.php
// We find the path of db_conn.php relative to the document root
$dbConnPath = str_replace('\\', '/', dirname(__FILE__));
$docRoot = str_replace('\\', '/', $_SERVER['DOCUMENT_ROOT']);
$projectSubfolder = str_replace($docRoot, '', $dbConnPath);

define('GOOGLE_REDIRECT_URI', $protocol . $domainName . $projectSubfolder . '/actions/google.php');
