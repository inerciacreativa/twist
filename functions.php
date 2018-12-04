<?php

use Twist\Model\Post\Query;
use Twist\Model\Site\Site;
use Twist\Service\CoreServiceProvider;
use Twist\Twist;

Twist::theme()->services(new CoreServiceProvider())->options([
	'context'    => [
		'shared' => [
			'site' => Site::class,
		],
		'view'   => [
			'posts' => Query::class,
		],
	],
	'service' => [
		'head_cleaner'        => [
			'enable'    => true,
			'generator' => true,
			'edit'      => true,
			'emoji'     => true,
		],
		'content_cleaner'     => [
			'enable'     => true,
			'attributes' => [],
			'styles'     => [],
			'comments'   => true,
		],
		'thumbnail_generator' => [
			'enable' => true,
			'videos' => true,
		],
		'lazy_load'           => [
			'enable'    => true,
			'threshold' => 200,
		],
	],
])->styles([
	[
		'id'     => 'twist',
		'load'   => 'scripts/app.css',
		'parent' => true,
	],
])->scripts([
	[
		'id'     => 'twist',
		'load'   => 'scripts/app.js',
		'deps' => ['jquery'],
		'parent' => true,
	],
	[
		'id'     => 'jquery',
		'load'   => 'scripts/jquery.js',
		'parent' => true,
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
	'twitter'  => __('Twitter', 'twist'),
	'facebook' => __('Facebook', 'twist'),
	'linkedin' => __('LinkedIn', 'twist'),
])->thumbnail(850, 510, true);
