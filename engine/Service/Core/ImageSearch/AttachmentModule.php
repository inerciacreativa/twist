<?php

namespace Twist\Service\Core\ImageSearch;

/**
 * Class AttachmentModule
 *
 * @package Twist\Service\Core\ImageSearch
 */
class AttachmentModule implements ModuleInterface
{
	/**
	 * @inheritdoc
	 */
	public function search(ImageResolver $resolver, bool $all = false): bool
	{
		$found = false;

		if ($resolver->post()->images()->count() > 0) {
			foreach ($resolver->post()->images() as $image) {
				$found = true;
				$resolver->add($image);
			}
		}

		return $found;
	}

}