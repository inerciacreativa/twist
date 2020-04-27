<?php

namespace Twist\App;

use Twist\Library\Data\Repository;
use Twist\Library\Support\Data;
use Twist\Twist;
use Twist\View\Twig\TwigViewService;

/**
 * Class Config
 *
 * @package Twist\App
 */
class Config extends Repository
{

	/**
	 * Config constructor.
	 */
	public function __construct()
	{
		parent::__construct([
			'dir'  => [
				'home'       => defined('WP_ROOT') ? WP_ROOT : ABSPATH,
				'stylesheet' => get_stylesheet_directory(),
				'template'   => get_template_directory(),
				'upload'     => wp_upload_dir(null, false)['basedir'],
			],
			'uri'  => [
				'home'       => home_url(),
				'stylesheet' => get_stylesheet_directory_uri(),
				'template'   => get_template_directory_uri(),
			],
			'view' => [
				'debug'     => Twist::isDebug(),
				'service'   => TwigViewService::id(),
				'namespace' => TwigViewService::MAIN_NAMESPACE,
				'folder'    => '/templates',
				'context'   => [],
			],
		]);
	}

	/**
	 * Make sure that returns a value (and not a closure).
	 *
	 * @inheritdoc
	 */
	public function get(string $key, $default = null)
	{
		return Data::value(parent::get($key, $default));
	}

}
