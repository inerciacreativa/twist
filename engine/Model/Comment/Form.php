<?php

namespace Twist\Model\Comment;

use Twist\Library\Dom\Document;
use Twist\Library\Hook\Hook;
use Twist\Library\Html\Tag;
use Twist\Model\Site\Site;
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
	 * @var FormDecoratorInterface
	 */
	protected $decorator;

	/**
	 * Form constructor.
	 *
	 * @param array $classes
	 * @param array $ids
	 */
	public function __construct(array $classes = [], array $ids = [])
	{
		$decorator = Twist::config('form.comment.decorator');
		if (!($decorator instanceof FormDecoratorInterface)) {
			$this->decorator = new FormDecorator($classes, $ids);
		} else {
			$this->decorator = new $decorator;
		}

		Hook::add('comment_form_defaults', function (array $arguments) {
			return $this->getDefaults($arguments);
		}, 11);

		// Gets the decorated cancel button
		Hook::add('cancel_comment_reply_link', function () {
			return $this->getCancelButton(func_get_arg(2));
		}, 1, 3);
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

		return $this->getForm($form);
	}

	/**
	 * @param string $form
	 *
	 * @return string
	 */
	protected function getForm(string $form): string
	{
		$document = new Document(Site::language());
		$document->loadMarkup($form);
		$document->removeComments();

		$wrapper = $document->getElementById('respond');
		$wrapper->setAttribute('id', $this->decorator->getId('wrapper'));
		$wrapper->setAttribute('class', $this->decorator->getClass('wrapper'));

		$document = Hook::apply('twist_comment_form', $document);

		return $document->saveMarkup();
	}

	/**
	 * @param array $arguments
	 *
	 * @return array
	 */
	protected function getDefaults(array $arguments): array
	{
		$defaults = $this->decorator->getDefaults($arguments);

		$defaults['title_reply']    = Tag::span($arguments['title_reply']);
		$defaults['title_reply_to'] = Tag::span($arguments['title_reply_to']);

		$defaults['cancel_reply_before'] = ' ';
		$defaults['cancel_reply_after']  = '';

		$defaults['fields']        = $this->getFields();
		$defaults['comment_field'] = $this->getTextarea();
		$defaults['submit_button'] = $this->getSubmitButton($arguments['label_submit']);

		return $defaults;
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
		return $this->decorator->getCancelButton('cancel-reply', $label, $this->decorator->getId('form'));
	}

}
