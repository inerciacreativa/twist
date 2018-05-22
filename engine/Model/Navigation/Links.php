<?php

namespace Twist\Model\Navigation;

use Twist\Library\Model\Collection;

/**
 * Class Links
 *
 * @package Twist\Model\Navigation
 *
 * @method Link|null parent()
 * @method Link get(int $id)
 * @method Link|null first(callable $callback = null)
 * @method Link|null last(callable $callback = null)
 * @method Links only(array $ids)
 * @method Links except(array $ids)
 * @method Links slice(int $offset, int $length = null)
 * @method Links take(int $limit)
 */
class Links extends Collection
{

}