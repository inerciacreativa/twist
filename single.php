<?php

use function Twist\view;
use Twist\Model\Post\Query;

view('post.twig', [
	'posts'  => Query::main(),
	'latest' => Query::latest(5),
]);

