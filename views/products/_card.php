<article>
    <a href="<?= $escape($productsUrl . '/' . $product->slug) ?>">
        <?php if ($product->imageUrl !== null): ?>
            <img
                src="<?= $escape($basePath . $product->imageUrl) ?>"
                alt="<?= $escape(trim($product->imageAlt ?? '') !== '' ? $product->imageAlt : $product->name) ?>"
                width="240"
                loading="lazy"
            >
        <?php endif; ?>
        <h3><?= $escape($product->name) ?></h3>
    </a>
    <?php if ($product->categoryName !== null): ?>
        <p><?= $escape($product->categoryName) ?></p>
    <?php endif; ?>
    <p>$<?= $escape($product->price) ?></p>
    <p><?= $product->stockQuantity > 0 ? 'In stock' : 'Out of stock' ?></p>
</article>
