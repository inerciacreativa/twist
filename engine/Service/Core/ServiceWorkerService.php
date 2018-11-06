<?php

namespace Twist\Service\Core;

use Twist\App\App;
use Twist\App\Asset;
use Twist\Library\Hook\Hook;
use Twist\Service\Service;

/**
 * Class ServiceWorkerService
 *
 * @package Twist\Service
 */
class ServiceWorkerService extends Service
{

	/**
	 * @var Asset
	 */
	protected $asset;

	/**
	 * ServiceWorkerService constructor.
	 *
	 * @param App   $app
	 * @param Asset $asset
	 */
	public function __construct(App $app, Asset $asset)
	{
		$this->asset = $asset;

		parent::__construct($app);
	}

	/**
	 * @inheritdoc
	 */
	public function boot(): void
	{
		if (is_admin()) {
			return;
		}

		$this->hook()->off('wp_footer', 'addScript', Hook::AFTER);

		if ($this->config('enable')) {
			$this->start();
		}
	}

	/**
	 * Injects the script at the end of the page.
	 */
	public function addScript(): void
	{

		$script = str_replace(
			[
				'{{script}}'
			],
			[
				$this->asset->url($this->config('script'))
			],
			file_get_contents(__DIR__ . '/ServiceWorkerScript.js')
		);

		echo <<<SCRIPT
	<script>
$script
	</script>
SCRIPT;

	}

}