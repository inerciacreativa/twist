<?php

namespace Twist\Library\Model;

/**
 * Interface ModelInterface
 *
 * @package Twist\Library\Model
 */
interface ModelInterface extends HasParentInterface, HasChildrenInterface
{

	public function id(): int;

}