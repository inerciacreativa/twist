<?php

use Twist\Model\Post\Query;
use function Twist\view;

view('404.html.twig', [
	'posts' => Query::search()
]);
