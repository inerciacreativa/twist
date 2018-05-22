<?php

namespace Twist\Model\Image;

use Twist\Library\Model\Collection;
use Twist\Model\Post\Post;
use Twist\Model\Post\PostQuery;

/**
 * Class Images
 *
 * @package Twist\Model\Image
 *
 * @method Post|null parent()
 * @method Image get(int $id)
 * @method Image|null first(callable $callback = null)
 * @method Image|null last(callable $callback = null)
 * @method Images only(array $ids)
 * @method Images except(array $ids)
 * @method Images slice(int $offset, int $length = null)
 * @method Images take(int $limit)
 */
class Images extends Collection
{

	public static function create(Post $post, array $parameters = []): Images
	{
		$collection = new static();
		$collection->set_parent($post);

		if (!empty($parameters['ids'])) {
			if (empty($parameters['orderby'])) {
				$parameters['orderby'] = 'post__in';
			}

			$parameters['include'] = $parameters['ids'];
		}

		if (isset($parameters['orderby'])) {
			$parameters['orderby'] = sanitize_sql_orderby($parameters['orderby']);

			if (!$parameters['orderby']) {
				unset($parameters['orderby']);
			}
		}

		if (!isset($parameters['order'])) {
			$parameters['order'] = 'ASC';
		} else if ($parameters['order'] === 'RAND') {
			$parameters['orderby'] = 'none';
		}

		if (!isset($parameters['orderby'])) {
			$parameters['orderby'] = 'menu_order ID';
		}

		$parameters = array_merge($parameters, [
			'post_status'    => 'inherit',
			'post_type'      => 'attachment',
			'post_mime_type' => 'image',
			'post_parent'    => $post->id(),
			'posts_per_page' => -1,
		]);

		$query = PostQuery::create($parameters, false);

		foreach ($query->object()->posts as $image) {
			$collection->add(new Image($image, $post));
		}

		return $collection;
	}

	/**
	 * @return Images
	 */
	public function sort(): Images
	{
		$models = $this->models;

		uasort($models, function (Image $image1, Image $image2) {
			$info1 = $image1->get('large');
			$info2 = $image2->get('large');

			$test1 = ($info1['width'] * 10) + $info1['height'];
			$test2 = ($info2['width'] * 10) + $info2['height'];

			if ($test1 === $test2) {
				return 0;
			}

			return ($test1 > $test2) ? -1 : 1;
		});

		return new static($this->parent, $models);
	}

}