<?php

namespace Twist\Model\Link;

use Twist\Model\Collection;

/**
 * Class Links
 *
 * @package Twist\Model\Link
 *
 * @method Link|null parent()
 * @method Link|null get(int $id)
 * @method Link[] all()
 * @method Link|null first(callable $callback = null, $default = null)
 * @method Link|null last(callable $callback = null, $default = null)
 * @method Links merge($models)
 * @method Links only(array $ids)
 * @method Links except(array $ids)
 * @method Links slice(int $offset, int $length = null)
 * @method Links take(int $limit)
 * @method Links filter(callable $callback)
 * @method Links where(string $method, string $operator, $value = null)
 * @method Links sort(string $method = null, bool $descending = false, int $options = SORT_REGULAR)
 * @method Links shuffle()
 */
class Links extends Collection
{

}
