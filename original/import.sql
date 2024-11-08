# Product Category
INSERT INTO product_category (id, parent_id, label, priority) SELECT t.id, NULL, t.category, t.id FROM (SELECT id, category from categoryList ORDER BY id) as t;

# Product Types
INSERT INTO product_category (parent_id, label, priority) SELECT t.cat_id, t.prodType, t.id FROM (SELECT id, cat_id, prodType FROM productTypeList ORDER BY id) as t;

# Materials
INSERT INTO material (id, material) SELECT id, material FROM materials ORDER BY id;
INSERT INTO material VALUES (21, "UNKNOWN(21)");

# Products
INSERT INTO product (id, product_category_id, material_id, class, name, item_number, size, shape, quantity, weight, is_viewable, notes, image)
 SELECT t.id, pc.id, t.mat_id, t.class, t.name, t.itemNumber, t.size, t.shape, t.pkgQty, t.weight, t.isViewable, t.notes, '' as image
  FROM (SELECT id, ptype_id, mat_id, class, name, itemNumber, size, shape, pkgQty, weight, isViewable, notes FROM products WHERE ptype_id > 0 ORDER BY id) t
  JOIN product_category pc ON t.ptype_id = pc.priority AND pc.parent_id IS NOT NULL;

# Product pricing
INSERT INTO product_quantity (product_id, quantity, price, label)
 SELECT id, qty1, price1, label1 FROM products WHERE ptype_id > 0 AND qty1 > 0 ORDER BY id;
INSERT INTO product_quantity (product_id, quantity, price, label)
 SELECT id, qty2, price2, label2 FROM products WHERE ptype_id > 0 AND qty2 > 0 ORDER BY id;
INSERT INTO product_quantity (product_id, quantity, price, label)
 SELECT id, qty3, price3, label3 FROM products WHERE ptype_id > 0 AND qty3 > 0 ORDER BY id;
INSERT INTO product_quantity (product_id, quantity, price, label)
 SELECT id, qty4, price4, label4 FROM products WHERE ptype_id > 0 AND qty4 > 0 ORDER BY id;
INSERT INTO product_quantity (product_id, quantity, price, label)
 SELECT id, qty5, price5, label5 FROM products WHERE ptype_id > 0 AND qty5 > 0 ORDER BY id;
INSERT INTO product_quantity (product_id, quantity, price, label)
 SELECT id, qty6, price6, label6 FROM products WHERE ptype_id > 0 AND qty6 > 0 ORDER BY id;