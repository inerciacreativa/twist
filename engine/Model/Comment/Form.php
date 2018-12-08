<?php

namespace Twist\Model\Comment;

use Twist\Library\Hook\Hook;
use Twist\Library\Util\Tag;
use Twist\Model\User\User;
use Twist\Twist;

/**
 * Class Form
 *
 * @package Twist\Model\Comment
 */
class Form
{

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
	 */
	public function __construct()
	{
		if (($this->decorator = Twist::config('form.comment.decorator')) === null) {
			$this->decorator = new FormDecorator(Twist::config('form.comment.classes', []));
		}

		Hook::add('comment_form_defaults', function (array $arguments) {
			return $this->getDefaults($arguments);
		}, 11);

		// Gets the decorated cancel button
		Hook::add('cancel_comment_reply_link', function () {
			return $this->getCancelButton(func_get_arg(2));
		}, 1, 3);

		// Normalize generated hidden fields
		Hook::add('comment_id_fields', function (string $fields) {
			return str_replace(["'", ' />', "\n"], ['"', '>', ''], $fields);
		});
	}

	/**
	 * @return string
	 */
	public function __toString(): string
	{
		return $this->render();
	}

	/**
	 * @return string
	 */
	public function render(): string
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
	protected function getDefaults(array $arguments): array
	{
		$arguments = $this->decorator->getDefaults($arguments);

		$arguments['id_form'] = $this->id;

		$arguments['title_reply']    = Tag::span($arguments['title_reply']);
		$arguments['title_reply_to'] = Tag::span($arguments['title_reply_to']);

		$arguments['cancel_reply_before'] = ' ';
		$arguments['cancel_reply_after']  = '';

		$arguments['fields']        = $this->getFields();
		$arguments['comment_field'] = $this->getTextarea();
		$arguments['submit_button'] = $this->getSubmitButton($arguments['label_submit']);

		return $arguments;
	}

	/**
	 * @return Tag
	 */
	protected function getTextarea(): Tag
	{
		return $this->decorator->getTextareaField('comment', _x('Comment', 'noun', 'twist'), [
			'cols'      => 45,
			'rows'      => 6,
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
			'author' => $this->decorator->getTextField('author', __('Name', 'twist'), [
				'value'     => $commenter->name(),
				'type'      => 'text',
				'maxlength' => 245,
				'required'  => true,
			]),
			'email'  => $this->decorator->getTextField('email', __('E-mail', 'twist'), [
				'value'            => $commenter->email(),
				'type'             => 'email',
				'maxlength'        => 100,
				'required'         => true,
				'aria-describedby' => 'email-notes',
			]),
			'url'    => $this->decorator->getTextField('url', __('Website', 'twist'), [
				'value'     => $commenter->url(),
				'type'      => 'url',
				'maxlength' => 200,
			]),
		];
	}

	/**
	 * @param string $label
	 *
	 * @return Tag
	 */
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