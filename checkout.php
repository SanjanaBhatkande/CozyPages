<?php
require_once __DIR__ . '/db.php';
$page_title = 'Checkout — CozyPages';
$page_id    = 'checkout';
$summary    = compute_cart_summary();

// If cart is empty, redirect back.
if (empty($summary['items'])) {
  header('Location: cart.php');
  exit;
}

include __DIR__ . '/header.php';
?>
<div class="section-head">
  <h1 style="margin:0;">Checkout</h1>
  <a href="cart.php" class="muted">← Back to cart</a>
</div>

<div class="checkout-grid">
  <section class="panel">
    <h2>Order summary</h2>
    <ul class="order-list">
      <?php foreach ($summary['items'] as $item): ?>
        <li>
          <span><?= e($item['name']) ?> <span class="muted">× <?= (int)$item['quantity'] ?></span></span>
          <span>$<?= number_format($item['line_total'], 2) ?></span>
        </li>
      <?php endforeach; ?>
    </ul>
    <div class="summary-total">
      <span>Total</span>
      <span>$<?= number_format($summary['subtotal'], 2) ?> USD</span>
    </div>
  </section>

  <aside class="panel">
    <h2>Pay securely</h2>
    <div class="secure-note">
      <span aria-hidden="true">🔒</span>
      <div>
        <strong>Secure payment.</strong>
        Your transaction is processed by PayPal. We never see or store your card details.
      </div>
    </div>
    <div id="paypal-button-container"></div>
    <p class="muted" style="font-size:0.8rem;text-align:center;margin-top:0.75rem;">
      You'll be redirected to a confirmation page once payment is complete.
    </p>
  </aside>
</div>

<script src="https://www.paypal.com/sdk/js?client-id=<?= e(PAYPAL_CLIENT_ID) ?>&currency=USD&intent=capture"></script>
<script>
(function () {
  if (!window.paypal) {
    console.error('[CozyPages] PayPal SDK failed to load. Check PAYPAL_CLIENT_ID in db.php.');
    document.getElementById('paypal-button-container').innerHTML =
      '<p style="color:var(--danger);font-size:0.9rem;">Could not load PayPal. Please refresh or check your client ID.</p>';
    return;
  }

  const overlay = document.getElementById('loadingOverlay');
  function showLoading(show) {
    overlay.classList.toggle('show', !!show);
    overlay.setAttribute('aria-hidden', show ? 'false' : 'true');
  }

  paypal.Buttons({
    style: {
      shape: 'pill',
      color: 'gold',
      layout: 'vertical',
      label: 'paypal',
    },

    createOrder: function () {
      return fetch('create-order.php', {
        method: 'POST',
        headers: { 'Accept': 'application/json' },
      })
        .then((res) => res.json())
        .then((data) => {
          console.log('[CozyPages] create-order response:', data);
          if (!data || !data.id) {
            throw new Error(data && data.error ? data.error : 'Failed to create order');
          }
          return data.id;
        })
        .catch((err) => {
          console.error('[CozyPages] createOrder error:', err);
          window.toast(err.message || 'Could not start payment', 'error');
          throw err;
        });
    },

    onApprove: function (data) {
      showLoading(true);
      return fetch('capture-order.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
        body: JSON.stringify({ orderID: data.orderID }),
      })
        .then((res) => res.json())
        .then((details) => {
          console.log('[CozyPages] capture-order response:', details);
          if (!details.ok) {
            throw new Error(details.error || 'Payment could not be captured');
          }
          window.location.href = 'success.php?order=' + encodeURIComponent(details.paypal_order_id);
        })
        .catch((err) => {
          showLoading(false);
          console.error('[CozyPages] onApprove error:', err);
          window.toast(err.message || 'Payment failed', 'error');
        });
    },

    onCancel: function () {
      window.toast('Payment cancelled', 'error');
    },

    onError: function (err) {
      console.error('[CozyPages] PayPal onError:', err);
      window.toast('A PayPal error occurred. Please try again.', 'error');
    },
  }).render('#paypal-button-container');
})();
</script>

<?php include __DIR__ . '/footer.php'; ?>
