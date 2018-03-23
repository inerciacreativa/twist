<?php

namespace Twist;

use Twist\Service\ServiceProvider;
use Twist\Model\Site\Site;
use Twist\View\Twig\TwigService;
use Twist\View\ViewProvider;

/**
 * Sets default services and configuration.
 */
add_action('after_setup_theme', function () {

	app()->provider(new ServiceProvider());
	app()->provider(new ViewProvider());

	config()->fill([
		'app.debug'      => \defined('WP_DEBUG') && WP_DEBUG,
		'dir.stylesheet' => STYLESHEETPATH,
		'dir.template'   => TEMPLATEPATH,
		'dir.upload'     => wp_upload_dir()['basedir'],
		'uri.home'       => home_url(),
		'uri.stylesheet' => get_stylesheet_directory_uri(),
		'uri.template'   => get_template_directory_uri(),
	]);

	config()->fill([
		'view.service' => TwigService::id(),
		'view.theme'   => '',
		'view.data'    => [
			'site' => Site::class,
		],
		'view.jquery'  => '//ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js',
		'view.cache'   => config('app.debug') ? false : config('dir.upload') . '/view_cache',
		'view.paths'   => function () {
			if ($theme = config('view.theme')) {
				$theme = "/$theme";
			}

			return array_unique(array_map(function ($path) use ($theme) {
				return file_exists("$path/views$theme") ? "$path/views$theme" : "$path/views";
			}, [STYLESHEETPATH, TEMPLATEPATH]));
		},
	]);

	config()->fill([
		'api.rest'            => false,
		'api.oembed'          => false,
		'filter.relative_url' => true,
		'filter.emoji'        => true,
		'filter.content'      => [
			'attributes' => [],
			'styles'     => [],
			'comments'   => true,
		],
	]);

}, PHP_INT_MIN);

/**
 * Initialize the application.
 */
add_action('after_setup_theme', function () {

	app()->boot();

}, PHP_INT_MAX);
