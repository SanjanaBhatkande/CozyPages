<?php
/**
 * Reusable header partial.
 * Set $page_title and optional $page_id before including.
 */
require_once __DIR__ . '/db.php';
$summary = compute_cart_summary();
$cart_count = $summary['item_count'];
$page_title = $page_title ?? 'CozyPages — Books, Coffee & Comfort';
$page_id    = $page_id ?? '';
?><!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title><?= e($page_title) ?></title>
  <meta name="description" content="CozyPages — a cozy book café selling beautifully chosen books, fresh pastries, and warm drinks." />
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,500;9..144,600;9..144,700&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet" />
  <link rel="stylesheet" href="style.css" />
</head>
<body data-page="<?= e($page_id) ?>">
  <header class="navbar">
    <div class="nav-inner">
      <a href="index.php" class="brand">
        <span class="brand-mark">Cp</span>
        CozyPages
      </a>
      <nav>
        <ul class="nav-links">
          <li><a href="index.php">Shop</a></li>
          <li><a href="index.php#about">About</a></li>
          <li><a href="index.php#contact">Visit</a></li>
        </ul>
      </nav>
      <a href="cart.php" class="nav-cart" aria-label="Cart">
        <span>Cart</span>
        <span class="cart-badge"><?= (int)$cart_count ?></span>
      </a>
    </div>
  </header>
  <main>
    <div class="container">
