<?php

namespace Twist\View\Twig;

use Throwable;
use Twig\Environment;
use Twig\Extension\DebugExtension;
use Twig\Loader\FilesystemLoader;
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
	 *
	 */
	public const MAIN_NAMESPACE = FilesystemLoader::MAIN_NAMESPACE;

	/**
	 * @var FilesystemLoader
	 */
	protected $loader;

	/**
	 * @var Environment
	 */
	protected $environment;

	/**
	 * @inheritdoc
	 */
	protected function init(): void
	{
		$this->loader = new FilesystemLoader();

		foreach ($this->config->get('view.paths') as $path) {
			$this->loader->addPath($path['path'], $path['namespace']);
		}

		$this->environment = new Environment($this->loader, [
			'cache'       => $this->config->get('view.cache', false),
			'debug'       => $this->config->get('app.debug', false),
			'auto_reload' => true,
		]);

		$this->environment->addExtension(new TwigExtension());

		if ($this->config->get('app.debug')) {
			$this->environment->addExtension(new DebugExtension());
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
	 * @param string $namespace
	 *
	 * @throws AppException
	 */
	public function path(string $path, string $namespace = self::MAIN_NAMESPACE): void
	{
		try {
			$this->loader->addPath($path, $namespace);
		} catch (Throwable $exception) {
			throw new AppException($exception);
		}
	}

}
