<?php

namespace Twist\Model\Meta;

use Twist\Library\Model\Enumerator;
use Twist\Library\Model\ModelInterface;

/**
 * Class Meta
 *
 * @package Twist\Model\Meta
 */
class Meta extends Enumerator
{

	/**
	 * Meta constructor.
	 *
	 * @param ModelInterface $model
	 * @param string         $type
	 */
	public function __construct(ModelInterface $model, string $type)
	{
		parent::__construct($model, get_metadata($type, $model->id()));
	}

	/**
	 * @inheritdoc
	 */
	public function get($id)
	{
		$value = parent::get($id);

		if (!\is_array($value)) {
			return null;
		}

		return \count($value) === 1 ? maybe_unserialize($value[0]) : array_map('maybe_unserialize', $value);
	}

}