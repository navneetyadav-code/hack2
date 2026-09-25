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
$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['name'], $data['address'], $data['phone'], $data['payment_method'], $data['cart']) || empty($data['cart'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Missing required fields or empty cart']);
    exit;
}

$email = isset($data['email']) ? $data['email'] : '';
$payment_method = $data['payment_method'];

mysqli_begin_transaction($conn);

try {
    // Group cart items by farmer_id
    $orders_by_farmer = [];

    foreach ($data['cart'] as $item) {
        $prod_id = intval($item['product_id']);
        $req_qty = floatval($item['quantity']);

        // Fetch product and lock row for update
        $stmt = mysqli_prepare($conn, "SELECT farmer_id, quantity, price_per_kg FROM products WHERE id = ? FOR UPDATE");
        mysqli_stmt_bind_param($stmt, "i", $prod_id);
        mysqli_stmt_execute($stmt);
        $res = mysqli_stmt_get_result($stmt);
        if ($row = mysqli_fetch_assoc($res)) {
            if ($req_qty > $row['quantity']) {
                throw new Exception("Not enough quantity for product ID $prod_id");
            }
            
            $farmer_id = $row['farmer_id'];
            $price = $row['price_per_kg'];
            $item_total = $req_qty * $price;
            
            if (!isset($orders_by_farmer[$farmer_id])) {
                $orders_by_farmer[$farmer_id] = [
                    'total_amount' => 0,
                    'items' => []
                ];
            }
            
            $orders_by_farmer[$farmer_id]['total_amount'] += $item_total;
            $orders_by_farmer[$farmer_id]['items'][] = [
                'product_id' => $prod_id,
                'quantity' => $req_qty,
                'price' => $price
            ];

            // Deduct inventory
            $new_qty = $row['quantity'] - $req_qty;
            $upd_stmt = mysqli_prepare($conn, "UPDATE products SET quantity = ? WHERE id = ?");
            mysqli_stmt_bind_param($upd_stmt, "di", $new_qty, $prod_id);
            mysqli_stmt_execute($upd_stmt);
        } else {
            throw new Exception("Product ID $prod_id not found");
        }
    }

    // Create orders
    foreach ($orders_by_farmer as $farmer_id => $order_data) {
        $total = $order_data['total_amount'];
        $ins_order = mysqli_prepare($conn, "INSERT INTO orders (buyer_id, farmer_id, total_amount, payment_method, delivery_name, delivery_address, delivery_phone, delivery_email, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'pending')");
        mysqli_stmt_bind_param($ins_order, "iidsssss", $buyer_id, $farmer_id, $total, $payment_method, $data['name'], $data['address'], $data['phone'], $email);
        mysqli_stmt_execute($ins_order);
        
        $order_id = mysqli_insert_id($conn);

        foreach ($order_data['items'] as $it) {
            $ins_item = mysqli_prepare($conn, "INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)");
            mysqli_stmt_bind_param($ins_item, "iidd", $order_id, $it['product_id'], $it['quantity'], $it['price']);
            mysqli_stmt_execute($ins_item);
        }
    }

    mysqli_commit($conn);
    echo json_encode(['success' => true]);
} catch (Exception $e) {
    mysqli_rollback($conn);
    http_response_code(400);
    echo json_encode(['error' => $e->getMessage()]);
}
