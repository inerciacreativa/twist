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
		     ->before('twist_post_filter', 'clean')
		     ->before('twist_comment_filter', 'clean');
	}

	/**
	 * @param Document $document
	 *
	 * @return Document
	 */
	protected function clean(Document $document): Document
	{
		$document->cleanAttributes($this->config('attributes', []), $this->config('styles', []));
		$document->cleanElements();

		if ($this->config('comments')) {
			$document->cleanComments();
		}

		return $document;
	}

}