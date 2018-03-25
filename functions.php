<?php

include_once __DIR__ . '/vendor/autoload.php';
include_once __DIR__ . '/engine/app.php';

use Twist\Service\CoreServiceProvider;
use function Twist\theme;

theme()->services(new CoreServiceProvider())->config([
	'api.rest'            => false,
	'api.oembed'          => false,
	'filter.relative_url' => true,
	'filter.emoji'        => true,
	'filter.content'      => [
		'attributes' => [],
		'styles'     => [],
		'comments'   => true,
	],
])->styles([
	[
		'id'     => 'twist',
		'load'   => 'scripts/main.css',
		'parent' => true,
	]
])->scripts([
	[
		'id'     => 'twist',
		'load'   => 'scripts/main.js',
		'parent' => true,
		'deps'   => ['jquery']
	],
	[
		'id'   => 'comment-reply',
		'load' => function () {
			return is_single() && comments_open() && get_option('thread_comments');
		},
	],
])->sidebars([
	[
		'id'            => 'sidebar',
		'name'          => __('Sidebar', 'twist'),
		'description'   => __('Add widgets here to appear in your sidebar.', 'twist'),
		'before_widget' => '<aside id="%1$s" class="widget %2$s">',
		'after_widget'  => '</aside>',
		'before_title'  => '<h2 class="widget-title">',
		'after_title'   => '</h2>',
	],
	[
		'id'            => 'footer-primary',
		'name'          => __('Primary Footer', 'twist'),
		'description'   => __('Add widgets here to appear in your footer.', 'twist'),
		'before_widget' => '<aside id="%1$s" class="widget %2$s">',
		'after_widget'  => '</aside>',
		'before_title'  => '<h2 class="widget-title">',
		'after_title'   => '</h2>',
	],
	[
		'id'            => 'footer-secondary',
		'name'          => __('Secondary Footer', 'twist'),
		'description'   => __('Add widgets here to appear in your footer.', 'twist'),
		'before_widget' => '<aside id="%1$s" class="widget %2$s">',
		'after_widget'  => '</aside>',
		'before_title'  => '<h2 class="widget-title">',
		'after_title'   => '</h2>',
	],
])->menus([
	'primary' => __('Primary Menu', 'twist'),
	'social'  => __('Social Links Menu', 'twist'),
])->contact([
	'googleplus' => __('Google+', 'twist'),
	'twitter'    => __('Twitter', 'twist'),
	'facebook'   => __('Facebook', 'twist'),
	'linkedin'   => __('LinkedIn', 'twist'),
])->thumbnail(850, 510, true);
