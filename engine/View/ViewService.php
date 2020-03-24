<?php

namespace Twist\View;

use Twist\App\Action;
use Twist\App\Config;
use Twist\Service\Service;

/**
 * Class View
 *
 * @package Twist\View
 */
abstract class ViewService extends Service implements ViewInterface
{

	/**
	 * @var Context
	 */
	protected $context;

	/**
	 * View constructor.
	 *
	 * @param Config  $config
	 * @param Context $context
	 */
	public function __construct(Config $config, Context $context)
	{
		parent::__construct($config, Action::INIT);

		$this->context = $context;
	}

	/**
	 * @inheritDoc
	 */
	public function boot(): bool
	{
		return $this->config->get('view.service') === static::id();
	}

	/**
	 * @inheritDoc
	 */
	protected function init(): void
	{
		$this->config->set([
			'view' => [
				'cache' => $this->config->get('view.debug') ? false : $this->config->get('dir.upload') . '/view_cache',
				'paths' => $this->getPaths($this->config->get('dir.stylesheet'), $this->config->get('dir.template'), $this->config->get('view.folder'), $this->config->get('view.namespace')),
			],
		]);
	}

	/**
	 * @param string      $stylesheet
	 * @param string      $template
	 * @param string      $folder
	 * @param string|null $namespace
	 *
	 * @return array
	 */
	protected function getPaths(string $stylesheet, string $template, string $folder, string $namespace = null): array
	{
		$paths = [];

		if ($namespace) {
			$paths[] = [$namespace, $stylesheet];
		}

		$paths[] = [basename($stylesheet), $stylesheet];

		if ($template !== $stylesheet) {
			$paths[] = [basename($template), $template];
		}

		return array_map(static function (array $path) use ($folder) {
			return [
				'namespace' => $path[0],
				'path'      => $path[1] . $folder,
			];
		}, $paths);
	}

}
