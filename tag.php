<?php

use function Twist\view;
use Twist\Model\Taxonomy\Tag;

view('tag.html.twig', [
	'tag' => new Tag(),
]);
