<?php

namespace Twist\Asset;

use Twist\Asset\Queue\Scripts;
use Twist\Asset\Queue\Styles;
use Twist\Model\Post\PostsQuery;

/**
 * Class Queue
 *
 * @package Twist\Asset
 */
class Queue
{

	/**
	 * @var Styles
	 */
	private $styles;

	/**
	 * @var Scripts
	 */
	private $scripts;

	/**
	 * Queue constructor.
	 *
	 * @param Resources $resources
	 */
	public function __construct(Resources $resources)
	{
		$this->styles  = new Styles($resources);
		$this->scripts = new Scripts($resources);
	}

	/**
	 * [
	 *    'id'     (string)
	 *    'load'   (string|bool)
	 *    'parent' (bool)
	 *    'deps'   (array)
	 *    'footer' (bool)
	 *    'attr'   (string)
	 * ]
	 *
	 * @param array $styles
	 * @param bool  $parent
	 *
	 * @return $this
	 */
	public function styles(array $styles, bool $parent = false): self
	{
		if (!PostsQuery::is_admin()) {
			$this->styles->add($styles, $parent);
		}

		return $this;
	}

	/**
	 * [
	 *    'id'     (string)
	 *    'load'   (string|bool)
	 *    'parent' (bool)
	 *    'deps'   (array)
	 *    'media'  (string)
	 * ]
	 *
	 * @param array $scripts
	 * @param bool  $parent
	 *
	 * @return $this
	 */
	public function scripts(array $scripts, bool $parent = false): self
	{
		if (!PostsQuery::is_admin()) {
			$this->scripts->add($scripts, $parent);
		}

		return $this;
	}

	/**
	 * @param string          $id
	 * @param string|callable $script
	 *
	 * @return $this
	 */
	public function inline(string $id, $script): self
	{
		if (!PostsQuery::is_admin()) {
			$this->scripts->inline($id, $script);
		}

		return $this;
	}

}
