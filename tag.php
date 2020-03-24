<?php

use Twist\Model\Taxonomy\Tag;
use Twist\View;

View::display('tag.html.twig', [
	'tag' => new Tag(),
]);
