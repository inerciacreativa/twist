<?php

namespace Twist\Model;

use ArrayAccess;
use Countable;
use SeekableIterator;

/**
 * Interface CollectionIteratorInterface
 *
 * @package Twist\Model
 */
interface CollectionIteratorInterface extends ArrayAccess, Countable, SeekableIterator
{

}
