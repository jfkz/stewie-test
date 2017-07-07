-- First query
SELECT order.id, payment.summa FROM `order`,`payment` WHERE payment.order_id = order.id
-- Second query
SELECT product_item.product_id, SUM(product_item.price) FROM product_item 
LEFT JOIN order_item ON product_item.order_item_id =  order_item.id
LEFT JOIN `order` ON order_item.order_id = order.id
WHERE MONTH(`order`.finish_date) = 1
GROUP BY product_item.product_id
-- Third query
SELECT order_item.product_id, count(order_item.product_id) as 'product_count'
FROM `order` LEFT JOIN `payment` ON `order`.id = payment.order_id
LEFT JOIN order_item ON order_item.order_id = `order`.id
LEFT JOIN product_item on order_item.id =  product_item.order_item_id
WHERE product_item.product_id IS NULL
GROUP BY order_item.product_id