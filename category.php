<?php

use function Twist\view;
use Twist\Model\Taxonomy\Category;

view('category.html.twig', [
	'category' => new Category(),
]);
