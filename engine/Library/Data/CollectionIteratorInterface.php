<?php

namespace Twist\Library\Data;

use ArrayAccess;
use Countable;
use SeekableIterator;
use Serializable;

/**
 * Class CollectionIterator
 *
 * @package Twist\Library\Data
 */
interface CollectionIteratorInterface extends ArrayAccess, Countable, SeekableIterator, Serializable
{

}