<div
    class="cart-app"
    data-cart-app
    data-cart-url="<?= $escape($cartUrl) ?>"
    data-cart-items-url="<?= $escape($cartItemsUrl) ?>"
    data-checkout-url="<?= $escape($checkoutUrl) ?>"
    data-checkout-confirm-url="<?= $escape($checkoutConfirmUrl) ?>"
    data-csrf-token="<?= $escape($csrfToken) ?>"
>
    <button
        class="cart-trigger"
        type="button"
        data-cart-open
        aria-label="Open shopping cart"
        aria-haspopup="dialog"
        aria-controls="cart-dialog"
    >
        <svg aria-hidden="true" viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
            <path d="M3 3h2l2.2 10.2a2 2 0 0 0 2 1.6h7.7a2 2 0 0 0 1.9-1.4L21 6H6"></path>
            <circle cx="10" cy="20" r="1"></circle>
            <circle cx="18" cy="20" r="1"></circle>
        </svg>
        <span>Cart</span>
        <span class="cart-count" data-cart-count aria-label="<?= $escape($cartItemCount) ?> items in cart">
            <?= $escape($cartItemCount) ?>
        </span>
    </button>

    <dialog class="cart-dialog" id="cart-dialog" data-cart-dialog aria-labelledby="cart-modal-title">
        <div class="cart-modal-panel" data-cart-panel>
            <p>Loading your cart...</p>
        </div>
    </dialog>

    <p class="cart-live-region" data-cart-live role="status" aria-live="polite"></p>
</div>
