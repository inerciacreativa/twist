<?php

namespace Twist\Service\Core;

use Twist\Library\Dom\Document;
use Twist\Library\Hook\Hook;
use Twist\Library\Util\Str;
use Twist\Service\Service;

/**
 * Class ContentCleanerService
 *
 * @package Twist\Service\Core
 */
class ContentCleanerService extends Service
{

	/**
	 * @inheritdoc
	 */
	public function boot(): void
	{
		$this->hook()
		     ->off('the_content', 'clean', Hook::AFTER)
		     ->off('comment_text', 'clean', Hook::AFTER);

		if ($this->config('enable')) {
			$this->start();
		}
	}

	/**
	 * @param string $content
	 *
	 * @return string
	 */
	protected function clean(string $content): string
	{
		$dom = new Document(get_bloginfo('language'));

		$dom->loadMarkup(Str::whitespace($content));
		$dom->cleanAttributes($this->config('attributes', []), $this->config('styles', []));
		$dom->cleanElements();

		if ($this->config('comments')) {
			$dom->cleanComments();
		}

		$this->hook()->apply('twist_app_content_cleaner_service', $dom);

		return $dom->saveMarkup();
	}

}