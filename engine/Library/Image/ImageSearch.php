<?php

namespace Twist\Library\Image;

use Twist\Library\Image\Finder\FinderInterface;
use Twist\Library\Image\Finder\Images;
use Twist\Library\Image\Finder\YouTube;
use Twist\Library\Image\Finder\Vimeo;
use Twist\Library\Image\Finder\Ted;

/**
 * Class ImageSearch
 *
 * @package Twist\Library\Image
 */
class ImageSearch
{

    /**
     * @var FinderInterface[]
     */
    protected $finders = [
        Images::class,
        YouTube::class,
        Vimeo::class,
        Ted::class
    ];

    /**
     * @var string
     */
    protected $html;

    /**
     * @var bool
     */
    protected $videos;

    /**
     * @param string $html
     * @param bool   $videos
     */
    public function __construct(string $html, bool $videos = false)
    {
        $this->html   = $html;
        $this->videos = $videos;
    }

    /**
     * @param string $html
     * @param bool   $videos
     *
     * @return ImageCollection
     */
    public static function find(string $html, bool $videos = false): ImageCollection
    {
        $search = new static($html, $videos);

        return $search->get();
    }

    /**
     * @return ImageCollection
     */
    public function get(): ImageCollection
    {
        $collection = new ImageCollection();

        foreach ($this->finders as $finder) {
            $collection = $this->finder($finder)->search($this->html, $collection);

            if (!$this->videos && $collection->count() > 0) {
                break;
            }
        }

        return $collection;
    }

    /**
     * @param $class
     *
     * @return FinderInterface
     */
    protected function finder(string $class): FinderInterface
    {
        return new $class;
    }

}