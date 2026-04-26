<?php
require_once __DIR__ . '/db.php';

$page_title = 'CozyPages — Books, Coffee & Comfort';
$page_id    = 'home';

$products = fetch_products();

// Group by category safely
$grouped = [];
foreach ($products as $p) {
  $category = $p['category'] ?? 'Others';
  $grouped[$category][] = $p;
}

include __DIR__ . '/header.php';
?>

<!-- HERO -->
<section class="hero">
  <span class="eyebrow">Book café · Est. 2021</span>
  <h1>Stories worth lingering over.</h1>
  <p>
    A cozy corner of curated books, warm pastries, and slow afternoons.
    Order online and pick up — or have your favorites delivered straight from our shelves.
  </p>
</section>

<!-- PRODUCT SECTIONS WRAPPER -->
<div class="container">

<?php foreach ($grouped as $category => $items): ?>
  
  <section class="category-section">

    <!-- CATEGORY HEADER -->
    <div class="section-head">
      <h2><?= e($category) ?></h2>
      <span class="muted">
        <?= count($items) ?> item<?= count($items) === 1 ? '' : 's' ?>
      </span>
    </div>

    <!-- PRODUCT GRID -->
    <div class="product-grid">
      <?php foreach ($items as $p): ?>
        <article class="product-card">

          <div class="product-image-wrap">
            <img
              src="<?= e($p['image']) ?>"
              alt="<?= e($p['name']) ?>"
              loading="lazy"
              onerror="this.onerror=null;this.src='https://placehold.co/600x600/e9dcc4/6b4a2b?text=CozyPages';"
            />
          </div>

          <div class="product-body">
            <div class="product-cat">
              <?= e($p['category'] ?? 'General') ?>
            </div>

            <h3 class="product-name">
              <?= e($p['name']) ?>
            </h3>

            <p class="product-desc">
              <?= e($p['description'] ?? 'No description available.') ?>
            </p>

            <div class="product-row">
              <span class="product-price">
                $<?= number_format((float)$p['price'], 2) ?>
              </span>

              <button class="btn btn-accent" data-add-to-cart="<?= (int)$p['id'] ?>">
                Add to cart
              </button>
            </div>
          </div>

        </article>
      <?php endforeach; ?>
    </div>

  </section>

<?php endforeach; ?>

</div>

<!-- ABOUT -->
<section class="container" style="margin-top:4rem;">
  <div class="panel">
    <h2>About CozyPages</h2>
    <p style="color:var(--muted); max-width:700px;">
      We're a small independent café and bookstore on the corner of Linden Lane.
      Every title on our shelves is hand-picked, every pastry baked the same morning,
      and every coffee pulled by people who genuinely want you to stay a while.
    </p>
  </div>
</section>

<!-- CONTACT -->
<section class="container" style="margin-top:1.5rem;">
  <div class="panel">
    <h2>Visit us</h2>
    <p style="color:var(--muted);">
      12 Linden Lane · Open daily 8am – 9pm · hello@cozypages.cafe
    </p>
  </div>
</section>

<?php include __DIR__ . '/footer.php'; ?>