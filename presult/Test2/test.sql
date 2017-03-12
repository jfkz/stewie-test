
/*

  1. Найти оплаченные/предоплаченные заказы с суммами оплаты. (то есть есть хоть одна запись в таблице payment)

*/

SELECT DISTINCT
	`order`.*
FROM
	`order`
    LEFT JOIN `payment` ON (`payment`.`order_id` = `order`.`id`)
WHERE
	`payment`.`id` IS NOT NULL;

/*

  2. Найти сумму закупки товара, по заказам в январе. (сумма закупки товара находится в таблице product_item, колонка price,
  необходимо найти id товара и сколько потрачено денег на товары, проданные по заказам в январе.
  дату заказа можно определить по колонке finish_date в таблице order)

*/

SELECT
  sum(`product_item`.`price`) as total_sum
FROM
  `order`
  LEFT JOIN `order_item` ON (`order_item`.`order_id` = `order`.`id`)
  LEFT JOIN `product_item` ON (`product_item`.`order_item_id` = `order_item`.`id`)
WHERE
  `order`.`finish_date` BETWEEN '2017-01-01 00:00:00' AND '2017-01-31 23:59:59';

/*

  3. Найти товары (id) и количество, которых не хватает на складе по оплаченным заказам (не хватает, означает,
  что в таблице product_item недостаточно строк со статусом free для некоторых товаров)

 */

SELECT
  `tmp_1`.`product_id`,

  -- количетво необходимых для выдачи минус количество доступных на складе
  `tmp_1`.`count_not_issued` - SUM(IF(`product_item`.`status` = 'free', 1, 0)) as count_deficit
FROM
    -- все позиции заказов, по которым не было выдачи
  (SELECT
    `order_item`.`product_id`,
    count(*) as count_not_issued
  FROM
    `order_item`
    LEFT JOIN `product_item` ON (`product_item`.`order_item_id` = `order_item`.`id`)
  WHERE
    `product_item`.`id` IS NULL AND
    `order_item`.`order_id` in (
                                -- ищем только среди оплаченных заказов
                                SELECT DISTINCT
                                  `order`.`id`
                                FROM
                                  `order`
                                    LEFT JOIN `payment` ON (`payment`.`order_id` = `order`.`id`)
                                WHERE
                                  `payment`.`id` IS NOT NULL)
  GROUP BY `order_item`.product_id) as tmp_1

  LEFT JOIN product_item ON (product_item.product_id = tmp_1.product_id)

GROUP BY `tmp_1`.`product_id`
HAVING count_deficit > 0


