<?php

use Twist\Model\Post\Query;
use Twist\Twist;

Twist::view('404.html.twig', [
	'posts' => Query::search(),
]);
