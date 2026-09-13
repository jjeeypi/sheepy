-- Sheepy catalogue categories.
-- Safe to run repeatedly because category slugs are unique and upserted.

START TRANSACTION;

INSERT INTO categories (parent_category_id, name, slug) VALUES
    (NULL, 'Men', 'men'),
    (NULL, 'Women', 'women'),
    (NULL, 'Kids', 'kids'),
    (NULL, 'Accessories', 'accessories')
ON DUPLICATE KEY UPDATE
    parent_category_id = VALUES(parent_category_id),
    name = VALUES(name);

SET @men_category_id = (SELECT category_id FROM categories WHERE slug = 'men' LIMIT 1);
SET @women_category_id = (SELECT category_id FROM categories WHERE slug = 'women' LIMIT 1);
SET @kids_category_id = (SELECT category_id FROM categories WHERE slug = 'kids' LIMIT 1);
SET @accessories_category_id = (SELECT category_id FROM categories WHERE slug = 'accessories' LIMIT 1);

INSERT INTO categories (parent_category_id, name, slug) VALUES
    (@men_category_id, 'T-Shirts', 'men-t-shirts'),
    (@men_category_id, 'Shirts', 'men-shirts'),
    (@men_category_id, 'Pants', 'men-pants'),
    (@men_category_id, 'Shorts', 'men-shorts'),
    (@men_category_id, 'Jackets', 'men-jackets'),
    (@men_category_id, 'Sweaters & Hoodies', 'men-sweaters-hoodies'),
    (@men_category_id, 'Underwear', 'men-underwear'),
    (@men_category_id, 'Socks', 'men-socks'),

    (@women_category_id, 'T-Shirts', 'women-t-shirts'),
    (@women_category_id, 'Blouses', 'women-blouses'),
    (@women_category_id, 'Dresses', 'women-dresses'),
    (@women_category_id, 'Skirts', 'women-skirts'),
    (@women_category_id, 'Pants', 'women-pants'),
    (@women_category_id, 'Shorts', 'women-shorts'),
    (@women_category_id, 'Jackets', 'women-jackets'),
    (@women_category_id, 'Sweaters & Hoodies', 'women-sweaters-hoodies'),
    (@women_category_id, 'Underwear', 'women-underwear'),
    (@women_category_id, 'Socks', 'women-socks'),

    (@kids_category_id, 'T-Shirts', 'kids-t-shirts'),
    (@kids_category_id, 'Shirts & Blouses', 'kids-shirts-blouses'),
    (@kids_category_id, 'Dresses', 'kids-dresses'),
    (@kids_category_id, 'Pants', 'kids-pants'),
    (@kids_category_id, 'Shorts', 'kids-shorts'),
    (@kids_category_id, 'Jackets', 'kids-jackets'),
    (@kids_category_id, 'Sweaters & Hoodies', 'kids-sweaters-hoodies'),
    (@kids_category_id, 'Underwear', 'kids-underwear'),
    (@kids_category_id, 'Socks', 'kids-socks'),

    (@accessories_category_id, 'Bags', 'accessories-bags'),
    (@accessories_category_id, 'Hats & Caps', 'accessories-hats-caps'),
    (@accessories_category_id, 'Belts', 'accessories-belts'),
    (@accessories_category_id, 'Scarves', 'accessories-scarves'),
    (@accessories_category_id, 'Jewelry', 'accessories-jewelry'),
    (@accessories_category_id, 'Sunglasses', 'accessories-sunglasses')
ON DUPLICATE KEY UPDATE
    parent_category_id = VALUES(parent_category_id),
    name = VALUES(name);

COMMIT;
