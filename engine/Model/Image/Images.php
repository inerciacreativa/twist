<?php

namespace Twist\Model\Image;

use Twist\App\AppException;
use Twist\Model\Collection;
use Twist\Model\CollectionInterface;
use Twist\Model\Post\Post;
use Twist\Model\Post\PostsQuery;

/**
 * Class Images
 *
 * @package Twist\Model\Image
 *
 * @method Image parent()
 * @method Image|null get(int $id)
 * @method Image[] all()
 * @method Image|null first(callable $callback = null, $default = null)
 * @method Image|null last(callable $callback = null, $default = null)
 * @method Images merge($models)
 * @method Images only(array $ids)
 * @method Images except(array $ids)
 * @method Images slice(int $offset, int $length = null)
 * @method Images take(int $limit)
 * @method Images filter(callable $callback)
 * @method Images where(string $method, string $operator, $value = null)
 * @method Images shuffle()
 */
class Images extends Collection
{

	/**
	 * @param Post  $post
	 * @param array $parameters
	 *
	 * @return Images
	 */
	public static function make(Post $post, array $parameters = []): Images
	{
		$collection = new static($post);

		if (isset($parameters['orderby'])) {
			$parameters['orderby'] = sanitize_sql_orderby($parameters['orderby']);

			if (!$parameters['orderby']) {
				unset($parameters['orderby']);
			}
		}

		if (!empty($parameters['ids'])) {
			if (empty($parameters['orderby'])) {
				$parameters['orderby'] = 'post__in';
			}

			$parameters['include'] = $parameters['ids'];
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

		$query = PostsQuery::make($parameters, false);

		try {
			foreach ($query->posts() as $image) {
				$collection->add(new Image($image, $post));
			}
		} catch (AppException $exception) {
			// This will never happen!
		}

		return $collection;
	}

	/**
	 * @inheritDoc
	 *
	 * @return Images
	 */
	public function sort(string $method = null, bool $descending = false, int $options = SORT_REGULAR): CollectionInterface
	{
		$factor = 1;
		if ($method === null || $method === 'area') {
			$factor = 10;
		}

		$models = $this->models;

		uasort($models, static function (Image $image1, Image $image2) use ($factor, $descending) {
			$info1 = $image1->get('large');
			$info2 = $image2->get('large');

			$test1 = ($info1['width'] * $factor) + $info1['height'];
			$test2 = ($info2['width'] * $factor) + $info2['height'];

			if ($test1 === $test2) {
				return 0;
			}

			if ($descending) {
				return ($test2 > $test1) ? -1 : 1;
			}

			return ($test1 > $test2) ? -1 : 1;
		});

		return new static($this->parent, $models);
	}

}
