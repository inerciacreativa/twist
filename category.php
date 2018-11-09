<?php

use Twist\Model\Taxonomy\Category;
use Twist\Twist;

Twist::view('category.html.twig', [
	'category' => new Category(),
]);
