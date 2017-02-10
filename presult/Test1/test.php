<?php

require_once __DIR__ . "/../../DbSimple/Generic.php";

require_once __DIR__ . "/i_model.php";
require_once __DIR__ . "/database.php";
require_once __DIR__ . "/model.php";
require_once __DIR__ . "/blog_post.php";


/***** Test 1 start *****/

$post_1 = new blog_post(1);
$post_2 = new blog_post(2);

echo $post_1->get_field('date');
echo $post_1->get_field('text');

$post_1->delete();
$post_2->set_field('name', 'Some new name');
$post_2->save();


$post_3 = new blog_post();
$post_3->set_field('name', 'Test');
$post_3->set_field('text', 'Test text');
$post_3->save();
echo $post_3->id();

/***** Test 1 end *****/
