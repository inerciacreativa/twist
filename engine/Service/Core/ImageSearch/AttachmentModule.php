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
		if ($resolver->post()->images()->count() === 0) {
			return false;
		}

		foreach ($resolver->post()->images() as $image) {
			$resolver->add($image);
		}

		return true;
	}

}