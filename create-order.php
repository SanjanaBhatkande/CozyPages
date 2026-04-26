<?php
/**
 * Create a PayPal Orders v2 order from the current session cart.
 * Returns: { id: "PAYPAL_ORDER_ID" } on success.
 */
declare(strict_types=1);
require_once __DIR__ . '/db.php';

header('Content-Type: application/json');

function fail(string $msg, int $code = 400, array $extra = []): void {
  http_response_code($code);
  echo json_encode(array_merge(['error' => $msg], $extra));
  exit;
}

function paypal_access_token(): string {
  $url = paypal_base_url() . '/v1/oauth2/token';
  $ch = curl_init($url);
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
  $err  = curl_error($ch);
  curl_close($ch);

  if ($resp === false) {
    throw new RuntimeException('PayPal token cURL error: ' . $err);
  }
  $data = json_decode($resp, true);
  if ($code !== 200 || empty($data['access_token'])) {
    throw new RuntimeException('PayPal token request failed: ' . $resp);
  }
  return $data['access_token'];
}

$summary = compute_cart_summary();
if (empty($summary['items'])) {
  fail('Cart is empty', 400);
}

// Build PayPal-formatted item list. All amounts in USD with 2 decimals.
$items = [];
$item_total = 0.0;
foreach ($summary['items'] as $it) {
  $unit = number_format($it['price'], 2, '.', '');
  $items[] = [
    'name'        => mb_substr($it['name'], 0, 127),
    'quantity'    => (string)$it['quantity'],
    'unit_amount' => ['currency_code' => 'USD', 'value' => $unit],
    'category'    => 'PHYSICAL_GOODS',
  ];
  $item_total += $it['price'] * $it['quantity'];
}
$item_total = round($item_total, 2);
$total_str  = number_format($item_total, 2, '.', '');

$order_payload = [
  'intent' => 'CAPTURE',
  'purchase_units' => [[
    'reference_id' => 'cozypages-' . bin2hex(random_bytes(4)),
    'description'  => 'CozyPages order',
    'amount' => [
      'currency_code' => 'USD',
      'value'         => $total_str,
      'breakdown' => [
        'item_total' => ['currency_code' => 'USD', 'value' => $total_str],
      ],
    ],
    'items' => $items,
  ]],
  'application_context' => [
    'brand_name'         => 'CozyPages',
    'shipping_preference' => 'NO_SHIPPING',
    'user_action'        => 'PAY_NOW',
  ],
];

try {
  $token = paypal_access_token();

  $ch = curl_init(paypal_base_url() . '/v2/checkout/orders');
  curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST           => true,
    CURLOPT_HTTPHEADER     => [
      'Content-Type: application/json',
      'Accept: application/json',
      'Authorization: Bearer ' . $token,
      'PayPal-Request-Id: ' . bin2hex(random_bytes(12)),
    ],
    CURLOPT_POSTFIELDS     => json_encode($order_payload),
    CURLOPT_TIMEOUT        => 30,
  ]);
  $resp = curl_exec($ch);
  $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
  $err  = curl_error($ch);
  curl_close($ch);

  if ($resp === false) {
    fail('PayPal cURL error: ' . $err, 502);
  }
  $data = json_decode($resp, true);
  if ($code < 200 || $code >= 300 || empty($data['id'])) {
    error_log('[CozyPages] PayPal createOrder failed: ' . $resp);
    fail('PayPal createOrder failed', 502, ['paypal' => $data]);
  }

  // Per spec, return ONLY { id }
  echo json_encode(['id' => $data['id']]);
} catch (Throwable $e) {
  error_log('[CozyPages] createOrder exception: ' . $e->getMessage());
  fail('Server error: ' . $e->getMessage(), 500);
}
