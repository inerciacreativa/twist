<?php

namespace Twist\View\Twig;

use Twist\View\ViewService;

/**
 * Class TwigService
 *
 * @package Twist\View\Twig
 */
class TwigService extends ViewService
{

	/**
	 * @var \Twig_Environment
	 */
	private $service;

	/**
	 * @inheritdoc
	 */
	public function boot()
	{
		$paths   = $this->config->get('view.paths', []);
		$options = [
			'cache' => $this->config->get('view.cache', false),
			'debug' => $this->config->get('app.debug', false),
		];

		$loader  = new \Twig_Loader_Filesystem($paths);
		$service = new \Twig_Environment($loader, $options);
		$service->addExtension(new TwigExtension());
		$service->addExtension(new \Twig_Extension_StringLoader());

		if ($this->config->get('app.debug')) {
			$service->addExtension(new \Twig_Extension_Debug());
		}

		$this->service = $service;

		parent::boot();
	}

	/**
	 * @inheritdoc
	 */
	public function data(string $name, $value)
	{
		$this->service->addGlobal($name, $value);
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
		return $this->service->render($template, $data);
	}

	/**
	 * @inheritdoc
	 *
	 * @throws \Twig_Error_Loader  When the template cannot be found
	 * @throws \Twig_Error_Syntax  When an error occurred during compilation
	 * @throws \Twig_Error_Runtime When an error occurred during rendering
	 */
	public function display(string $template, array $data = [])
	{
		$this->service->display($template, $data);
	}

}