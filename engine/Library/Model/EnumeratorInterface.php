<?php

namespace Twist\Library\Model;

interface EnumeratorInterface extends \IteratorAggregate, \Countable
{

	public function model(): ModelInterface;

}