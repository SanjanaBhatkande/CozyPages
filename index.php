<?php include 'db.php'; ?>

<!DOCTYPE html>
<html>
<head>
    <title>CozyPages</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<h1>☕📚 CozyPages Café</h1>

<div style="text-align:center;">
    <a href="cart.php">🛒 View Cart</a>
</div>

<div class="products">
<?php
$result = $conn->query("SELECT * FROM products");

while($row = $result->fetch_assoc()) {
?>
    <div class="card">
        <img src="images/<?php echo $row['image']; ?>">
        <h3><?php echo $row['name']; ?></h3>
        <p>₹<?php echo $row['price']; ?></p>

        <form method="post" action="add_to_cart.php">
            <input type="hidden" name="id" value="<?php echo $row['id']; ?>">
            <button>Add to Cart 🛍️</button>
        </form>
    </div>
<?php } ?>
</div>

</body>
</html>