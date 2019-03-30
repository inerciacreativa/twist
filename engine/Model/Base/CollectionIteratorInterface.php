<?php

namespace Twist\Model\Base;

use ArrayAccess;
use Countable;
use SeekableIterator;

/**
 * Interface CollectionIteratorInterface
 *
 * @package Twist\Model\Base
 */
interface CollectionIteratorInterface extends ArrayAccess, Countable, SeekableIterator
{

}