<article class="product-card">
    <div class="product-image-wrap">
        <a href="<?= $escape($productsUrl . '/' . $product->slug) ?>" tabindex="-1" aria-hidden="true">
            <?php if ($product->imageUrl !== null): ?>
                <img
                    src="<?= $escape($basePath . $product->imageUrl) ?>"
                    alt="<?= $escape(trim($product->imageAlt ?? '') !== '' ? $product->imageAlt : $product->name) ?>"
                    loading="lazy"
                    width="480"
                    height="600"
                >
            <?php else: ?>
                <span class="product-image-placeholder">Image coming soon</span>
            <?php endif; ?>
        </a>
        <button
            class="product-add-button"
            type="button"
            data-add-to-cart
            data-product-id="<?= $escape($product->id) ?>"
            aria-label="<?= $escape($product->stockQuantity > 0 ? 'Add ' . $product->name . ' to cart' : $product->name . ' is out of stock') ?>"
            <?= $product->stockQuantity < 1 ? 'disabled' : '' ?>
        >
            <svg aria-hidden="true" viewBox="0 0 24 24">
                <path d="M12 5v14M5 12h14"></path>
            </svg>
        </button>
    </div>
    <div class="product-card-copy">
        <h3><a href="<?= $escape($productsUrl . '/' . $product->slug) ?>"><?= $escape($product->name) ?></a></h3>
        <strong>$<?= $escape($product->price) ?></strong>
    </div>
</article>
