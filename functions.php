<?php

use Twist\Asset;
use Twist\Model\Post\PostsQuery;
use Twist\Model\Site\Assets;
use Twist\Model\Site\Site;
use Twist\Service\Core\ContentCleanerService;
use Twist\Service\Core\HeadCleanerService;
use Twist\Service\Core\ThumbnailGeneratorService;
use Twist\Service\CoreServiceProvider;
use Twist\Twist;
use Twist\View;

Twist::provider(new CoreServiceProvider());

Twist::theme()->parent(static function () {

	Twist::theme()->options([
		'service'     => [
			HeadCleanerService::id()        => [
				'enable'    => true,
				'generator' => true,
				'edit'      => true,
				'emoji'     => true,
			],
			ContentCleanerService::id()     => [
				'enable'     => true,
				'attributes' => [],
				'styles'     => [],
				'comments'   => true,
			],
			ThumbnailGeneratorService::id() => [
				'enable'  => true,
				'modules' => [],
			],
		],
		'credentials' => [
			'youtube' => ['key' => ''],
			'vimeo'   => [
				'id'     => '',
				'secret' => '',
			],
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

	Asset::manifest('assets', 'assets.json', true);

	Asset::scripts([
		[
			'id'   => 'twist',
			'load' => 'scripts/app.js',
			'deps' => ['jquery'],
		],
		[
			'id'   => 'jquery',
			'load' => 'scripts/jquery.js',
		],
		[
			'id'   => 'comment-reply',
			'load' => static function () {
				return PostsQuery::main()->is_single() && ($post = PostsQuery::main()
																			 ->posts()
																			 ->first()) && $post->can_be_commented();
			},
		],
	], true);

	Asset::styles([
		'id'   => 'twist',
		'load' => 'styles/app.css',
	], true);

	View::context()->set([
		'posts'  => PostsQuery::class,
		'site'   => Site::class,
		'assets' => Assets::class,
	]);

});
