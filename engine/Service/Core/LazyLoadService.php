<?php

namespace Twist\Service\Core;

use Twist\App\App;
use Twist\App\Asset;
use Twist\Library\Dom\Document;
use Twist\Library\Hook\Hook;
use Twist\Library\Util\Tag;
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
		     ->off('ic_twist_assets_image', 'replace')
		     ->off('ic_feed_show_image', 'replace')
		     ->off('post_thumbnail_html', 'replace', Hook::AFTER)
		     ->off('get_avatar', 'replace', Hook::AFTER)
		     ->off('wp_footer', 'addScript', Hook::AFTER);

		if ($this->config->get('service.content_cleaner.enable')) {
			$this->hook()->off('twist_service_content_cleaner', 'replaceInDocument');
		} else {
			$this->hook()->off('the_content', 'replaceInContent');
		}

		if ($this->config->get('service.lazy_load')) {
			$this->start();
		}
	}

	/**
	 * @inheritdoc
	 */
	public function start(): void
	{
		$this->hook()->enable();
	}

	/**
	 * @inheritdoc
	 */
	public function stop(): void
	{
		$this->hook()->disable();
	}

	/**
	 * @param string $content
	 *
	 * @return string
	 */
	protected function replaceInContent(string $content): string
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
	protected function replace(string $image): string
	{
		$tag = Tag::parse($image);

		if ($tag) {
			if ($tag['data-lazy'] === 'false') {
				return $image;
			}

			$tag['data-src'] = $tag['src'];

			if (isset($tag['srcset'])) {
				$tag['data-srcset'] = $tag['srcset'];
			}

			unset($tag['src'], $tag['srcset']);

			return (string) $tag;
		}

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
