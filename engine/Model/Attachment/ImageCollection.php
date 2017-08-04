<?php

namespace Twist\Template\Attachment;

use Twist\Template\Base\Collection;
use Twist\Template\Post\Post;

class ImageCollection extends Collection
{

    /**
     * @var Post
     */
    private $post;

    /**
     * @var string
     */
    private $default;

    /**
     * @var string
     */
    private $size = null;

    /**
     * @var array
     */
    private $sizes = array();

    /**
     * @var bool
     */
    private $populated = false;

    /**
     * @var int
     */
    private $featured = 0;

    /**
     * Constructor.
     *
     * @param Post $post
     */
    public function __construct(Post $post)
    {
        $this->post     = $post;
        $this->sizes    = get_intermediate_image_sizes();
        $this->default  = reset($this->sizes);
        $this->size     = $this->default;
        $this->featured = (int) get_post_thumbnail_id($post->id());

        if (!in_array('full', $this->sizes)) {
            $this->sizes[] = 'full';
        }
    }

    /**
     * Loads all images attached to the post.
     *
     * @return $this
     */
    public function getAll()
    {
        if (!$this->populated) {
            $arguments = array(
                'post_parent'    => $this->post->id(),
                'post_status'    => 'inherit',
                'post_type'      => 'attachment',
                'post_mime_type' => 'image',
                'order'          => 'ASC',
                'orderby'        => 'menu_order ID'
            );

            if ($this->featured && $this->find($this->featured)) {
                $arguments['exclude'] = array($this->featured);
            }

            $images = get_children($arguments);

            foreach ($images as $image) {
                $featured = ($image->ID === $this->featured) ? true : false;

                $this->add(new Image($this, $image, $featured));
            }

            $this->populated = true;
        }

        return $this;
    }

    /**
     * Returns the featured image attached to the post or null.
     *
     * @return null|Image
     */
    public function getFeatured()
    {
        $featured = null;

        if ($this->featured) {
            $featured = $this->find($this->featured);

            if (is_null($featured)) {
                $image = get_post($this->featured);

                if ($image instanceof \WP_Post) {
                    $this->add(new Image($this, $image, true));

                    $featured = $this->find($this->featured);
                } else {
                    $this->featured = 0;
                }
            }
        }

        return $featured;
    }

    /**
     * Sets the current size.
     *
     * @param $size
     * @return $this
     */
    public function setSize($size = null)
    {
        if (empty($size)) {
            $this->size = $this->default;
        } elseif (in_array($size, $this->sizes)) {
            $this->size = $size;
        }

        return $this;
    }

    /**
     * Gets the current size.
     *
     * @return string
     */
    public function getSize()
    {
        return $this->size;
    }

}