<?php

namespace Twist;

use Twist\App\ServiceProvider;
use Twist\Model\Site\Site;
use Twist\View\Twig\TwigProvider;

/**
 * Sets default configuration
 */
add_action('after_setup_theme', function () {

	app()->provider(new ServiceProvider());

	config()->fill([
		'app.debug'      => defined('WP_DEBUG') && WP_DEBUG,
		'app.boot'       => [
			'filter.emoji',
			'filter.rest',
			'filter.embed',
			'filter.content',
		],
		'dir.stylesheet' => STYLESHEETPATH,
		'dir.template'   => TEMPLATEPATH,
		'dir.upload'     => wp_upload_dir()['basedir'],
		'uri.home'       => home_url(),
		'uri.stylesheet' => get_stylesheet_directory_uri(),
		'uri.template'   => get_template_directory_uri(),
		'filter.content' => [
			'attributes' => [],
			'styles'     => ['color'],
			'comments'   => true,
		],
	]);

	app()->provider(new TwigProvider());

	config()->fill([
		'view.service' => 'twig.service',
		'view.cache'   => config('app.debug') ? false : config('dir.upload') . '/view_cache',
		'view.theme'   => '',
		'view.paths'   => function () {
			if ($theme = config('view.theme')) {
				$theme = "/$theme";
			}

			return array_unique(array_map(function ($path) use ($theme) {
				return file_exists("$path/views$theme") ? "$path/views$theme" : "$path/views";
			}, [STYLESHEETPATH, TEMPLATEPATH]));
		},
		'view.data'    => [
			'site' => Site::class,
		],
	]);

}, 0);

/**
 * Sets default WordPress features
 */
add_action('after_setup_theme', function () {
	add_theme_support('title-tag');
	add_theme_support('post-thumbnails');
	add_theme_support('customize-selective-refresh-widgets');
	add_theme_support('html5', [
		'gallery',
		'caption',
	]);
}, 1);

/**
 * Initialize
 */
add_action('after_setup_theme', function () {

	app()->boot(config('app.boot', []));

}, 99);
