<?php

namespace Twist\Model\User;

use Twist\Library\Html\Tag;
use Twist\Model\Pagination\Pagination as BasePagination;

/**
 * Class Pagination
 *
 * @package Twist\Model\User
 */
class Pagination extends BasePagination
{

	/**
	 * @var Query
	 */
	private $query;

	/**
	 * Pagination constructor.
	 *
	 * @param Query $query
	 */
	public function __construct(Query $query)
	{
		$this->query = $query;
	}

	/**
	 * @inheritDoc
	 */
	protected function getPrevNextLinks(): array
	{
		if (!$this->query->has_pagination()) {
			return [];
		}

		return array_filter([
			$this->getPrevLink(_x('Previous', 'previous set of users', 'twist')),
			$this->getNextLink(_x('Next', 'next set of users', 'twist')),
		]);
	}

	/**
	 * @inheritDoc
	 */
	protected function getPaginatedLinks(array $arguments): array
	{
		if (!$this->query->has_pagination()) {
			return [];
		}

		$arguments = array_merge([
			'total'     => $this->query->total_pages(),
			'current'   => $this->query->current_page(),
			'mid_size'  => 1,
			'prev_text' => _x('Previous', 'previous set of users', 'twist'),
			'next_text' => _x('Next', 'next set of users', 'twist'),
		], $arguments, ['type' => 'array']);

		return paginate_links($arguments);
	}

	/**
	 * @param string $label
	 *
	 * @return string|null
	 */
	private function getPrevLink(string $label): ?string
	{
		if ($this->query->current_page() === 1) {
			return null;
		}

		return Tag::a([
			'class' => 'prev',
			'href'  => get_pagenum_link($this->query->current_page() - 1),
		], $label);
	}

	/**
	 * @param string $label
	 *
	 * @return string|null
	 */
	private function getNextLink(string $label): ?string
	{
		if ($this->query->current_page() === $this->query->total_pages()) {
			return null;
		}

		return Tag::a([
			'class' => 'next',
			'href'  => get_pagenum_link($this->query->current_page() + 1),
		], $label);
	}

}
