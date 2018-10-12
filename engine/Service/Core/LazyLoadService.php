<?php

namespace Twist\Service\Core;

use Twist\App\App;
use Twist\App\Asset;
use Twist\Library\Dom\Document;
use Twist\Library\Hook\Hook;
use Twist\Library\Util\Tag;
use Twist\Model\Post\Query;
use Twist\Service\Service;

/**
 * Class LazyLoadService
 *
 * @package Twist\Theme
 */
class LazyLoadService extends Service
{

	/**
	 * @var Asset
	 */
	protected $asset;

	/**
	 * LazyLoadService constructor.
	 *
	 * @param App   $app
	 * @param Asset $asset
	 */
	public function __construct(App $app, Asset $asset)
	{
		$this->asset = $asset;

		parent::__construct($app);
	}

	/**
	 * @inheritdoc
	 */
	public function boot(): void
	{
		if (is_admin()) {
			return;
		}

		$this->hook()
		     ->off('twist_asset_image', 'replaceInTag')
		     ->off('twist_asset_logo', 'replaceInTag')
		     ->off('ic_feed_show_image', 'replaceInString')
		     ->off('post_thumbnail_html', 'replaceInString', Hook::AFTER)
		     ->off('get_avatar', 'replaceInString', Hook::AFTER)
		     ->off('wp_footer', 'addScript', Hook::AFTER);

		if ($this->config->get('service.content_cleaner.enable')) {
			$this->hook()->off('twist_app_content_cleaner_service', 'replaceInDocument');
		} else {
			$this->hook()->off('the_content', 'replaceInText');
		}

		if ($this->config('enable')) {
			$this->start();
		}
	}

	/**
	 *
	 */
	protected function init(): void
	{
		if (Query::main()->is_feed()) {
			$this->stop();
		}
	}

	/**
	 * @param string $content
	 *
	 * @return string
	 */
	protected function replaceInText(string $content): string
	{
		$dom = new Document(get_bloginfo('language'));

		$dom->loadMarkup($content);
		$this->replaceInDocument($dom);

		return $dom->saveMarkup();
	}

	/**
	 * @param Document $dom
	 *
	 * @return Document
	 */
	protected function replaceInDocument(Document $dom): Document
	{
		$images = $dom->getElementsByTagName('img');

		/** @var \Twist\Library\Dom\Element $image */
		foreach ($images as $image) {
			$image->setAttribute('data-src', $image->getAttribute('src'));
			$image->removeAttribute('src');

			if ($image->hasAttribute('srcset')) {
				$image->setAttribute('data-srcset', $image->getAttribute('srcset'));
				$image->removeAttribute('srcset');
			}
		}

		return $dom;
	}

	/**
	 * @param string $image
	 *
	 * @return string
	 */
	protected function replaceInString(string $image): string
	{
		$tag = Tag::parse($image);

		if ($tag) {
			return $this->replaceInTag($tag)->render();
		}

		return $image;
	}

	/**
	 * @param Tag $image
	 *
	 * @return Tag
	 */
	protected function replaceInTag(Tag $image): Tag
	{
		if ($image['data-lazy'] === 'false') {
			unset($image['data-lazy']);

			return $image;
		}

		$image['data-src'] = $image['src'];

		if (isset($image['srcset'])) {
			$image['data-srcset'] = $image['srcset'];
		}

		unset($image['src'], $image['srcset']);

		return $image;
	}

	/**
	 * Adds the correct script.
	 *
	 * @see https://github.com/verlok/lazyload
	 */
	protected function addScript(): void
	{
		$lazyload        = $this->asset->url('scripts/lazyload.js', true);
		$lazyload_es2015 = $this->asset->url('scripts/lazyload-es2015.js', true);

		echo <<<SCRIPT
	<script>
		(function(w, d){
			const ll = d.createElement('script'), s = d.scripts[0];
			ll.src = !('IntersectionObserver' in w) ? '$lazyload' : '$lazyload_es2015';
			ll.async = true;
			w.lazyLoadOptions = {};
			s.parentNode.insertBefore(ll, s);
		}(window, document));
	</script>
SCRIPT;
	}
}
