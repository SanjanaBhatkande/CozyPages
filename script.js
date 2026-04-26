/* ============================================================
   CozyPages — frontend script
   - Toast notifications
   - Add to cart / qty / remove via fetch -> cart_actions.php
   - Cart count badge updates
   ============================================================ */

(function () {
  'use strict';

  /* ----------------------- Toasts ----------------------- */
  function ensureToastStack() {
    let stack = document.querySelector('.toast-stack');
    if (!stack) {
      stack = document.createElement('div');
      stack.className = 'toast-stack';
      document.body.appendChild(stack);
    }
    return stack;
  }

  window.toast = function (message, type) {
    const stack = ensureToastStack();
    const el = document.createElement('div');
    el.className = 'toast ' + (type || '');
    el.textContent = message;
    stack.appendChild(el);
    setTimeout(() => {
      el.classList.add('fade-out');
      el.addEventListener('animationend', () => el.remove(), { once: true });
    }, 2600);
  };

  /* ----------------------- Cart API ----------------------- */
  async function cartAction(action, payload) {
    const body = Object.assign({ action }, payload || {});
    try {
      const res = await fetch('cart_actions.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
        body: JSON.stringify(body),
      });
      const data = await res.json();
      if (!res.ok || !data.ok) {
        throw new Error(data.error || 'Request failed');
      }
      updateCartBadge(data.item_count);
      return data;
    } catch (err) {
      console.error('[CozyPages] cart action failed:', err);
      window.toast(err.message || 'Something went wrong', 'error');
      throw err;
    }
  }

  function updateCartBadge(count) {
    const badge = document.querySelector('.cart-badge');
    if (badge) {
      badge.textContent = String(count);
      badge.style.display = count > 0 ? '' : 'none';
    }
  }

  /* ----------------------- Add to cart ----------------------- */
  document.addEventListener('click', async (e) => {
    const btn = e.target.closest('[data-add-to-cart]');
    if (!btn) return;
    const id = parseInt(btn.dataset.addToCart, 10);
    if (!id) return;

    btn.disabled = true;
    const original = btn.textContent;
    btn.textContent = 'Adding…';
    try {
      const data = await cartAction('add', { id });
      window.toast(`Added “${data.product_name}” to your cart`, 'success');
    } catch (_) { /* toast already shown */ }
    finally {
      btn.disabled = false;
      btn.textContent = original;
    }
  });

  /* ----------------------- Cart page interactions ----------------------- */
  document.addEventListener('click', async (e) => {
    const inc = e.target.closest('[data-qty-inc]');
    const dec = e.target.closest('[data-qty-dec]');
    const rm  = e.target.closest('[data-remove]');

    if (inc || dec) {
      const row = (inc || dec).closest('[data-cart-row]');
      const id  = parseInt(row.dataset.cartRow, 10);
      const delta = inc ? 1 : -1;
      try {
        const data = await cartAction('update', { id, delta });
        renderCartFromState(data);
      } catch (_) {}
    }

    if (rm) {
      const row = rm.closest('[data-cart-row]');
      const id  = parseInt(row.dataset.cartRow, 10);
      try {
        const data = await cartAction('remove', { id });
        window.toast('Item removed', 'success');
        renderCartFromState(data);
      } catch (_) {}
    }
  });

  function renderCartFromState(data) {
    // The server returns the full updated summary.
    // Easiest robust approach: reload the cart page so totals/empty-state are correct.
    if (document.body.dataset.page === 'cart') {
      window.location.reload();
    }
  }

  /* ----------------------- Init ----------------------- */
  document.addEventListener('DOMContentLoaded', () => {
    const badge = document.querySelector('.cart-badge');
    if (badge && badge.textContent.trim() === '0') {
      badge.style.display = 'none';
    }
  });
})();
