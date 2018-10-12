<?php

namespace Twist\View\Twig;

use Twist\View\ViewInterface;
use Twist\View\ViewService;

/**
 * Class TwigService
 *
 * @package Twist\View\Twig
 */
class TwigService extends ViewService
{

	/**
	 * @var \Twig_Loader_Filesystem
	 */
	protected $loader;

	/**
	 * @var \Twig_Environment
	 */
	protected $environment;

	/**
	 * @inheritdoc
	 */
	public function boot(): void
	{
		$this->loader      = new \Twig_Loader_Filesystem($this->config->get('view.paths', []));
		$this->environment = new \Twig_Environment($this->loader, [
			'cache'       => $this->config->get('view.cache', false),
			'debug'       => $this->config->get('app.debug', false),
			'auto_reload' => true,
		]);

		$this->environment->addExtension(new TwigExtension());
		$this->environment->addExtension(new \Twig_Extension_StringLoader());

		if ($this->config->get('app.debug')) {
			$this->environment->addExtension(new \Twig_Extension_Debug());
		}

		foreach ((array) $this->config->get('data.global', []) as $name => $value) {
			$this->environment->addGlobal($name, $this->resolveData($value));
		}

		foreach ((array) $this->config->get('data.view', []) as $name => $value) {
			$this->addData($name, $value);
		}
	}

	/**
	 * @inheritdoc
	 *
	 * @throws \Twig_Error_Loader  When the template cannot be found
	 * @throws \Twig_Error_Syntax  When an error occurred during compilation
	 * @throws \Twig_Error_Runtime When an error occurred during rendering
	 */
	public function render(string $template, array $data = []): string
	{
		return $this->environment->render($template, $this->mergeData($data));
	}

	/**
	 * @inheritdoc
	 *
	 * @throws \Twig_Error_Loader  When the template cannot be found
	 * @throws \Twig_Error_Syntax  When an error occurred during compilation
	 * @throws \Twig_Error_Runtime When an error occurred during rendering
	 */
	public function display(string $template, array $data = []): void
	{
		$this->environment->display($template, $this->mergeData($data));
	}

	/**
	 * @inheritdoc
	 */
	public function getPaths(): array
	{
		return $this->loader->getPaths();
	}

	/**
	 * @inheritdoc
	 *
	 * @return \Twist\View\ViewInterface
	 * @throws \Twig_Error_Loader
	 */
	public function addPath(string $path): ViewInterface
	{
		$this->loader->addPath($path);

		return $this;
	}

}