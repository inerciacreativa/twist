<?php

namespace Twist\Model\Pagination;

use Twist\Model\Link\Links;

/**
 * Class PaginationLinks
 *
 * @package Twist\Model\Pagination
 *
 * @method null parent()
 * @method PaginationLinkInterface|null get(int $id)
 * @method PaginationLinkInterface[] all()
 * @method PaginationLinkInterface|null first(callable $callback = null, $default = null)
 * @method PaginationLinkInterface|null last(callable $callback = null, $default = null)
 * @method PaginationLinks merge($models)
 * @method PaginationLinks only(array $ids)
 * @method PaginationLinks except(array $ids)
 * @method PaginationLinks slice(int $offset, int $length = null)
 * @method PaginationLinks take(int $limit)
 * @method PaginationLinks filter(callable $callback)
 * @method PaginationLinks where(string $method, string $operator, $value = null)
 * @method PaginationLinks sort(string $method = null, bool $descending = false, int $options = SORT_REGULAR)
 * @method PaginationLinks shuffle()
 */
class PaginationLinks extends Links
{

}
