<?php

use Twist\Model\Taxonomy\Tag;
use Twist\Twist;

Twist::view('tag.html.twig', [
	'tag' => new Tag(),
]);
