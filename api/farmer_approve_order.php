<?php
session_start();
require dirname(__DIR__) . '/db_conn.php';
header('Content-Type: application/json');

if (!isset($_SESSION['id']) || $_SESSION['role'] !== 'farmer') {
    http_response_code(403);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

$farmer_id = $_SESSION['id'];
$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['order_id'], $data['status'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Missing fields']);
    exit;
}

$order_id = intval($data['order_id']);
$new_status = $data['status']; // 'approved' or 'rejected'

mysqli_begin_transaction($conn);
try {
    $stmt = mysqli_prepare($conn, "SELECT status, tracking_number FROM orders WHERE id = ? AND farmer_id = ? FOR UPDATE");
    mysqli_stmt_bind_param($stmt, "ii", $order_id, $farmer_id);
    mysqli_stmt_execute($stmt);
    $res = mysqli_stmt_get_result($stmt);
    if (!($order = mysqli_fetch_assoc($res))) {
        throw new Exception("Order not found or access denied.");
    }

    if ($order['status'] === 'pending' && $new_status === 'approved') {
        // Deduct stock
        $item_stmt = mysqli_prepare($conn, "SELECT product_id, quantity FROM order_items WHERE order_id = ?");
        mysqli_stmt_bind_param($item_stmt, "i", $order_id);
        mysqli_stmt_execute($item_stmt);
        $items_res = mysqli_stmt_get_result($item_stmt);
        
        while ($it = mysqli_fetch_assoc($items_res)) {
            $p_id = $it['product_id'];
            $qty = $it['quantity'];
            
            $p_stmt = mysqli_prepare($conn, "SELECT quantity FROM products WHERE id = ? FOR UPDATE");
            mysqli_stmt_bind_param($p_stmt, "i", $p_id);
            mysqli_stmt_execute($p_stmt);
            $p_res = mysqli_stmt_get_result($p_stmt);
            if ($prod = mysqli_fetch_assoc($p_res)) {
                if ($prod['quantity'] < $qty) {
                    throw new Exception("Insufficient stock for product ID $p_id.");
                }
                $new_qty = $prod['quantity'] - $qty;
                $upd_p = mysqli_prepare($conn, "UPDATE products SET quantity = ? WHERE id = ?");
                mysqli_stmt_bind_param($upd_p, "di", $new_qty, $p_id);
                mysqli_stmt_execute($upd_p);
            }
        }
        
        // Create Shipment
        $qr_text = 'QR-' . $order['tracking_number'] . '-' . mt_rand(1000, 9999);
        $ins_ship = mysqli_prepare($conn, "INSERT INTO shipments (tracking_id, order_id, qr_code_text, status) VALUES (?, ?, ?, 'PENDING')");
        mysqli_stmt_bind_param($ins_ship, "sis", $order['tracking_number'], $order_id, $qr_text);
        mysqli_stmt_execute($ins_ship);
        
        $shipment_id = mysqli_insert_id($conn);
        
        // Log creation
        $ins_log = mysqli_prepare($conn, "INSERT INTO shipment_logs (shipment_id, status, updated_by) VALUES (?, 'PENDING', ?)");
        mysqli_stmt_bind_param($ins_log, "ii", $shipment_id, $farmer_id);
        mysqli_stmt_execute($ins_log);

        $new_status = 'approved';
    }

    $upd_o = mysqli_prepare($conn, "UPDATE orders SET status = ? WHERE id = ?");
    mysqli_stmt_bind_param($upd_o, "si", $new_status, $order_id);
    mysqli_stmt_execute($upd_o);

    mysqli_commit($conn);
    echo json_encode(['success' => true, 'new_status' => $new_status]);
} catch (Exception $e) {
    mysqli_rollback($conn);
    http_response_code(400);
    echo json_encode(['error' => $e->getMessage()]);
}
