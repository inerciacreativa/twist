<?php

namespace Twist\Service\Filter;

use Twist\Service\Service;
use Twist\Library\Dom\Document;
use Twist\Library\Util\Str;
use Twist\Library\Util\Arr;

/**
 * Class ContentCleanerService
 *
 * @package Twist\Service\Filter
 */
class ContentCleanerService extends Service
{

	/**
	 * @inheritdoc
	 */
	public function start()
	{
		add_filter('the_content', [$this, 'clean'], PHP_INT_MAX);
		add_filter('comment_text', [$this, 'clean'], PHP_INT_MAX);
	}

	/**
	 * @inheritdoc
	 */
	public function stop()
	{
		remove_filter('the_content', [$this, 'clean'], PHP_INT_MAX);
		remove_filter('comment_text', [$this, 'clean'], PHP_INT_MAX);
	}

	/**
	 * @param string $content
	 * @param array  $config
	 *
	 * @return string
	 */
	public function clean(string $content, array $config = []): string
	{
		$defaults = [
			'attributes' => [],
			'styles'     => [],
			'comments'   => false,
		];

		$defaults = Arr::defaults($defaults, $this->config->get('filter.content', []));
		$config   = Arr::defaults($defaults, $config);
		$document = new Document(get_bloginfo('language'));

		$document->loadMarkup(Str::whitespace($content));
		$document->cleanAttributes($config['attributes'], $config['styles']);
		$document->cleanElements();

		if ($config['comments']) {
			$document->cleanComments();
		}

		$document = apply_filters('ic_twist_content_cleaner', $document);

		$content = $document->saveMarkup();

		return $content;
	}

}