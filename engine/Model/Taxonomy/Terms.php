<?php

namespace Twist\Model\Taxonomy;

use Twist\Model\Collection;

/**
 * Class Terms
 *
 * @package Twist\Model\Taxonomy
 *
 * @method Term|null parent()
 * @method Term|null get(int $id)
 * @method Term[] all()
 * @method Term|null first(callable $callback = null, $default = null)
 * @method Term|null last(callable $callback = null, $default = null)
 * @method Terms merge($models)
 * @method Terms only(array $ids)
 * @method Terms except(array $ids)
 * @method Terms slice(int $offset, int $length = null)
 * @method Terms take(int $limit)
 * @method Terms filter(callable $callback)
 * @method Terms where(string $method, string $operator, $value = null)
 * @method Terms sort(string $method = null, bool $descending = false, int $options = SORT_REGULAR)
 */
class Terms extends Collection
{

}
