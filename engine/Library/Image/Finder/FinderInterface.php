<?php

namespace Twist\Library\Image\Finder;

use Twist\Library\Image\ImageCollection;

/**
 * Interface FinderInterface
 *
 * @package Twist\Library\Image\Finder
 */
interface FinderInterface
{

    /**
     * @param string               $html
     * @param ImageCollection|null $collection
     * @param int                  $limit
     * @param int                  $width
     *
     * @return ImageCollection
     */
    public function search(string $html, ImageCollection $collection = null, int $limit = 0, int $width = 720): ImageCollection;

}