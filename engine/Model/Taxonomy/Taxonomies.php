<?php

namespace Twist\Model\Taxonomy;

use Twist\App\AppException;
use Twist\Model\Enumerable;

/**
 * Class Taxonomies
 *
 * @package Twist\Model\Taxonomy
 */
class Taxonomies extends Enumerable
{

	/**
	 * @var Taxonomies
	 */
	private static $instance;

	/**
	 * @return Taxonomies
	 */
	public static function getInstance(): Taxonomies
	{
		if (static::$instance === null) {
			static::$instance = new static();
		}

		return static::$instance;
	}

	/**
	 * Taxonomies constructor.
	 */
	private function __construct()
	{
		$this->fill(array_flip(get_taxonomies()));
	}

	/**
	 * @inheritdoc
	 */
	public function get(string $key, $default = null): ?Taxonomy
	{
		if (!$this->has($key)) {
			return null;
		}

		$taxonomy = parent::get($key);

		if (!($taxonomy instanceof Taxonomy)) {
			try {
				$taxonomy = new Taxonomy($key);

				$this->set($key, $taxonomy);
			} catch (AppException $exception) {
				$taxonomy = null;
			}
		}

		return $taxonomy;
	}

	/**
	 * @inheritdoc
	 */
	public function getValues(): array
	{
		foreach ($this->getNames() as $name) {
			$this->get($name);
		}

		return parent::getValues();
	}

	/**
	 * Prevent the instance from being cloned.
	 */
	private function __clone()
	{
	}

	/**
	 * Prevent from being unserialized.
	 */
	private function __wakeup()
	{
	}

}
