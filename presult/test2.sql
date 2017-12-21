-- Найти оплаченные/предоплаченные заказы с суммами оплаты. 
-- (то есть есть хоть одна запись в таблице payment)

SELECT o.id, o.status, o.finish_date, SUM( p.summa ) AS 'suma'
FROM `order` o
INNER JOIN `payment` p ON o.id = p.order_id
GROUP BY o.id;

-- Найти сумму закупки товара, по заказам в январе. 
-- (сумма закупки товара находится в таблице product_item, колонка price, 
-- необходимо найти id товара и сколько потрачено денег на товары, 
-- проданные по заказам в январе. дату заказа можно определить по колонке finish_date в таблице order)


SELECT pi.product_id, SUM(pi.price) AS 'summa'
  FROM `product_item` pi
  JOIN `order_item` oi ON pi.order_item_id = oi.id 
  JOIN `order` o ON oi.order_id = o.id
  WHERE o.finish_date >= '2017-01-01' AND o.finish_date < '2017-02-01'
  GROUP BY pi.product_id;


-- Найти товары (id) и количество, которых не хватает на складе по оплаченным заказам 
-- (не хватает, означает, что в таблице product_item недостаточно строк со статусом free для некоторых товаров)

SELECT oi.product_id, (COUNT(oi.product_id) - (SELECT COUNT(*)
                                                FROM product_item pi
                                                WHERE pi.status = 'free'
                                                  AND pi.product_id = oi.product_id) ) AS num
  FROM order_item oi
  JOIN `order` o ON oi.order_id = o.id
  WHERE o.status = 'new'
    AND (oi.order_id IN (SELECT DISTINCT p.order_id FROM payment p))
  GROUP BY oi.product_id
HAVING num > 0;
