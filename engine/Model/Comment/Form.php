<?php

namespace Twist\Model\Comment;

use Twist\Model\Comment\Form\BootstrapDecorator;
use Twist\Model\Comment\Form\BulmaDecorator;
use Twist\Model\Comment\Form\FormDecoratorInterface;
use Twist\Model\Post\Post;
use Twist\Model\User\User;
use Twist\Library\Util\Tag;

/**
 * Class Form
 *
 * @package Twist\Model\Comment
 */
class Form
{

	static protected $decorators = [
		'bulma'     => BulmaDecorator::class,
		'bootstrap' => BootstrapDecorator::class,
	];

	/**
	 * @var Post
	 */
	protected $post;

	/**
	 * @var FormDecoratorInterface
	 */
	protected $decorator;

	/**
	 * Form constructor.
	 *
	 * @param string $decorator
	 */
	public function __construct(string $decorator = null)
	{
		$decorator       = self::$decorators[$decorator] ?? reset(self::$decorators);
		$this->decorator = new $decorator;

		add_filter('comment_form_defaults', [$this, 'setup'], 1);
		add_filter('comment_id_fields', [$this, 'decorate']);
	}

	/**
	 * @return string
	 */
	public function show(): string
	{
		ob_start();
		comment_form();

		return str_replace('<!-- #respond -->', '', ob_get_clean());
	}

	/**
	 * @param array $arguments
	 *
	 * @return array
	 */
	public function setup(array $arguments): array
	{
		$arguments = array_merge($arguments, $this->decorator->defaults());

		$arguments['fields']        = $this->getFields();
		$arguments['comment_field'] = $this->getTextArea();

		return $arguments;
	}

	/**
	 * @param string $fields
	 *
	 * @return string
	 */
	public function decorate(string $fields): string
	{
		return str_replace(["'", ' />', "\n"], ['"', '>', ''], $fields);
	}

	/**
	 * @return Tag
	 */
	protected function getTextArea(): Tag
	{
		return $this->decorator->textarea('comment', _x('Comment', 'noun'), [
			'cols'      => 45,
			'rows'      => 8,
			'maxlength' => 65525,
			'required'  => true,
		]);
	}

	/**
	 * @return array
	 */
	protected function getFields(): array
	{
		$commenter = User::commenter();

		return [
			'author' => $this->decorator->text('author', __('Name'), [
				'value'     => $commenter->name(),
				'type'      => 'text',
				'maxlength' => 245,
				'required'  => true,
			]),
			'email'  => $this->decorator->text('email', __('Email'), [
				'value'            => $commenter->email(),
				'type'             => 'email',
				'maxlength'        => 100,
				'required'         => true,
				'aria-describedby' => 'email-notes',
			]),
			'url'    => $this->decorator->text('url', __('Website'), [
				'value'     => $commenter->url(),
				'type'      => 'url',
				'maxlength' => 200,
			]),
		];
	}

}