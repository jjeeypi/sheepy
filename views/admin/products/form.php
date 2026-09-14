<?php
$pageTitle = $editing ? 'Edit Product' : 'Add Product';
require __DIR__ . '/../_head.php';
?>

<div class="adm-section">
    <!-- Header -->
    <div class="adm-section-header">
        <div>
            <h1 class="adm-section-title"><?= $editing ? 'Edit Product' : 'Add Product' ?></h1>
            <p class="adm-section-sub">
                <a href="<?= $escape($manageProductsUrl) ?>" class="adm-see-all">← Back to Manage Products</a>
            </p>
        </div>
    </div>

    <!-- Form -->
    <form method="post" action="<?= $escape($formUrl) ?>" enctype="multipart/form-data">
        <input type="hidden" name="_token" value="<?= $escape($csrfToken) ?>">
        
        <div class="adm-form-grid">
            
            <!-- Left Column: Primary details -->
            <div class="adm-form-card">
                <div class="adm-form-card-header">
                    <h3 class="adm-form-card-title">General information</h3>
                </div>
                
                <div class="adm-form-card-body">
                    <div class="adm-field <?= isset($errors['name']) ? 'is-invalid' : '' ?>">
                        <label for="name">Product Name</label>
                        <input id="name" name="name" type="text" value="<?= $escape($old['name'] ?? '') ?>" required maxlength="128">
                        <?php if (isset($errors['name'])): ?>
                            <span class="adm-field-error"><?= $escape($errors['name']) ?></span>
                        <?php endif; ?>
                    </div>
                    
                    <div class="adm-field <?= isset($errors['description']) ? 'is-invalid' : '' ?>">
                        <label for="description">Description</label>
                        <textarea id="description" name="description" maxlength="2000"><?= $escape($old['description'] ?? '') ?></textarea>
                        <?php if (isset($errors['description'])): ?>
                            <span class="adm-field-error"><?= $escape($errors['description']) ?></span>
                        <?php endif; ?>
                    </div>
                    
                    <div class="adm-field-row">
                        <div class="adm-field <?= isset($errors['sku']) ? 'is-invalid' : '' ?>">
                            <label for="sku">SKU</label>
                            <input id="sku" name="sku" type="text" value="<?= $escape($old['sku'] ?? '') ?>" required maxlength="64" pattern="[A-Za-z0-9._-]+">
                            <span class="adm-field-hint">Letters, numbers, dots, dashes, underscores</span>
                            <?php if (isset($errors['sku'])): ?>
                                <span class="adm-field-error"><?= $escape($errors['sku']) ?></span>
                            <?php endif; ?>
                        </div>
                        
                        <div class="adm-field <?= isset($errors['category_id']) ? 'is-invalid' : '' ?>">
                            <label for="category_id">Category</label>
                            <select id="category_id" name="category_id">
                                <option value="">Uncategorized</option>
                                <?php foreach ($categories as $category): ?>
                                    <option value="<?= $escape($category['id']) ?>" <?= (string) ($old['category_id'] ?? '') === (string) $category['id'] ? 'selected' : '' ?>>
                                        <?= $escape($category['name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <?php if (isset($errors['category_id'])): ?>
                                <span class="adm-field-error"><?= $escape($errors['category_id']) ?></span>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <div class="adm-field-row">
                        <div class="adm-field <?= isset($errors['price']) ? 'is-invalid' : '' ?>">
                            <label for="price">Price (USD)</label>
                            <input id="price" name="price" type="number" step="0.01" min="0" value="<?= $escape($old['price'] ?? '') ?>" required>
                            <?php if (isset($errors['price'])): ?>
                                <span class="adm-field-error"><?= $escape($errors['price']) ?></span>
                            <?php endif; ?>
                        </div>
                        
                        <div class="adm-field <?= isset($errors['stock_quantity']) ? 'is-invalid' : '' ?>">
                            <label for="stock_quantity">Stock Quantity</label>
                            <input id="stock_quantity" name="stock_quantity" type="number" step="1" min="0" value="<?= $escape($old['stock_quantity'] ?? '0') ?>" required>
                            <?php if (isset($errors['stock_quantity'])): ?>
                                <span class="adm-field-error"><?= $escape($errors['stock_quantity']) ?></span>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Right Column: Media & Status -->
            <div style="display: flex; flex-direction: column; gap: 1.25rem;">
                <!-- Media -->
                <div class="adm-form-card">
                    <div class="adm-form-card-header">
                        <h3 class="adm-form-card-title">Media</h3>
                    </div>
                    <div class="adm-form-card-body">
                        <div class="adm-field <?= isset($errors['image']) ? 'is-invalid' : '' ?>">
                            <?php if ($currentImageUrl !== null): ?>
                                <div class="adm-image-preview">
                                    <img src="<?= $escape($currentImageUrl) ?>" alt="Current product image">
                                    <div class="adm-image-preview-caption">Current image</div>
                                </div>
                                <div style="margin-top: 0.75rem;">
                                    <label for="image">Replace image (optional)</label>
                                    <input id="image" name="image" type="file" accept="image/jpeg,image/png,image/webp" style="padding: 0.4rem; font-size: 12.5px;">
                                </div>
                            <?php else: ?>
                                <label for="image">Product image</label>
                                <div class="adm-image-drop">
                                    <svg viewBox="0 0 24 24">
                                        <rect x="3" y="3" width="18" height="18" rx="2" ry="2"/>
                                        <circle cx="8.5" cy="8.5" r="1.5"/>
                                        <polyline points="21 15 16 10 5 21"/>
                                    </svg>
                                    <p>Click to upload<br>or drag and drop</p>
                                    <input id="image" name="image" type="file" accept="image/jpeg,image/png,image/webp">
                                </div>
                                <span class="adm-field-hint" style="text-align: center; display: block; margin-top: 0.25rem;">PNG, JPG or WEBP (max 5MB)</span>
                            <?php endif; ?>
                            
                            <?php if (isset($errors['image'])): ?>
                                <span class="adm-field-error" style="margin-top: 0.5rem; display: block;"><?= $escape($errors['image']) ?></span>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                
                <!-- Status -->
                <div class="adm-form-card">
                    <div class="adm-form-card-header">
                        <h3 class="adm-form-card-title">Status</h3>
                    </div>
                    <div class="adm-form-card-body">
                        <label class="adm-toggle-label">
                            <span class="adm-toggle-info">
                                <strong>Active</strong>
                                <span>Visible to customers in the store</span>
                            </span>
                            <span class="adm-toggle-wrap">
                                <input type="checkbox" name="is_active" class="adm-toggle-input" value="1" <?= ($old['is_active'] ?? false) ? 'checked' : '' ?>>
                                <span class="adm-toggle-track"></span>
                            </span>
                        </label>
                    </div>
                </div>
            </div>
            
        </div>
        
        <!-- Actions -->
        <div class="adm-form-card" style="margin-top: 1.25rem;">
            <div class="adm-form-actions">
                <a href="<?= $escape($manageProductsUrl) ?>" class="adm-btn-secondary">Cancel</a>
                <button type="submit" class="adm-btn-primary">
                    <svg viewBox="0 0 24 24">
                        <path d="M19 21H5a2 2 0 01-2-2V5a2 2 0 012-2h11l5 5v11a2 2 0 01-2 2z"/>
                        <polyline points="17 21 17 13 7 13 7 21"/>
                        <polyline points="7 3 7 8 15 8"/>
                    </svg>
                    <?= $editing ? 'Update Product' : 'Save Product' ?>
                </button>
            </div>
        </div>
    </form>

</div>

<?php require __DIR__ . '/../_foot.php'; ?>
