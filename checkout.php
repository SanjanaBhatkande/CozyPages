<?php include 'db.php'; ?>

<!DOCTYPE html>
<html>
<head>
    <title>Checkout - CozyPages</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<h1>💳 Checkout</h1>

<div class="cart-box">

<?php
$total = 0;

if (!empty($_SESSION['cart'])) {

    foreach ($_SESSION['cart'] as $id => $qty) {

        // 🔴 skip invalid IDs
        if ($id <= 0) continue;

        $result = $conn->query("SELECT * FROM products WHERE id=$id");

        // 🔴 safety check
        if (!$result || $result->num_rows == 0) {
            continue;
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
    echo "<p class='empty'>Cart is empty 😢</p>";
}

echo "<h2>Total: ₹$total</h2>";
?>

<!-- PayPal Button -->
<form action="https://www.sandbox.paypal.com/cgi-bin/webscr" method="post">

    <input type="hidden" name="cmd" value="_xclick">
    <input type="hidden" name="business" value="sb-asnwy50025006@business.example.com">
    <input type="hidden" name="item_name" value="CozyPages Order">

    <input type="hidden" name="amount" value="<?php echo round($total / 83, 2); ?>">
    <input type="hidden" name="currency_code" value="USD">

    <input type="hidden" name="return" value="http://localhost/cozypages/success.php">
    <input type="hidden" name="cancel_return" value="http://localhost/cozypages/cart.php">

    <button type="submit" class="checkout-btn">
        Pay with PayPal 💳
    </button>

</form>

</div>

</body>
</html>