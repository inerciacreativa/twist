<?php

namespace Twist\Model\Comment;

use Twist\Model\Comment\Form\BootstrapDecorator;
use Twist\Model\Comment\Form\BulmaDecorator;
use Twist\Model\Comment\Form\FormDecoratorInterface;
use Twist\Model\User\User;
use Twist\Library\Util\Tag;
use function Twist\capture;

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
	 * @var string
	 */
	protected $id = 'comment-form';

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

		$this->setup();
	}

	/**
	 * @return string
	 */
	public function show(): string
	{
		$form = capture('comment_form');

		return str_replace('<!-- #respond -->', '', $form);
	}

	protected function setup()
	{
		add_filter('comment_form_defaults', function ($arguments) {
			return $this->getArguments($arguments);
		}, 11);

		// Gets the decorated cancel button
		add_filter('cancel_comment_reply_link', function ($cancel, $link, $text) {
			return $this->getCancelButton($text);
		}, 1, 3);

		// Normalize generated hidden fields
		add_filter('comment_id_fields', function (string $fields) {
			return str_replace(["'", ' />', "\n"], ['"', '>', ''], $fields);
		});
	}

	/**
	 * @param array $arguments
	 *
	 * @return array
	 */
	protected function getArguments(array $arguments): array
	{
		$arguments = $this->decorator->getDefaults($arguments);

		$arguments['id_form']        = $this->id;

		$arguments['title_reply']    = Tag::span($arguments['title_reply']);
		$arguments['title_reply_to'] = Tag::span($arguments['title_reply_to']);

		$arguments['cancel_reply_before'] = ' ';
		$arguments['cancel_reply_after']  = '';

		$arguments['fields']        = $this->getInputFields();
		$arguments['comment_field'] = $this->getTextArea();
		$arguments['submit_button'] = $this->getSubmitButton($arguments['label_submit']);

		return $arguments;
	}

	/**
	 * @return Tag
	 */
	protected function getTextArea(): Tag
	{
		return $this->decorator->getTextArea('comment', _x('Comment', 'noun'), [
			'cols'      => 45,
			'rows'      => 6,
			'maxlength' => 65525,
			'required'  => true,
		]);
	}

	/**
	 * @return array
	 */
	protected function getInputFields(): array
	{
		$commenter = User::commenter();

		return [
			'author' => $this->decorator->getTextInput('author', __('Name'), [
				'value'     => $commenter->name(),
				'type'      => 'text',
				'maxlength' => 245,
				'required'  => true,
			]),
			'email'  => $this->decorator->getTextInput('email', __('Email'), [
				'value'            => $commenter->email(),
				'type'             => 'email',
				'maxlength'        => 100,
				'required'         => true,
				'aria-describedby' => 'email-notes',
			]),
			'url'    => $this->decorator->getTextInput('url', __('Website'), [
				'value'     => $commenter->url(),
				'type'      => 'url',
				'maxlength' => 200,
			]),
		];
	}

	protected function getSubmitButton(string $label): Tag
	{
		return $this->decorator->getSubmitButton('submit', $label);
	}

	/**
	 * @param string $label
	 *
	 * @return Tag
	 */
	protected function getCancelButton(string $label): Tag
	{
		return $this->decorator->getCancelButton('cancel-reply', $label, $this->id);
	}

}