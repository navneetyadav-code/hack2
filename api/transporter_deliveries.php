<?php
session_start();
require dirname(__DIR__) . '/db_conn.php';
header('Content-Type: application/json');

if (!isset($_SESSION['id']) || $_SESSION['role'] !== 'transporter') {
    http_response_code(403);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

$transporter_id = $_SESSION['id'];

$query = "
    SELECT s.tracking_id, s.status, MAX(sl.updated_at) as updated_at
    FROM shipments s
    JOIN shipment_logs sl ON s.id = sl.shipment_id
    WHERE sl.updated_by = ?
    GROUP BY s.id
    ORDER BY updated_at DESC
";
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, "i", $transporter_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

$deliveries = [];
while ($row = mysqli_fetch_assoc($result)) {
    $deliveries[] = $row;
}

echo json_encode($deliveries);
