<?php
session_start();
require dirname(__DIR__) . '/db_conn.php';

header('Content-Type: application/json');

if (!isset($_SESSION['id']) || $_SESSION['role'] !== 'buyer') {
    http_response_code(403);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

$search = isset($_GET['search']) ? '%' . trim($_GET['search']) . '%' : '%';

// Join products with users table to get farmer details
$query = "
    SELECT 
        p.id as product_id,
        p.name as product_name,
        p.quantity,
        p.price_per_kg,
        p.harvest_date,
        u.id as farmer_id,
        u.name as farmer_name,
        u.mobile as farmer_mobile
    FROM products p
    JOIN users u ON p.farmer_id = u.id
    WHERE (p.name LIKE ? OR u.name LIKE ?) AND p.quantity > 0
    ORDER BY p.created_at DESC
";

$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, "ss", $search, $search);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

$products = [];
while ($row = mysqli_fetch_assoc($result)) {
    $products[] = $row;
}

echo json_encode($products);
