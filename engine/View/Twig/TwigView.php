<?php

namespace Twist\View\Twig;

use Twist\View\View;

/**
 * Class TwigService
 *
 * @package Twist\View\Twig
 */
class TwigView extends View
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
	protected function init(): void
	{
		$this->loader      = new \Twig_Loader_Filesystem($this->config->get('view.paths', []));
		$this->environment = new \Twig_Environment($this->loader, [
			'cache'       => $this->config->get('view.cache', false),
			'debug'       => $this->config->get('app.debug', false),
			'auto_reload' => true,
		]);

		$this->environment->addExtension(new TwigExtension());

		if ($this->config->get('app.debug')) {
			$this->environment->addExtension(new \Twig_Extension_Debug());
		}
	}

	/**
	 * @param array $context
	 *
	 * @return array
	 *
	 * @throws \Exception
	 */
	protected function resolve(array $context): array
	{
		foreach ($this->context->shared() as $name => $value) {
			$this->environment->addGlobal($name, $value);
		}

		return $this->context->resolve($context);
	}

	/**
	 * @inheritdoc
	 *
	 * @throws \Twig_Error_Loader  When the template cannot be found
	 * @throws \Twig_Error_Syntax  When an error occurred during compilation
	 * @throws \Twig_Error_Runtime When an error occurred during rendering
	 * @throws \Exception
	 */
	public function render(string $template, array $context = []): string
	{
		return $this->environment->render($template, $this->resolve($context));
	}

	/**
	 * @inheritdoc
	 *
	 * @throws \Twig_Error_Loader  When the template cannot be found
	 * @throws \Twig_Error_Syntax  When an error occurred during compilation
	 * @throws \Twig_Error_Runtime When an error occurred during rendering
	 * @throws \Exception
	 */
	public function display(string $template, array $context = []): void
	{
		$this->environment->display($template, $this->resolve($context));
	}

	/**
	 * @param string $path
	 *
	 * @throws \Twig_Error_Loader
	 */
	public function path(string $path): void
	{
		$this->loader->addPath($path);
	}

}