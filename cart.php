<?php
require_once __DIR__ . '/db.php';
$page_title = 'Your Cart — CozyPages';
$page_id    = 'cart';
$summary    = compute_cart_summary();
include __DIR__ . '/header.php';
?>
<div class="section-head">
  <h1 style="margin:0;">Your Cart</h1>
  <a href="index.php" class="muted">← Continue shopping</a>
</div>

<?php if (empty($summary['items'])): ?>
  <div class="empty-state">
    <div class="icon">🥐</div>
    <h2>Your cart is empty</h2>
    <p>Looks like you haven't added anything yet. Browse the shop and pick something cozy.</p>
    <a href="index.php" class="btn btn-primary">Browse the shop</a>
  </div>
<?php else: ?>
  <div class="cart-layout">
    <div class="cart-list">
      <?php foreach ($summary['items'] as $item): ?>
        <div class="cart-row" data-cart-row="<?= (int)$item['id'] ?>">
          <img
            src="<?= e($item['image']) ?>"
            alt="<?= e($item['name']) ?>"
            onerror="this.onerror=null;this.src='https://placehold.co/200x200/e9dcc4/6b4a2b?text=CP';"
          />
          <div class="cart-meta">
            <h3><?= e($item['name']) ?></h3>
            <div class="price-each">$<?= number_format($item['price'], 2) ?> each</div>
            <div class="qty-control" role="group" aria-label="Quantity">
              <button class="qty-btn" data-qty-dec aria-label="Decrease">−</button>
              <span class="qty-value"><?= (int)$item['quantity'] ?></span>
              <button class="qty-btn" data-qty-inc aria-label="Increase">+</button>
            </div>
          </div>
          <div>
            <div class="cart-line-total">$<?= number_format($item['line_total'], 2) ?></div>
            <button class="cart-remove" data-remove>Remove</button>
          </div>
        </div>
      <?php endforeach; ?>
    </div>

    <aside class="cart-summary">
      <h2 style="margin-top:0;">Summary</h2>
      <div class="summary-row"><span>Items</span><span><?= (int)$summary['item_count'] ?></span></div>
      <div class="summary-row"><span>Subtotal</span><span>$<?= number_format($summary['subtotal'], 2) ?></span></div>
      <div class="summary-row"><span>Shipping</span><span>Free</span></div>
      <div class="summary-total">
        <span>Total</span>
        <span>$<?= number_format($summary['subtotal'], 2) ?></span>
      </div>
      <a href="checkout.php" class="btn btn-primary btn-block">Proceed to Checkout</a>
      <p class="muted" style="font-size:0.8rem;text-align:center;margin-top:0.8rem;">
        Secure payment via PayPal
      </p>
    </aside>
  </div>
<?php endif; ?>

<?php include __DIR__ . '/footer.php'; ?>
