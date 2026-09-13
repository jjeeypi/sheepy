<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= $editing ? 'Edit Product' : 'Add Product' ?> | Sheepy</title>
</head>
<body>
    <main>
        <h1><?= $editing ? 'Edit Product' : 'Add Product' ?></h1>
        <p><a href="<?= $escape($manageProductsUrl) ?>">Back to Manage Products</a></p>

        <form method="post" action="<?= $escape($formUrl) ?>" enctype="multipart/form-data">
            <input type="hidden" name="_token" value="<?= $escape($csrfToken) ?>">

            <p>
                <label for="category_id">Category</label><br>
                <select id="category_id" name="category_id">
                    <option value="">Uncategorized</option>
                    <?php foreach ($categories as $category): ?>
                        <option
                            value="<?= $escape($category['id']) ?>"
                            <?= (string) ($old['category_id'] ?? '') === (string) $category['id'] ? 'selected' : '' ?>
                        ><?= $escape($category['name']) ?></option>
                    <?php endforeach; ?>
                </select>
                <?php if (isset($errors['category_id'])): ?>
                    <br><span role="alert"><?= $escape($errors['category_id']) ?></span>
                <?php endif; ?>
            </p>

            <p>
                <label for="sku">SKU</label><br>
                <input
                    id="sku"
                    name="sku"
                    type="text"
                    value="<?= $escape($old['sku'] ?? '') ?>"
                    maxlength="64"
                    pattern="[A-Za-z0-9._-]+"
                    required
                >
                <?php if (isset($errors['sku'])): ?>
                    <br><span role="alert"><?= $escape($errors['sku']) ?></span>
                <?php endif; ?>
            </p>

            <p>
                <label for="name">Product name</label><br>
                <input
                    id="name"
                    name="name"
                    type="text"
                    value="<?= $escape($old['name'] ?? '') ?>"
                    maxlength="200"
                    required
                >
                <?php if (isset($errors['name'])): ?>
                    <br><span role="alert"><?= $escape($errors['name']) ?></span>
                <?php endif; ?>
            </p>

            <p>
                <label for="description">Description</label><br>
                <textarea
                    id="description"
                    name="description"
                    maxlength="5000"
                    rows="6"
                ><?= $escape($old['description'] ?? '') ?></textarea>
                <?php if (isset($errors['description'])): ?>
                    <br><span role="alert"><?= $escape($errors['description']) ?></span>
                <?php endif; ?>
            </p>

            <p>
                <label for="price">Price</label><br>
                <input
                    id="price"
                    name="price"
                    type="number"
                    value="<?= $escape($old['price'] ?? '') ?>"
                    min="0.01"
                    max="99999999.99"
                    step="0.01"
                    required
                >
                <?php if (isset($errors['price'])): ?>
                    <br><span role="alert"><?= $escape($errors['price']) ?></span>
                <?php endif; ?>
            </p>

            <p>
                <label for="stock_quantity">Stock quantity</label><br>
                <input
                    id="stock_quantity"
                    name="stock_quantity"
                    type="number"
                    value="<?= $escape($old['stock_quantity'] ?? '0') ?>"
                    min="0"
                    max="4294967295"
                    step="1"
                    required
                >
                <?php if (isset($errors['stock_quantity'])): ?>
                    <br><span role="alert"><?= $escape($errors['stock_quantity']) ?></span>
                <?php endif; ?>
            </p>

            <p>
                <label>
                    <input
                        name="is_active"
                        type="checkbox"
                        value="1"
                        <?= !empty($old['is_active']) ? 'checked' : '' ?>
                    >
                    Visible to customers
                </label>
            </p>

            <?php if ($editing && $currentImageUrl !== null): ?>
                <p>
                    Current image:<br>
                    <img
                        src="<?= $escape($currentImageUrl) ?>"
                        alt="<?= $escape($product->imageAlt ?? $product->name) ?>"
                        width="160"
                    >
                </p>
            <?php endif; ?>

            <p>
                <label for="image">
                    <?= $editing ? 'Replace product image (optional)' : 'Product image' ?>
                </label><br>
                <input
                    id="image"
                    name="image"
                    type="file"
                    accept="image/jpeg,image/png,image/webp,image/gif"
                    <?= $editing ? '' : 'required' ?>
                >
                <br><small>JPG, PNG, WebP, or GIF; maximum 5 MB. Alt text defaults to the product name.</small>
                <?php if (isset($errors['image'])): ?>
                    <br><span role="alert"><?= $escape($errors['image']) ?></span>
                <?php endif; ?>
            </p>

            <button type="submit"><?= $editing ? 'Save Changes' : 'Create Product' ?></button>
        </form>
    </main>
</body>
</html>
