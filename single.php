<?php

use function Twist\view;
use Twist\Model\Post\Posts;

view('article.twig', [
    'posts' => new Posts(),
]);
