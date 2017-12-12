<?php

use function Twist\view;
use Twist\Model\Post\Query;
use Twist\Model\Taxonomy\Tag;

view('tag.twig', [
	'tag' => new Tag(),
	'posts' => new Query(),
]);
