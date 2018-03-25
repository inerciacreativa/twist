<?php

namespace Twist\Service\Filter;

use Twist\Service\Service;

/**
 * Class ImageSearchService
 *
 * @package Twist\Service\Filter
 */
class ImageSearchService extends Service
{

	/**
	 * @inheritdoc
	 */
	public function start()
	{
		add_filter('the_content', [$this, 'search'], 1);
	}

	/**
	 * @inheritdoc
	 */
	public function stop()
	{
		remove_filter('the_content', [$this, 'search'], 1);
	}

	/**
	 * @param string $content
	 */
	public function search(string $content)
	{

	}

}