<?php

namespace Twist\Model\Navigation;

use Twist\Model\Link\Links;

/**
 * Class NavigationLinks
 *
 * @package Twist\Model\Navigation
 *
 * @method NavigationLinkInterface|null parent()
 * @method NavigationLinkInterface|null get(int $id)
 * @method NavigationLinkInterface[] all()
 * @method NavigationLinkInterface|null first(callable $callback = null, $default = null)
 * @method NavigationLinkInterface|null last(callable $callback = null, $default = null)
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
class NavigationLinks extends Links
{

}
