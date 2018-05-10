<?php

namespace Twist\Library\Image\Finder;

use Twist\Library\Image\ImageCollection;

/**
 * Class Finder
 *
 * @package Twist\Library\Image\Finder
 */
abstract class Finder implements FinderInterface
{

    /**
     * @var int
     */
    protected $width;

    /**
     * @inheritdoc
     */
    public function search(string $html, ImageCollection $collection = null, int $limit = 0, int $width = 720): ImageCollection
    {
        $this->width = $width;

        if ($collection === null) {
            $collection = new ImageCollection();
        }

        if (!preg_match_all($this->getRegex(), $html, $patterns)) {
            return $collection;
        }

        $images = array_unique($patterns[1]);

        foreach ($images as $id) {
            $image = $this->getImage($id);

            if (empty($image)) {
                continue;
            }

            $collection->append($image);

            if ($limit > 0 && $collection->count() === $limit) {
                break;
            }
        }

        return $collection;
    }

    /**
     * @return string
     */
    abstract protected function getRegex(): string;

    /**
     * @param string $id
     *
     * @return array
     */
    abstract protected function getImage(string $id): array;

    /**
     * @param array $widths
     * @param int   $search
     *
     * @return int
     */
    protected static function closest(array $widths, int $search): int
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