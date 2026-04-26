<?php
require_once __DIR__ . '/db.php';

start_session();

$paypal_order_id = $_GET['order'] ?? '';
$order = null;

if ($paypal_order_id !== '') {
  $stmt = db()->prepare('SELECT id, paypal_order_id, total, items_json, created_at FROM orders WHERE paypal_order_id = ? LIMIT 1');
  $stmt->execute([$paypal_order_id]);
  $order = $stmt->fetch();
}

// Clear the cart now that the order is confirmed.
$_SESSION['cart'] = [];

$items = [];
if ($order && !empty($order['items_json'])) {
  $items = json_decode($order['items_json'], true) ?: [];
}

$page_title = 'Order Confirmed — CozyPages';
$page_id    = 'success';
include __DIR__ . '/header.php';
?>
<div class="success-card">
  <div class="success-mark" aria-hidden="true">✓</div>
  <h1>Thank you for your order!</h1>
  <p style="color:var(--muted);max-width:460px;margin:0.5rem auto 0;">
    Your payment has been received. We're already getting your CozyPages order ready.
  </p>

  <?php if ($order): ?>
    <div class="order-id">Order #<?= e($order['paypal_order_id']) ?></div>

    <div style="margin-top:2rem;text-align:left;">
      <h2 style="font-size:1.1rem;text-align:center;">Order summary</h2>
      <ul class="order-list" style="max-width:440px;margin:0 auto;">
        <?php foreach ($items as $i): ?>
          <li>
            <span><?= e($i['name']) ?> <span class="muted">× <?= (int)$i['quantity'] ?></span></span>
            <span>$<?= number_format((float)$i['line_total'], 2) ?></span>
          </li>
        <?php endforeach; ?>
      </ul>
      <div class="summary-total" style="max-width:440px;margin:0 auto;">
        <span>Total paid</span>
        <span>$<?= number_format((float)$order['total'], 2) ?> USD</span>
      </div>
    </div>
  <?php else: ?>
    <p class="muted">We couldn't find the order details, but your payment went through.</p>
  <?php endif; ?>

  <div style="margin-top:2rem;">
    <a href="index.php" class="btn btn-primary">Back to shop</a>
  </div>
</div>

<?php include __DIR__ . '/footer.php'; ?>
