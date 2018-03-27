<?php

use function Twist\view;
use Twist\Model\Post\Query;

view('page.twig', [
	'posts' => new Query(),
	'latest' => Query::latest(5),
]);
