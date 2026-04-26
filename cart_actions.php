<?php
/**
 * AJAX endpoint for cart mutations.
 * Body: { action: 'add'|'update'|'remove', id: int, delta?: int }
 */
declare(strict_types=1);
require_once __DIR__ . '/db.php';

header('Content-Type: application/json');
start_session();

function json_out(array $data, int $status = 200): void {
  http_response_code($status);
  echo json_encode($data);
  exit;
}

$raw = file_get_contents('php://input');
$body = json_decode($raw ?: '[]', true) ?: [];
$action = $body['action'] ?? '';
$id     = (int)($body['id'] ?? 0);

if ($id <= 0 || !in_array($action, ['add', 'update', 'remove'], true)) {
  json_out(['ok' => false, 'error' => 'Invalid request'], 400);
}

// Verify product exists
$stmt = db()->prepare('SELECT id, name FROM products WHERE id = ? LIMIT 1');
$stmt->execute([$id]);
$product = $stmt->fetch();
if (!$product) {
  json_out(['ok' => false, 'error' => 'Product not found'], 404);
}

$cart = get_cart();

switch ($action) {
  case 'add':
    $cart[$id] = ($cart[$id] ?? 0) + 1;
    break;

  case 'update':
    $delta = (int)($body['delta'] ?? 0);
    $newQty = ($cart[$id] ?? 0) + $delta;
    if ($newQty <= 0) {
      unset($cart[$id]);
    } else {
      $cart[$id] = min($newQty, 99);
    }
    break;

  case 'remove':
    unset($cart[$id]);
    break;
}

save_cart($cart);
$summary = compute_cart_summary();

json_out([
  'ok'           => true,
  'product_name' => $product['name'],
  'item_count'   => $summary['item_count'],
  'subtotal'     => $summary['subtotal'],
]);
