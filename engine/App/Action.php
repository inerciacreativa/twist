<?php

namespace Twist\App;

/**
 * Class Action
 *
 * @package Twist\App
 */
class Action
{
	public const BOOT = 'after_setup_theme';

	public const INIT = 'init';

	public const REQUEST = 'parse_request';

	public const QUERY = 'parse_query';

	public const SETUP = 'wp';

	public const HEAD = 'wp_head';

	public const FOOT = 'wp_footer';

	public const SHUTDOWN = 'shutdown';

}
