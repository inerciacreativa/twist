<?php

namespace Twist\Model\Base;

/**
 * Class Model
 *
 * @package Twist\Model\Base
 */
abstract class Model implements ModelInterface
{

	use HasParent;
	use HasChildren;

}