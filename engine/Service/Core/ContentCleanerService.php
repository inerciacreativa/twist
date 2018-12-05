<?php

namespace Twist\Service\Core;

use Twist\Library\Dom\Document;
use Twist\Library\Util\Str;
use Twist\Model\Post\Query;
use Twist\Model\Site\Site;
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
	public function boot(): bool
	{
		return $this->config('enable') && !Query::is_admin();
	}

	/**
	 * @inheritdoc
	 */
	protected function init(): void
	{
		$this->hook()
		     ->after('the_content', 'clean')
		     ->after('comment_text', 'clean');
	}

	/**
	 * @param string $content
	 *
	 * @return string
	 */
	protected function clean(string $content): string
	{
		$dom = new Document(Site::language());

		$dom->loadMarkup(Str::whitespace($content));
		$dom->cleanAttributes($this->config('attributes', []), $this->config('styles', []));
		$dom->cleanElements();

		if ($this->config('comments')) {
			$dom->cleanComments();
		}

		$this->hook()->apply('twist_service_content_cleaner', $dom);

		return $dom->saveMarkup();
	}

}