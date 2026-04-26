# CozyPages — Setup Guide

A PHP + MySQL + PayPal (Orders API v2) e-commerce demo for a cozy book café.

## 1. Install on XAMPP

1. Copy the entire `cozypages` folder into `C:\xampp\htdocs\` (Windows) or
   `/Applications/XAMPP/htdocs/` (macOS).
2. Start **Apache** and **MySQL** in the XAMPP control panel.

## 2. Create the database

Open <http://localhost/phpmyadmin>, click **Import**, choose `schema.sql`, and run it.
This creates the `cozypages` database with `products` (seeded) and `orders` tables.

Alternatively, from a terminal:

```bash
mysql -u root < schema.sql
```

## 3. Configure credentials

Open `db.php` and set:

```php
const DB_USER = 'root';
const DB_PASS = '';                          // your MySQL password (XAMPP default is empty)

const PAYPAL_ENV           = 'sandbox';
const PAYPAL_CLIENT_ID     = 'YOUR_PAYPAL_SANDBOX_CLIENT_ID';
const PAYPAL_CLIENT_SECRET = 'YOUR_PAYPAL_SANDBOX_CLIENT_SECRET';
```

Get sandbox credentials at <https://developer.paypal.com/dashboard/applications/sandbox>.

## 4. Open the site

<http://localhost/cozypages/index.php>

## 5. Test a payment

- Add items, go to checkout, click the PayPal button.
- Log in with a **sandbox personal** test account (Developer Dashboard → Testing Tools → Sandbox Accounts).
- After a successful capture you'll be redirected to `success.php` and the order will appear in the `orders` table.

## File map

```
index.php           Home / product grid
cart.php            Cart with qty controls + remove
checkout.php        Order summary + PayPal Buttons
success.php         Confirmation + clears cart
cart_actions.php    AJAX: add / update / remove
create-order.php    PayPal Orders v2 — create
capture-order.php   PayPal Orders v2 — capture + DB insert
db.php              PDO connection + helpers + PayPal config
header.php / footer.php   Shared layout
style.css           Cozy beige/brown design system
script.js           Toasts, fetch-based cart actions
schema.sql          Database schema + seed products
images/             Product imagery
```

## Notes

- Currency is hardcoded to **USD**.
- Prepared statements are used everywhere.
- PayPal integration uses **only** Orders API v2 (no `webscr`, no `_xclick`, no forms).
- `ON DUPLICATE KEY UPDATE` makes capture idempotent if the user double-clicks.
