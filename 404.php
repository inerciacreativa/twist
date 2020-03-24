<?php

use Twist\Model\Post\Query;
use Twist\View;

View::display('404.html.twig', [
	'posts' => Query::search(),
]);
