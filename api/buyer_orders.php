<?php
session_start();
require dirname(__DIR__) . '/db_conn.php';
header('Content-Type: application/json');

if (!isset($_SESSION['id']) || $_SESSION['role'] !== 'buyer') {
    http_response_code(403);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

$buyer_id = $_SESSION['id'];
$query = "SELECT o.*, u.name as farmer_name FROM orders o JOIN users u ON o.farmer_id = u.id WHERE o.buyer_id = ? ORDER BY o.created_at DESC";
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, "i", $buyer_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

$orders = [];
while ($row = mysqli_fetch_assoc($result)) {
    // Fetch items
    $item_stmt = mysqli_prepare($conn, "SELECT oi.quantity, oi.price, p.name FROM order_items oi JOIN products p ON oi.product_id = p.id WHERE oi.order_id = ?");
    mysqli_stmt_bind_param($item_stmt, "i", $row['id']);
    mysqli_stmt_execute($item_stmt);
    $items_res = mysqli_stmt_get_result($item_stmt);
    $items = [];
    while ($i_row = mysqli_fetch_assoc($items_res)) {
        $items[] = $i_row;
    }
    $row['items'] = $items;
    $orders[] = $row;
}
echo json_encode($orders);
