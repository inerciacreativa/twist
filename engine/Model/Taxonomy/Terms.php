<?php

namespace Twist\Model\Taxonomy;

use Twist\Library\Model\Collection;

/**
 * Class Terms
 *
 * @package Twist\Model\Taxonomy
 *
 * @method Term|null parent()
 * @method Term get(int $id)
 * @method Term|null first(callable $callback = null)
 * @method Term|null last(callable $callback = null)
 * @method Terms only(array $ids)
 * @method Terms except(array $ids)
 * @method Terms slice(int $offset, int $length = null)
 * @method Terms take(int $limit)
 */
class Terms extends Collection
{

}