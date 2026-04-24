<?php
include 'db.php';

/* 🔴 MUST ensure session starts */
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/* 🔴 Get product ID safely */
$id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);

/* 🔴 Validate ID */
if (!$id || $id <= 0) {
    die("❌ Invalid product ID received");
}

/* 🔴 Initialize cart if not exists */
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

/* 🔴 Check if product exists in database (IMPORTANT FIX) */
$stmt = $conn->prepare("SELECT id FROM products WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die("❌ Product does not exist in database");
}

/* 🔴 Add to cart (quantity system) */
if (isset($_SESSION['cart'][$id])) {
    $_SESSION['cart'][$id] += 1;
} else {
    $_SESSION['cart'][$id] = 1;
}

/* 🔴 Redirect back */
header("Location: cart.php");
exit();
?>