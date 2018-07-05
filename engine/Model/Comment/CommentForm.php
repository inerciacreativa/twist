<?php

namespace Twist\Model\Comment;

use Twist\Library\Util\Tag;
use Twist\Model\User\User;

/**
 * Class CommentForm
 *
 * @package Twist\Model\Comment
 */
class CommentForm
{

	/**
	 * @var string
	 */
	protected $id = 'comment-form';

	/**
	 * @var CommentFormDecoratorInterface
	 */
	protected $decorator;

	/**
	 * Form constructor.
	 *
	 * @param CommentFormDecoratorInterface $decorator
	 */
	public function __construct(CommentFormDecoratorInterface $decorator)
	{
		$this->decorator = $decorator;

		add_filter('comment_form_defaults', function (array $arguments) {
			return $this->parse($arguments);
		}, 11);

		// Gets the decorated cancel button
		add_filter('cancel_comment_reply_link', function (/** @noinspection PhpUnusedParameterInspection */
			string $cancel, string $link, string $text) {
			return $this->cancel($text);
		}, 1, 3);

		// Normalize generated hidden fields
		add_filter('comment_id_fields', function (string $fields) {
			return str_replace(["'", ' />', "\n"], ['"', '>', ''], $fields);
		});
	}

	/**
	 * @return string
	 */
	public function show(): string
	{
		ob_start();
		comment_form();
		$form = ob_get_clean();

		return str_replace('<!-- #respond -->', '', $form);
	}

	/**
	 * @param array $arguments
	 *
	 * @return array
	 */
	protected function parse(array $arguments): array
	{
		$arguments = $this->decorator->defaults($arguments);

		$arguments['id_form'] = $this->id;

		$arguments['title_reply']    = Tag::span($arguments['title_reply']);
		$arguments['title_reply_to'] = Tag::span($arguments['title_reply_to']);

		$arguments['cancel_reply_before'] = ' ';
		$arguments['cancel_reply_after']  = '';

		$arguments['fields']        = $this->fields();
		$arguments['comment_field'] = $this->textarea();
		$arguments['submit_button'] = $this->submit($arguments['label_submit']);

		return $arguments;
	}

	/**
	 * @return Tag
	 */
	protected function textarea(): Tag
	{
		return $this->decorator->textarea('comment', _x('Comment', 'noun', 'twist'), [
			'cols'      => 45,
			'rows'      => 6,
			'maxlength' => 65525,
			'required'  => true,
		]);
	}

	/**
	 * @return array
	 */
	protected function fields(): array
	{
		$commenter = User::commenter();

		return [
			'author' => $this->decorator->text('author', __('Name', 'twist'), [
				'value'     => $commenter->name(),
				'type'      => 'text',
				'maxlength' => 245,
				'required'  => true,
			]),
			'email'  => $this->decorator->text('email', __('E-mail', 'twist'), [
				'value'            => $commenter->email(),
				'type'             => 'email',
				'maxlength'        => 100,
				'required'         => true,
				'aria-describedby' => 'email-notes',
			]),
			'url'    => $this->decorator->text('url', __('Website', 'twist'), [
				'value'     => $commenter->url(),
				'type'      => 'url',
				'maxlength' => 200,
			]),
		];
	}

	protected function submit(string $label): Tag
	{
		return $this->decorator->submit('submit', $label);
	}

	/**
	 * @param string $label
	 *
	 * @return Tag
	 */
	protected function cancel(string $label): Tag
	{
		return $this->decorator->cancel('cancel-reply', $label, $this->id);
	}

}