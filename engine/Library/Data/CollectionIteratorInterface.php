<?php

namespace Twist\Library\Data;

/**
 * Class CollectionIterator
 *
 * @package Twist\Library\Data
 */
interface CollectionIteratorInterface extends \SeekableIterator, \ArrayAccess, \Serializable, \Countable
{

	public function append($value);

	public function getArrayCopy();

	public function getFlags();

	public function setFlags($flags);

	public function asort();

	public function ksort();

	public function uasort($cmp_function);

	public function uksort($cmp_function);

	public function natsort();

	public function natcasesort();

}