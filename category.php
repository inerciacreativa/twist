<?php

use function Twist\view;
use Twist\Model\Taxonomy\Category;

view('category.twig', [
	'category' => new Category(),
]);
