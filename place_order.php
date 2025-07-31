<?php
include 'config.php';

// Ambil data dari POST
$name = $_POST['name'];
$phone = $_POST['phone'];
$email = $_POST['email'];
$address = $_POST['address'];
$notes = $_POST['notes'] ?? '';
$cart = json_decode($_POST['cart'], true);

// Hitung total
$total = 0;
foreach ($cart as $item) {
    $total += $item['price'] * $item['quantity'];
}

// Mulai transaksi
$conn->begin_transaction();

try {
    // Simpan ke tabel orders
    $stmt = $conn->prepare("INSERT INTO orders (customer_name, phone, email, address, notes, total) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sssssd", $name, $phone, $email, $address, $notes, $total);
    $stmt->execute();
    $order_id = $stmt->insert_id;
    $stmt->close();

    // Simpan setiap item ke order_items
    $stmt = $conn->prepare("INSERT INTO order_items (order_id, menu_id, quantity, price) VALUES (?, ?, ?, ?)");
    foreach ($cart as $item) {
        $menu_id = $item['id'];
        $quantity = $item['quantity'];
        $price = $item['price'];
        $stmt->bind_param("iiid", $order_id, $menu_id, $quantity, $price);
        $stmt->execute();
    }
    $stmt->close();

    // Commit transaksi
    $conn->commit();

    // Kirim respon sukses
    echo json_encode(['status' => 'success', 'order_id' => $order_id]);
} catch (Exception $e) {
    // Rollback transaksi jika ada error
    $conn->rollback();
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}

$conn->close();
?>