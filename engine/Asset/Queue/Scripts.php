<?php

namespace Twist\Asset\Queue;

use Twist\Asset\Resources;
use Twist\Library\Html\Tag;
use Twist\Library\Support\Data;

/**
 * Class Scripts
 *
 * @package Twist\Asset\Queue
 */
class Scripts extends QueueStore
{

	/**
	 * @var array
	 */
	protected $inline = [];

	/**
	 * @inheritDoc
	 */
	public function __construct(Resources $resources)
	{
		parent::__construct($resources);

		$this->hook()
			 ->after('script_loader_tag', 'attributes', ['arguments' => 2])
			 ->after('wp_footer', 'print');
	}

	/**
	 * @param string          $id
	 * @param string|callable $script
	 */
	public function inline(string $id, $script): void
	{
		$this->inline[$id] = $script;
	}

	/**
	 * @inheritDoc
	 */
	protected function defaults(bool $parent): array
	{
		return [
			'id'     => null,
			'load'   => false,
			'parent' => $parent,
			'deps'   => [],
			'attr'   => null,
			'footer' => true,
		];
	}

	/**
	 * @inheritDoc
	 */
	protected function queue(object $script): void
	{
		if (isset($script->src)) {
			wp_deregister_script($script->id);
			wp_enqueue_script($script->id, $script->src, $script->deps, null, $script->footer);
		} else {
			if ($script->footer) {
				wp_script_add_data($script->id, 'group', 1);
			}
			wp_enqueue_script($script->id);
		}
	}

	/**
	 * @inheritDoc
	 */
	protected function dequeue(object $script): void
	{
		wp_dequeue_script($script->id);
	}

	/**
	 * Adds extra HTML attributes to script elements.
	 *
	 * @param string $script
	 * @param string $handle
	 *
	 * @return string|Tag
	 */
	private function attributes(string $script, string $handle): string
	{
		$scripts = $this->collection->filter(static function ($script) {
			return !empty($script->attr);
		})->pluck('attr', 'id')->all();

		if (array_key_exists($handle, $scripts) && ($tag = Tag::parse($script))) {
			$attribute       = $scripts[$handle];
			$tag[$attribute] = $attribute;
			$script          = (string) $tag;
		}

		return $script;
	}

	/**
	 * Adds inline script in the footer.
	 */
	private function print(): void
	{
		foreach ($this->inline as $script) {
			$script = Data::value($script);
			if (empty($script)) {
				continue;
			}

			echo <<<SCRIPT
	<script>
$script
   </script>
SCRIPT;
		}
	}

}
