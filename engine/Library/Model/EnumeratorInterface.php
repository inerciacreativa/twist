<?php

namespace Twist\Library\Model;

interface EnumeratorInterface extends \IteratorAggregate, \Countable
{

	public function model(): ModelInterface;

	public function set($id, $value): void;

	public function get($id);

	public function has($id): bool;

	public function unset($id): void;

}