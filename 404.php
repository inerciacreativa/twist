<?php

use Twist\Model\Post\PostsQuery;
use Twist\View;

View::display('404.html.twig', [
	'posts' => PostsQuery::search(),
]);
