<?php
$pageTitle = 'Products';
require __DIR__ . '/../_head.php';
?>

<div class="adm-section">
    <!-- Header -->
    <div class="adm-section-header">
        <div>
            <h1 class="adm-section-title">Manage Products</h1>
            <p class="adm-section-sub">Create and edit products in your catalog</p>
        </div>
        <a href="<?= $escape($addProductUrl) ?>" class="adm-btn-primary">
            <svg viewBox="0 0 24 24">
                <line x1="12" y1="5" x2="12" y2="19"/>
                <line x1="5" y1="12" x2="19" y2="12"/>
            </svg>
            Add Product
        </a>
    </div>

    <!-- Notices -->
    <?php if ($notice !== null): ?>
        <div class="adm-notice adm-notice-success" role="status">
            <svg viewBox="0 0 24 24">
                <path d="M22 11.08V12a10 10 0 11-5.93-9.14"/>
                <polyline points="22 4 12 14.01 9 11.01"/>
            </svg>
            <?= $escape($notice) ?>
        </div>
    <?php endif; ?>
    
    <?php if ($pageError !== null): ?>
        <div class="adm-notice adm-notice-error" role="alert">
            <svg viewBox="0 0 24 24">
                <circle cx="12" cy="12" r="10"/>
                <line x1="12" y1="8" x2="12" y2="12"/>
                <line x1="12" y1="16" x2="12.01" y2="16"/>
            </svg>
            <?= $escape($pageError) ?>
        </div>
    <?php endif; ?>

    <!-- Table -->
    <?php if ($products === []): ?>
        <div class="adm-empty">
            <svg style="width: 48px; height: 48px; margin: 0 auto 1rem; stroke: #cfc4ac; stroke-width: 1; fill: none;" viewBox="0 0 24 24">
                <path d="M20.59 13.41l-7.17 7.17a2 2 0 01-2.83 0L2 12V2h10l8.59 8.59a2 2 0 010 2.82z"/>
                <circle cx="7" cy="7" r="1.5"/>
            </svg>
            <h3>No products found</h3>
            <p>Your catalog is currently empty. Get started by adding your first product.</p>
            <a href="<?= $escape($addProductUrl) ?>" class="adm-btn-secondary">
                Add Product
            </a>
        </div>
    <?php else: ?>
        <div class="adm-table-card">
            <table class="adm-table">
                <thead>
                    <tr>
                        <th style="width: 40%">Product</th>
                        <th>Category</th>
                        <th>Price</th>
                        <th>Stock</th>
                        <th>Visibility</th>
                        <th style="width: 1%;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($products as $product): ?>
                        <tr>
                            <td>
                                <div class="adm-table-product">
                                    <div class="adm-table-thumb">
                                        <?php if ($product->imageUrl !== null): ?>
                                            <img src="<?= $escape($basePath . $product->imageUrl) ?>" alt="">
                                        <?php else: ?>
                                            <div class="adm-table-thumb-empty">
                                                <svg viewBox="0 0 24 24"><path d="M4 22h14a2 2 0 002-2V7.5L14.5 2H6a2 2 0 00-2 2v4"/></svg>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                    <div style="min-width: 0;">
                                        <div class="adm-table-product-name"><?= $escape($product->name) ?></div>
                                        <div class="adm-table-product-meta">SKU: <?= $escape($product->sku) ?></div>
                                    </div>
                                </div>
                            </td>
                            <td class="col-muted"><?= $escape($product->categoryName ?? 'Uncategorized') ?></td>
                            <td><?= $escape($product->price) ?></td>
                            <td><?= $escape($product->stockQuantity) ?></td>
                            <td>
                                <?php if ($product->isActive): ?>
                                    <span class="adm-chip adm-chip-visible">Visible</span>
                                <?php else: ?>
                                    <span class="adm-chip adm-chip-hidden">Hidden</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div class="adm-tbl-actions">
                                    <a href="<?= $escape($productsUrl . '/' . $product->id . '/edit') ?>" class="adm-tbl-btn" aria-label="Edit">
                                        <svg viewBox="0 0 24 24">
                                            <path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7"/>
                                            <path d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z"/>
                                        </svg>
                                        Edit
                                    </a>
                                    
                                    <form method="post" action="<?= $escape($productsUrl . '/' . $product->id . '/delete') ?>" onsubmit="return confirm('Are you sure you want to delete this product?');" style="margin:0;">
                                        <input type="hidden" name="_token" value="<?= $escape($csrfToken) ?>">
                                        <button type="submit" class="adm-tbl-btn adm-tbl-btn-danger" <?= $product->canBeDeleted() ? '' : 'disabled title="Cannot delete: product is used in orders"' ?> aria-label="Delete">
                                            <svg viewBox="0 0 24 24">
                                                <polyline points="3 6 5 6 21 6"/>
                                                <path d="M19 6v14a2 2 0 01-2 2H7a2 2 0 01-2-2V6m3 0V4a2 2 0 012-2h4a2 2 0 012 2v2"/>
                                            </svg>
                                            Delete
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>

</div>

<?php require __DIR__ . '/../_foot.php'; ?>
