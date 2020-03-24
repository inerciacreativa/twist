<?php

namespace Twist\Asset\Queue;

/**
 * Class Styles
 *
 * @package Twist\Asset\Queue
 */
class Styles extends QueueStore
{

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
			'media'  => 'all',
		];
	}

	/**
	 * @inheritDoc
	 */
	protected function queue(object $style): void
	{
		if (isset($style->src)) {
			wp_deregister_style($style->id);
			wp_enqueue_style($style->id, $style->src, $style->deps, null, $style->media);
		} else {
			wp_enqueue_style($style->id);
		}
	}

	/**
	 * @inheritDoc
	 */
	protected function dequeue(object $style): void
	{
		wp_dequeue_style($style->id);
	}

}
