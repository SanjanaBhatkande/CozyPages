<?php 
include 'db.php';
?>

<!DOCTYPE html>
<html>
<head>
    <title>Cart - CpzyPages</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<h1>🛒 Your Cart</h1>

<div class="cart-box">

<?php
$total = 0;

if (!empty($_SESSION['cart'])) {

    foreach ($_SESSION['cart'] as $id => $qty) {

        // 🔴 skip invalid IDs immediately
        if ($id <= 0) continue;

        $result = $conn->query("SELECT * FROM products WHERE id=$id");

        if (!$result || $result->num_rows == 0) {
            continue; // 🔥 silently ignore broken products
        }

        $product = $result->fetch_assoc();

        $subtotal = $product['price'] * $qty;
        $total += $subtotal;

        echo "
        <div class='cart-item'>
            <div>
                <h3>{$product['name']}</h3>
                <p>Qty: $qty</p>
            </div>
            <p>₹$subtotal</p>
        </div>
        ";
    }

} else {
    echo "<p class='empty'>Your cart is empty 😢</p>";
}

echo "<h2>Total: ₹$total</h2>";
?>

<a class="checkout-btn" href="checkout.php">Proceed to Checkout 💳</a>

</div>

</body>
</html>