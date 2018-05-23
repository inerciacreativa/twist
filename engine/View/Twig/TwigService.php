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
	 * @var array
	 */
	protected $data = [];

	/**
	 * @inheritdoc
	 */
	public function boot(): void
	{
		$this->loader      = new \Twig_Loader_Filesystem($this->config->get('view.paths', []));
		$this->environment = new \Twig_Environment($this->loader, [
			'cache'       => $this->config->get('view.cache', false),
			'debug'       => $this->config->get('view.debug', false),
			'auto_reload' => true,
		]);

		$this->environment->addExtension(new TwigExtension());
		$this->environment->addExtension(new \Twig_Extension_StringLoader());

		if ($this->config->get('view.debug')) {
			$this->environment->addExtension(new \Twig_Extension_Debug());
		}
	}

	/**
	 * @inheritdoc
	 */
	public function addGlobalData(string $name, $value): ViewInterface
	{
		$this->environment->addGlobal($name, $value);

		return $this;
	}

	/**
	 * @inheritdoc
	 */
	public function getGlobalData(): array
	{
		return $this->environment->getGlobals();
	}

	/**
	 * @inheritdoc
	 */
	public function addData(string $name, $value): ViewInterface
	{
		$this->data[$name] = $value;

		return $this;
	}

	/**
	 * @inheritdoc
	 */
	public function getData(): array
	{
		return $this->data;
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
		$this->start();

		return $this->environment->render($template, array_merge($this->data, $data));
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
		$this->start();

		$this->environment->display($template, array_merge($this->data, $data));
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