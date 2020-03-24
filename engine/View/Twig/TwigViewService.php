<?php

namespace Twist\View\Twig;

use Throwable;
use Twig\Environment;
use Twig\Extension\DebugExtension;
use Twig\Loader\FilesystemLoader;
use Twist\App\AppException;
use Twist\View\ViewService;

/**
 * Class TwigService
 *
 * @package Twist\View\Twig
 */
class TwigViewService extends ViewService
{

	public const MAIN_NAMESPACE = FilesystemLoader::MAIN_NAMESPACE;

	/**
	 * @var FilesystemLoader
	 */
	private $loader;

	/**
	 * @var Environment
	 */
	private $environment;

	/**
	 * @inheritDoc
	 *
	 * @throws AppException
	 */
	protected function init(): void
	{
		parent::init();

		$this->loader      = $this->getLoader();
		$this->environment = $this->getEnvironment();

		$this->addPaths();
		$this->addExtensions();
	}

	/**
	 * @return FilesystemLoader
	 */
	protected function getLoader(): FilesystemLoader
	{
		return new FilesystemLoader();
	}

	/**
	 * @return Environment
	 */
	protected function getEnvironment(): Environment
	{
		return new Environment($this->loader, [
			'cache'       => $this->config->get('view.cache', false),
			'debug'       => $this->config->get('view.debug', false),
			'auto_reload' => true,
		]);
	}

	/**
	 * @throws AppException
	 */
	protected function addPaths(): void
	{
		foreach ($this->config->get('view.paths') as $path) {
			$this->path($path['path'], $path['namespace']);
		}
	}

	/**
	 *
	 */
	protected function addExtensions(): void
	{
		$this->environment->addExtension(new TwigExtension());

		if ($this->config->get('app.debug')) {
			$this->environment->addExtension(new DebugExtension());
		}
	}

	/**
	 * @throws AppException
	 */
	protected function addGlobals(): void
	{
		try {
			foreach ($this->context->all() as $name => $value) {
				$this->environment->addGlobal($name, $value);
			}
		} catch (Throwable $exception) {
			throw new AppException($exception);
		}
	}

	/**
	 * @param array $context
	 *
	 * @return array
	 *
	 * @throws AppException
	 */
	protected function getContext(array $context): array
	{
		$this->addGlobals();

		return $this->context->resolve($context);
	}

	/**
	 * @inheritDoc
	 *
	 * @throws AppException
	 */
	public function render(string $template, array $context = []): string
	{
		try {
			return $this->environment->render($template, $this->getContext($context));
		} catch (Throwable $exception) {
			throw new AppException($exception);
		}
	}

	/**
	 * @inheritDoc
	 *
	 * @throws AppException
	 */
	public function display(string $template, array $context = []): void
	{
		try {
			$this->environment->display($template, $this->getContext($context));
		} catch (Throwable $exception) {
			throw new AppException($exception);
		}
	}

	/**
	 * @inheritDoc
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
