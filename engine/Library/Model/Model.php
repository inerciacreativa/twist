<?php

namespace Twist\Library\Model;

/**
 * Class Model
 *
 * @package Twist\Library\Model
 */
abstract class Model implements ModelInterface
{

	use HasParent;
	use HasChildren;

}