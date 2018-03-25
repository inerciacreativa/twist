<?php

use function Twist\view;
use Twist\Model\Post\Query;

view('search.twig', [
	'posts' => new Query(),
]);
