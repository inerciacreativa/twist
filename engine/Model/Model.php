<?php

namespace Twist\Model;

/**
 * Class Model
 *
 * @package Twist\Model
 */
abstract class Model implements ModelInterface
{

	use HasParent;
	use HasChildren;

}
