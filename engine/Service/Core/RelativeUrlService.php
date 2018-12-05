<?php

namespace Twist\Service\Core;

use Twist\Model\Post\Query;
use Twist\Service\Service;

/**
 * Class RelativeUrlService
 *
 * @package Twist\Service\Core
 */
class RelativeUrlService extends Service
{

	/**
	 * @var array
	 */
	protected static $filters = [
		//'bloginfo_url',
		//'the_permalink',
		'the_content_more_link',
		'post_link',
		'post_type_link',
		'page_link',
		'attachment_link',
		'post_type_archive_link',
		'author_link',
		'term_link',
		'search_link',
		'day_link',
		'month_link',
		'year_link',
		'get_pagenum_link',
		'get_comments_pagenum_link',
	];

	/**
	 * @inheritdoc
	 */
	public function boot(): bool
	{
		return $this->config('enable') && !Query::is_admin();
	}

	/**
	 * @inheritdoc
	 *
	 * @throws \Exception
	 */
	protected function init(): void
	{
		if (Query::main()->is_feed()) {
			return;
		}

		foreach (self::$filters as $filter) {
			$this->hook()->on($filter, 'makeRelative');
		}
	}

	/**
	 * @param string|mixed $link
	 *
	 * @return string|mixed
	 */
	protected function makeRelative($link)
	{
		if (\is_string($link) && strpos($link, $this->config->get('uri.home')) === 0) {
			return wp_make_link_relative($link);
		}

		return $link;
	}

}