<?php

namespace Twist\Library\Image;

use Twist\Library\Data\CollectionIteratorInterface;

/**
 * Class ImageIterator
 *
 * @package Twist\Library\Image
 */
class ImageIterator extends \ArrayIterator implements CollectionIteratorInterface
{

    /**
     * @return Image
     */
    public function current(): Image
    {
        $image = parent::current();

        return new Image($image);
    }

    /**
     * 
     */
    public function asort(): void
    {
        $this->uasort(function($a, $b) {
            $a = ($a['width'] * 10) + $a['height'];
            $b = ($b['width'] * 10) + $b['height'];

            if ($a === $b) {
                return 0;
            }

            return ($a > $b) ? -1 : 1;
        });
    }

}