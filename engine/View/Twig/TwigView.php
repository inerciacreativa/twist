<?php

namespace Twist\View\Twig;

use Throwable;
use Twig_Environment;
use Twig_Extension_Debug;
use Twig_Loader_Filesystem;
use Twist\App\AppException;
use Twist\View\View;

/**
 * Class TwigService
 *
 * @package Twist\View\Twig
 */
class TwigView extends View
{

	/**
	 * @var Twig_Loader_Filesystem
	 */
	protected $loader;

	/**
	 * @var Twig_Environment
	 */
	protected $environment;

	/**
	 * @inheritdoc
	 */
	protected function init(): void
	{
		$this->loader      = new Twig_Loader_Filesystem($this->config->get('view.paths', []));
		$this->environment = new Twig_Environment($this->loader, [
			'cache'       => $this->config->get('view.cache', false),
			'debug'       => $this->config->get('app.debug', false),
			'auto_reload' => true,
		]);

		$this->environment->addExtension(new TwigExtension());

		if ($this->config->get('app.debug')) {
			$this->environment->addExtension(new Twig_Extension_Debug());
		}
	}

	/**
	 * @param array $context
	 *
	 * @return array
	 *
	 * @throws AppException
	 */
	protected function resolve(array $context): array
	{
		try {
			foreach ($this->context->shared() as $name => $value) {
				$this->environment->addGlobal($name, $value);
			}
		} catch (Throwable $exception) {
			throw new AppException($exception);
		}

		return $this->context->resolve($context);
	}

	/**
	 * @inheritdoc
	 *
	 * @throws AppException
	 */
	public function render(string $template, array $context = []): string
	{
		try {
			return $this->environment->render($template, $this->resolve($context));
		} catch (Throwable $exception) {
			throw new AppException($exception);
		}
	}

	/**
	 * @inheritdoc
	 *
	 * @throws AppException
	 */
	public function display(string $template, array $context = []): void
	{
		try {
			$this->environment->display($template, $this->resolve($context));
		} catch (Throwable $exception) {
			throw new AppException($exception);
		}
	}

	/**
	 * @param string $path
	 *
	 * @throws AppException
	 */
	public function path(string $path): void
	{
		try {
			$this->loader->addPath($path);
		} catch (Throwable $exception) {
			throw new AppException($exception);
		}
	}

}