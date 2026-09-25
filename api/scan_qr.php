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
$data = json_decode(file_get_contents('php://input'), true);

if (empty($data['qr_code_text'])) {
    http_response_code(400);
    echo json_encode(['error' => 'QR Code text is missing']);
    exit;
}

$qr_text = $data['qr_code_text'];

mysqli_begin_transaction($conn);
try {
    $stmt = mysqli_prepare($conn, "SELECT id, status, order_id FROM shipments WHERE qr_code_text = ? FOR UPDATE");
    mysqli_stmt_bind_param($stmt, "s", $qr_text);
    mysqli_stmt_execute($stmt);
    $res = mysqli_stmt_get_result($stmt);
    
    if (!($shipment = mysqli_fetch_assoc($res))) {
        throw new Exception("Unrecognized QR Code. Not found in system.");
    }
    
    $current_status = $shipment['status'];
    $new_status = $current_status;
    
    if ($current_status === 'PENDING') {
        $new_status = 'COLLECTION_POINT';
    } else if ($current_status === 'COLLECTION_POINT') {
        $new_status = 'IN_TRANSIT';
    } else if ($current_status === 'IN_TRANSIT') {
        $new_status = 'DELIVERED';
    } else if ($current_status === 'DELIVERED') {
        throw new Exception("Package is already delivered.");
    }
    
    $shipment_id = $shipment['id'];
    
    // Update shipment
    $upd_s = mysqli_prepare($conn, "UPDATE shipments SET status = ?, transporter_id = ? WHERE id = ?");
    mysqli_stmt_bind_param($upd_s, "sii", $new_status, $transporter_id, $shipment_id);
    mysqli_stmt_execute($upd_s);
    
    // Insert into logs
    $ins_log = mysqli_prepare($conn, "INSERT INTO shipment_logs (shipment_id, status, updated_by) VALUES (?, ?, ?)");
    mysqli_stmt_bind_param($ins_log, "isi", $shipment_id, $new_status, $transporter_id);
    mysqli_stmt_execute($ins_log);
    
    // If delivered, update orders table
    if ($new_status === 'DELIVERED') {
        $upd_o = mysqli_prepare($conn, "UPDATE orders SET status = 'delivered' WHERE id = ?");
        mysqli_stmt_bind_param($upd_o, "i", $shipment['order_id']);
        mysqli_stmt_execute($upd_o);
    }
    
    mysqli_commit($conn);
    echo json_encode(['success' => true, 'new_status' => $new_status]);
} catch (Exception $e) {
    mysqli_rollback($conn);
    http_response_code(400);
    echo json_encode(['error' => $e->getMessage()]);
}
