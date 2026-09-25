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
$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    $stmt = mysqli_prepare($conn, "SELECT id, name, quantity, price_per_kg, harvest_date, created_at FROM products WHERE farmer_id = ? ORDER BY created_at DESC");
    mysqli_stmt_bind_param($stmt, "i", $farmer_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $products = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $products[] = $row;
    }
    echo json_encode($products);
    exit;
}

if ($method === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    if (!isset($data['name'], $data['quantity'], $data['price_per_kg'])) {
        http_response_code(400);
        echo json_encode(['error' => 'Missing required fields']);
        exit;
    }

    $name = trim($data['name']);
    $quantity = floatval($data['quantity']);
    $price = floatval($data['price_per_kg']);
    $harvest_date = isset($data['harvest_date']) ? $data['harvest_date'] : null;

    $stmt = mysqli_prepare($conn, "INSERT INTO products (farmer_id, name, quantity, price_per_kg, harvest_date) VALUES (?, ?, ?, ?, ?)");
    mysqli_stmt_bind_param($stmt, "isdds", $farmer_id, $name, $quantity, $price, $harvest_date);
    
    if (mysqli_stmt_execute($stmt)) {
        echo json_encode(['success' => true, 'id' => mysqli_insert_id($conn)]);
    } else {
        http_response_code(500);
        echo json_encode(['error' => 'Failed to add product']);
    }
    exit;
}

if ($method === 'DELETE') {
    $id = isset($_GET['id']) ? intval($_GET['id']) : 0;
    if ($id <= 0) {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid ID']);
        exit;
    }

    $stmt = mysqli_prepare($conn, "DELETE FROM products WHERE id = ? AND farmer_id = ?");
    mysqli_stmt_bind_param($stmt, "ii", $id, $farmer_id);
    if (mysqli_stmt_execute($stmt)) {
        echo json_encode(['success' => true]);
    } else {
        http_response_code(500);
        echo json_encode(['error' => 'Failed to delete product']);
    }
    exit;
}

http_response_code(405);
echo json_encode(['error' => 'Method not allowed']);
