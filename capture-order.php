<?php
/**
 * Capture a PayPal Orders v2 order, verify COMPLETED, store in DB.
 * Body: { orderID: "PAYPAL_ORDER_ID" }
 * Returns: { ok: true, paypal_order_id, total } or { ok: false, error }
 */
declare(strict_types=1);
require_once __DIR__ . '/db.php';

header('Content-Type: application/json');

function out(array $data, int $code = 200): void {
  http_response_code($code);
  echo json_encode($data);
  exit;
}

function paypal_access_token(): string {
  $ch = curl_init(paypal_base_url() . '/v1/oauth2/token');
  curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST           => true,
    CURLOPT_USERPWD        => PAYPAL_CLIENT_ID . ':' . PAYPAL_CLIENT_SECRET,
    CURLOPT_HTTPHEADER     => [
      'Accept: application/json',
      'Content-Type: application/x-www-form-urlencoded',
    ],
    CURLOPT_POSTFIELDS     => 'grant_type=client_credentials',
    CURLOPT_TIMEOUT        => 20,
  ]);
  $resp = curl_exec($ch);
  $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
  curl_close($ch);
  $data = json_decode((string)$resp, true);
  if ($code !== 200 || empty($data['access_token'])) {
    throw new RuntimeException('PayPal auth failed: ' . $resp);
  }
  return $data['access_token'];
}

$body    = json_decode(file_get_contents('php://input') ?: '[]', true) ?: [];
$orderID = trim((string)($body['orderID'] ?? ''));

if ($orderID === '' || !preg_match('/^[A-Z0-9\-]{6,64}$/i', $orderID)) {
  out(['ok' => false, 'error' => 'Missing or invalid orderID'], 400);
}

try {
  $token = paypal_access_token();

  $ch = curl_init(paypal_base_url() . '/v2/checkout/orders/' . urlencode($orderID) . '/capture');
  curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST           => true,
    CURLOPT_HTTPHEADER     => [
      'Content-Type: application/json',
      'Accept: application/json',
      'Authorization: Bearer ' . $token,
      'PayPal-Request-Id: capture-' . bin2hex(random_bytes(8)),
    ],
    CURLOPT_POSTFIELDS     => '{}',
    CURLOPT_TIMEOUT        => 30,
  ]);
  $resp = curl_exec($ch);
  $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
  curl_close($ch);

  $data = json_decode((string)$resp, true);

  if ($code < 200 || $code >= 300) {
    error_log('[CozyPages] capture failed: ' . $resp);
    out(['ok' => false, 'error' => 'Capture failed', 'paypal' => $data], 502);
  }

  $status = $data['status'] ?? '';
  if ($status !== 'COMPLETED') {
    out(['ok' => false, 'error' => 'Payment not completed (status: ' . $status . ')'], 400);
  }

  // Pull totals from the capture response
  $captures = $data['purchase_units'][0]['payments']['captures'] ?? [];
  $cap = $captures[0] ?? null;
  $cap_status = $cap['status'] ?? '';
  if ($cap_status !== 'COMPLETED') {
    out(['ok' => false, 'error' => 'Capture not completed (status: ' . $cap_status . ')'], 400);
  }
  $total = (float)($cap['amount']['value'] ?? 0);
  $payer_email = $data['payer']['email_address'] ?? null;

  // Snapshot the cart at time of purchase
  $summary = compute_cart_summary();
  $items_json = json_encode($summary['items'], JSON_UNESCAPED_UNICODE);

  // Persist (idempotent on paypal_order_id)
  $stmt = db()->prepare(
    'INSERT INTO orders (paypal_order_id, total, payer_email, items_json)
     VALUES (?, ?, ?, ?)
     ON DUPLICATE KEY UPDATE total = VALUES(total), payer_email = VALUES(payer_email), items_json = VALUES(items_json)'
  );
  $stmt->execute([$orderID, $total, $payer_email, $items_json]);

  out([
    'ok'              => true,
    'paypal_order_id' => $orderID,
    'total'           => $total,
    'payer_email'     => $payer_email,
  ]);
} catch (Throwable $e) {
  error_log('[CozyPages] capture exception: ' . $e->getMessage());
  out(['ok' => false, 'error' => 'Server error: ' . $e->getMessage()], 500);
}
