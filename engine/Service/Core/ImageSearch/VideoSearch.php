<?php

namespace Twist\Service\Core\ImageSearch;

/**
 * Class VideoSearch
 *
 * @package Twist\Service\Core\ImageSearch
 */
abstract class VideoSearch implements ImageSearchInterface
{

	/**
	 * @var array
	 */
	protected $image;

	/**
	 * @inheritdoc
	 */
	public function search(string $html, int $width = 720): bool
	{
		if (!preg_match_all($this->regex(), $html, $patterns)) {
			return false;
		}

		$images = array_unique($patterns[1]);

		foreach ($images as $id) {
			$image = $this->retrieve($id, $width);

			if (!empty($image)) {
				$this->image = $image;
				break;
			}
		}

		return !empty($this->image);
	}

	/**
	 * @inheritdoc
	 */
	public function get(): ?ExternalImage
	{
		if (empty($this->image)) {
			return null;
		}

		return new ExternalImage($this->image);
	}

	/**
	 * @return string
	 */
	abstract protected function regex(): string;

	/**
	 * @param string $id
	 * @param int    $width
	 *
	 * @return array
	 */
	abstract protected function retrieve(string $id, int $width): array;

	/**
	 * @param array $widths
	 * @param int   $search
	 *
	 * @return int
	 */
	protected function closest(array $widths, int $search): int
	{
		$closest = null;

		foreach ($widths as $width) {
			if ($closest === null || abs($search - $closest) > abs($width - $search)) {
				$closest = $width;
			}
		}

		return $closest;
	}

}