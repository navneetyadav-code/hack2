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
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$limit = 10;
$offset = ($page - 1) * $limit;

// Count total matching items
$count_query = "SELECT COUNT(*) as total FROM products p JOIN users u ON p.farmer_id = u.id WHERE (p.name LIKE ? OR u.name LIKE ?) AND p.quantity > 0";
$c_stmt = mysqli_prepare($conn, $count_query);
mysqli_stmt_bind_param($c_stmt, "ss", $search, $search);
mysqli_stmt_execute($c_stmt);
$c_res = mysqli_stmt_get_result($c_stmt);
$total = mysqli_fetch_assoc($c_res)['total'];
$total_pages = ceil($total / $limit);

// Fetch paginated data
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
    LIMIT ? OFFSET ?
";

$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, "ssii", $search, $search, $limit, $offset);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

$products = [];
while ($row = mysqli_fetch_assoc($result)) {
    $products[] = $row;
}

echo json_encode([
    'data' => $products,
    'total' => $total,
    'page' => $page,
    'total_pages' => $total_pages
]);
