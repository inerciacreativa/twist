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
 * @method NavigationLinks merge($models)
 * @method NavigationLinks only(array $ids)
 * @method NavigationLinks except(array $ids)
 * @method NavigationLinks slice(int $offset, int $length = null)
 * @method NavigationLinks take(int $limit)
 * @method NavigationLinks filter(callable $callback)
 * @method NavigationLinks where(string $method, string $operator, $value = null)
 * @method NavigationLinks sort(string $method = null, bool $descending = false, int $options = SORT_REGULAR)
 * @method NavigationLinks shuffle()
 */
class NavigationLinks extends Links
{

}
