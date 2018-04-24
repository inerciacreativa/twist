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
	public function boot()
	{
		$loader      = new \Twig_Loader_Filesystem($this->config->get('view.paths', []));
		$environment = new \Twig_Environment($loader, [
			'cache' => $this->config->get('view.cache', false),
			'debug' => $this->config->get('app.debug', false),
		]);

		$environment->addExtension(new TwigExtension());
		$environment->addExtension(new \Twig_Extension_StringLoader());

		if ($this->config->get('app.debug')) {
			$environment->addExtension(new \Twig_Extension_Debug());
		}

		$this->environment = $environment;

		parent::boot();
	}

	/**
	 * @inheritdoc
	 */
	public function set(string $name, $value): ViewInterface
	{
		$this->environment->addGlobal($name, $value);

		return $this;
	}

	/**
	 * @inheritdoc
	 */
	public function add(string $name, $value): ViewInterface
	{
		$this->data[$name] = $value;

		return $this;
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
		return $this->environment->render($template, array_merge($this->data, $data));
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
		$this->environment->display($template, array_merge($this->data, $data));
	}

}