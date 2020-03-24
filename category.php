<?php

use Twist\Model\Taxonomy\Category;
use Twist\View;

View::display('category.html.twig', [
	'category' => new Category(),
]);
