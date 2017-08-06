<?php

use function Twist\view;
use Twist\Model\Post\Query;

view('article.twig', [
	'posts' => new Query(),
]);
