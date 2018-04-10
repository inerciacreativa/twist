<?php

use function Twist\view;
use Twist\Model\Taxonomy\Tag;

view('tag.twig', [
	'tag' => new Tag(),
]);
