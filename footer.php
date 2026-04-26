    </div>
  </main>
  <footer class="footer">
    <div class="container">
      <div class="brand">
        <span class="brand-mark" style="background:linear-gradient(135deg,#c8a97a,#b9764a)">Cp</span>
        CozyPages
      </div>
      <p>© <?= date('Y') ?> CozyPages Café. Brewed with care, bound with love.</p>
      <p><a href="index.php#contact">12 Linden Lane · Open daily 8am – 9pm</a></p>
    </div>
  </footer>
  <div class="loading-overlay" id="loadingOverlay" aria-hidden="true">
    <div class="spinner"></div>
    <p style="color:var(--brown-deep);font-weight:500;">Processing your payment…</p>
  </div>
  <div class="toast-stack" aria-live="polite" aria-atomic="true"></div>
  <script src="script.js"></script>
</body>
</html>
