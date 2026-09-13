<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Manage Products | Sheepy</title>
</head>
<body>
    <main>
        <h1>Manage Products</h1>
        <p><a href="<?= $escape($dashboardUrl) ?>">Back to Dashboard</a></p>
        <form method="get" action="<?= $escape($addProductUrl) ?>">
            <button type="submit">Add Product</button>
        </form>

        <?php if ($notice !== null): ?>
            <p role="status"><?= $escape($notice) ?></p>
        <?php endif; ?>
        <?php if ($pageError !== null): ?>
            <p role="alert"><?= $escape($pageError) ?></p>
        <?php endif; ?>

        <?php if ($products === []): ?>
            <p>No products have been created.</p>
        <?php else: ?>
            <table>
                <thead>
                    <tr>
                        <th scope="col">Image</th>
                        <th scope="col">Product</th>
                        <th scope="col">SKU</th>
                        <th scope="col">Price</th>
                        <th scope="col">Stock</th>
                        <th scope="col">Visibility</th>
                        <th scope="col">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($products as $product): ?>
                        <tr>
                            <td>
                                <?php if ($product->imageUrl !== null): ?>
                                    <img
                                        src="<?= $escape($basePath . $product->imageUrl) ?>"
                                        alt="<?= $escape($product->imageAlt ?? $product->name) ?>"
                                        width="100"
                                    >
                                <?php else: ?>
                                    No image
                                <?php endif; ?>
                            </td>
                            <td><?= $escape($product->name) ?></td>
                            <td><?= $escape($product->sku) ?></td>
                            <td><?= $escape($product->price) ?></td>
                            <td><?= $escape($product->stockQuantity) ?></td>
                            <td><?= $product->isActive ? 'Visible' : 'Hidden' ?></td>
                            <td>
                                <a href="<?= $escape($productsUrl . '/' . $product->id . '/edit') ?>">Edit</a>
                                <?php if ($product->canBeDeleted()): ?>
                                    <form
                                        method="post"
                                        action="<?= $escape($productsUrl . '/' . $product->id . '/delete') ?>"
                                    >
                                        <input type="hidden" name="_token" value="<?= $escape($csrfToken) ?>">
                                        <button type="submit">Delete</button>
                                    </form>
                                <?php else: ?>
                                    <button type="button" disabled title="Products included in orders cannot be deleted.">
                                        Delete
                                    </button>
                                    <small>Included in <?= $escape($product->orderCount) ?> order item(s)</small>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </main>
</body>
</html>
