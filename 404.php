<?php

use function Twist\view;
use Twist\Model\Post\Query;

view('404.twig', [
	'posts' => new Query(),
]);
