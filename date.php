<?php

use function Twist\view;
use Twist\Model\Post\Query;

view('date.twig', [
	'posts' => new Query(),
]);
