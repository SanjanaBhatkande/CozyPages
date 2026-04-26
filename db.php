<?php
/**
 * CozyPages — Database connection
 * Edit the credentials below to match your XAMPP setup.
 */

declare(strict_types=1);

const DB_HOST = '127.0.0.1';
const DB_PORT = 3306;
const DB_NAME = 'cozypages3';
const DB_USER = 'root';
const DB_PASS = 'Hinata@07'; // default XAMPP root password is empty

/**
 * PayPal Sandbox credentials.
 * Replace with your own from https://developer.paypal.com/dashboard/applications/sandbox
 */
const PAYPAL_ENV          = 'sandbox'; // 'sandbox' or 'live'
const PAYPAL_CLIENT_ID    = 'AVdNfdWYSfKiIONvXb9RKQHAGwxyeYK0bboz3rjYOrXXwiv_SAPZiOl2xowxI6vApwKl9k7LKdLJTTGF';
const PAYPAL_CLIENT_SECRET = 'EMaqvVPpinGYpI9j6UbZ_FbPe2hUlQmErL3QnnQU1c923HoIXz-oLiMKW5G_68breI6w_vzRO152Tv4r';

function paypal_base_url(): string {
    return PAYPAL_ENV === 'live'
        ? 'https://api-m.paypal.com'
        : 'https://api-m.sandbox.paypal.com';
}

function db(): PDO {
    static $pdo = null;
    if ($pdo === null) {
        $dsn = sprintf(
            'mysql:host=%s;port=%d;dbname=%s;charset=utf8mb4',
            DB_HOST, DB_PORT, DB_NAME
        );
        try {
            $pdo = new PDO($dsn, DB_USER, DB_PASS, [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ]);
        } catch (PDOException $e) {
            http_response_code(500);
            die('Database connection failed: ' . htmlspecialchars($e->getMessage()));
        }
    }
    return $pdo;
}

/** Start session safely (idempotent). */
function start_session(): void {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
}

/** Get current cart from session: [productId => qty]. */
function get_cart(): array {
    start_session();
    return $_SESSION['cart'] ?? [];
}

function save_cart(array $cart): void {
    start_session();
    $_SESSION['cart'] = $cart;
}

/** Fetch all products (id, name, price, image). */
function fetch_products(): array {
    return db()->query('SELECT id, name, price, image, description, category FROM products ORDER BY id ASC')
               ->fetchAll();
}

/** Fetch products whose ids are in $ids. Returns [id => row]. */
function fetch_products_by_ids(array $ids): array {
    if (empty($ids)) return [];
    $ids = array_values(array_unique(array_map('intval', $ids)));
    $placeholders = implode(',', array_fill(0, count($ids), '?'));
    $stmt = db()->prepare(
        "SELECT id, name, price, image, description, category
         FROM products WHERE id IN ($placeholders)"
    );
    $stmt->execute($ids);
    $out = [];
    foreach ($stmt->fetchAll() as $row) {
        $out[(int)$row['id']] = $row;
    }
    return $out;
}

/** Compute cart line items + totals from current session cart. */
function compute_cart_summary(): array {
    $cart = get_cart();
    if (empty($cart)) {
        return ['items' => [], 'subtotal' => 0.0, 'item_count' => 0];
    }
    $products = fetch_products_by_ids(array_keys($cart));
    $items = [];
    $subtotal = 0.0;
    $count = 0;
    foreach ($cart as $pid => $qty) {
        $pid = (int)$pid;
        $qty = max(1, (int)$qty);
        if (!isset($products[$pid])) continue;
        $p = $products[$pid];
        $line = (float)$p['price'] * $qty;
        $subtotal += $line;
        $count    += $qty;
        $items[] = [
            'id'       => $pid,
            'name'     => $p['name'],
            'price'    => (float)$p['price'],
            'image'    => $p['image'],
            'quantity' => $qty,
            'line_total' => round($line, 2),
        ];
    }
    return [
        'items'      => $items,
        'subtotal'   => round($subtotal, 2),
        'item_count' => $count,
    ];
}

/** Safe HTML escape. */
function e(?string $s): string {
    return htmlspecialchars((string)$s, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}
