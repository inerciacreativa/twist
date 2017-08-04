<?php

namespace Twist\Model\Comment;

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

    /**
     * @var Post
     */
    protected $post;

    /**
     * Form constructor.
     */
    public function __construct()
    {
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
        $arguments['fields']        = $this->getFields();
        $arguments['comment_field'] = $this->getTextArea();
        $arguments['class_form']    = 'form-comment form-standard';
        $arguments['submit_field']  = '<p class="form-actions">%1$s%2$s</p>' . "\n";
        $arguments['submit_button'] = Tag::input([
            'id'    => 'submit',
            'name'  => 'submit',
            'class' => 'btn btn-primary',
            'type'  => 'submit',
            'value' => '%4$s',
        ]);

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
        return Tag::p(['class' => 'form-group'], [
            Tag::label(['for' => 'comment'], _x('Comment', 'noun')),
            Tag::textarea([
                'id'        => 'comment',
                'name'      => 'comment',
                'class'     => 'form-control',
                'cols'      => 45,
                'rows'      => 8,
                'maxlength' => 65525,
                'required'  => true,
            ]),
            Tag::span(['class' => 'form-required'], __('Required')),
        ]);
    }

    /**
     * @return array
     */
    protected function getFields(): array
    {
        $commenter = User::commenter();

        return [
            'author' => $this->getField('author', __('Name'), [
                'value'     => $commenter->name(),
                'type'      => 'text',
                'maxlength' => 245,
                'required'  => true,
            ]),
            'email'  => $this->getField('email', __('Email'), [
                'value'            => $commenter->email(),
                'type'             => 'email',
                'maxlength'        => 100,
                'required'         => true,
                'aria-describedby' => 'email-notes',
            ]),
            'url'    => $this->getField('url', __('Website'), [
                'value'     => $commenter->url(),
                'type'      => 'url',
                'maxlength' => 200,
            ]),
        ];
    }

    /**
     * @param string       $name
     * @param string|array $label
     * @param array        $attributes
     *
     * @return Tag
     */
    protected function getField(string $name, string $label, array $attributes): Tag
    {
        if (isset($attributes['required'])) {
            $label = [
                $label,
                ' ',
                Tag::span(['class' => 'required'], '*'),
            ];
        }

        $field = Tag::p(['class' => 'form-group'], [
            Tag::label(['for' => $name], $label),
            Tag::input(array_merge(['id' => $name, 'name' => $name, 'class' => 'form-control'], $attributes)),
        ]);

        return $field;
    }
}