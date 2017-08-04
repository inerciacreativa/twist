<?php

namespace Twist\Template\Attachment;

use Twist\Template\Base\CollectionItem;

class Image extends CollectionItem
{

    /**
     * @var ImageCollection
     */
    private $collection;

    /**
     * @var \WP_Post
     */
    private $image;

    /**
     * @var bool
     */
    private $featured = false;

    /**
     * @var array
     */
    private $sizes = array();

    /**
     * @var string
     */
    private $alt = null;

    /**
     * @var string
     */
    private $caption = null;

    /**
     * @var string
     */
    private $link = null;

    /**
     * Constructor.
     *
     * @param ImageCollection $collection
     * @param \WP_Post        $image
     * @param bool            $featured
     */
    public function __construct(ImageCollection $collection, \WP_Post $image, $featured = false)
    {
        $this->collection = $collection;
        $this->image      = $image;
        $this->featured   = (bool) $featured;
    }

    /**
     * Returns the "src", "width" or "height" attribute of the image for the current size.
     *
     * @param $property
     * @return mixed
     */
    private function get($property)
    {
        $size = $this->size();

        if (!isset($this->sizes[$size])) {
            $info = wp_get_attachment_image_src($this->id(), $size);

            $this->sizes[$size] = array(
                'src'    => $info[0],
                'width'  => $info[1],
                'height' => $info[2]
            );
        }

        return $this->sizes[$size][$property];
    }

    /**
     * Returns the ID of the image.
     *
     * @return int
     */
    public function id()
    {
        return $this->image->ID;
    }

    /**
     * Returns the size of the image.
     * The current size is inferred from the collection.
     *
     * @return string
     */
    public function size()
    {
        return $this->collection->getSize();
    }

    /**
     * Whether it's the featured image.
     *
     * @return bool
     */
    public function is_featured()
    {
        return $this->featured;
    }

    /**
     * Returns the "src" attribute of the image.
     *
     * @return string
     */
    public function src()
    {
        return $this->get('src');
    }

    /**
     * Returns the width of the image.
     *
     * @return string
     */
    public function width()
    {
        return $this->get('width');
    }

    /**
     * Returns the height of the image.
     *
     * @return string
     */
    public function height()
    {
        return $this->get('height');
    }

    /**
     * Returns the "alt" attribute of the image.
     *
     * @return string
     */
    public function alt()
    {
        if (is_null($this->alt)) {
            $this->alt = get_post_meta($this->id(), '_wp_attachment_image_alt', true);

            if (empty($this->alt)) {
                $this->alt = $this->caption();
            }

            if (empty($this->alt)) {
                $this->alt = $this->title();
            }

            if (empty($this->alt)) {
                $this->alt = '';
            } else {
                $this->alt = esc_attr(trim($this->alt));
            }
        }

        return $this->alt;
    }

    /**
     * Returns the caption of the image.
     *
     * @return string
     */
    public function caption()
    {
        if (is_null($this->caption)) {
            $this->caption = trim($this->image->post_excerpt);

            if (!empty($this->caption)) {
                $this->caption = wptexturize($this->caption);
            }
        }

        return $this->caption;
    }

    /**
     * Returns the title of the image.
     *
     * @return string
     */
    public function title()
    {
        return $this->image->post_title;
    }

    /**
     * Returns the description of the image.
     *
     * @return string
     */
    public function description()
    {
        return $this->image->post_content;
    }

    /**
     * Returns the permalink to the attachment page.
     *
     * @return string
     */
    public function link()
    {
        if (is_null($this->link)) {
            $this->link = get_attachment_link($this->image);
        }

        return $this->link;
    }

}