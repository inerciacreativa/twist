<?php

namespace Twist\Model\User;

use Twist\Library\Util\Str;
use Twist\Model\Model;
use Twist\Model\Post\Query;
use Twist\Library\Util\Tag;

/**
 * Class User
 *
 * @package Twist\Model\User
 */
class User extends Model implements UserInterface
{

    /**
     * @var static
     */
    protected static $current;

    /**
     * @var static
     */
    protected static $commenter;

    /**
     * @var \WP_User
     */
    protected $user;

    /**
     * @var Tag[]
     */
    protected $avatar = [];

    /**
     * @var Profiles
     */
    protected $profiles;

    /**
     * @var Query
     */
    protected $posts;

    /**
     * @return User
     */
    public static function current(): User
    {
        if (empty(static::$current)) {
            static::$current = new static();
        }

        return static::$current;
    }

    /**
     * @return User
     */
    public static function commenter(): User
    {
        if (empty(static::$commenter)) {
            $user      = new static();
            $commenter = wp_get_current_commenter();

            if ($commenter['comment_author']) {
                $user->user->display_name = $commenter['comment_author'];
            }

            if ($commenter['comment_author_email']) {
                $user->user->user_email = $commenter['comment_author_email'];
            }

            if ($commenter['comment_author_url']) {
                $user->user->user_url = $commenter['comment_author_url'];
            }

            static::$commenter = $user;
        }

        return static::$commenter;
    }

    /**
     * @param \WP_User|\stdClass|int|string $user
     *
     * @return User
     */
    public static function create($user): User
    {
        return new static($user);
    }

    /**
     * User constructor.
     *
     * @param \WP_User|\stdClass|int|string|null $user
     */
    public function __construct($user = null)
    {
        if ($user === null) {
            $this->user = wp_get_current_user();
        } elseif ($user instanceof \WP_User) {
            $this->user = $user;
        } elseif (is_object($user) || is_numeric($user) || is_string($user)) {
            $this->user = new \WP_User($user);
        }
    }

    /**
     * @return int
     */
    public function id(): int
    {
        return (int)$this->user->ID;
    }

    /**
     * @return bool
     */
    public function exists(): bool
    {
        return $this->id() > 0;
    }

    /**
     * @return bool
     */
    public function is_logged(): bool
    {
        return $this->exists();
    }

    /**
     * @return bool
     */
    public function is_admin(): bool
    {
        return $this->exists() && $this->user->has_cap('administrator');
    }

    /**
     * @return string
     */
    public function name(): string
    {
        return $this->user->display_name;
    }

    /**
     * @return string
     */
    public function nice_name(): string
    {
        return $this->user->user_nicename;
    }

    /**
     * @return string
     */
    public function first_name(): string
    {
        return $this->user->first_name;
    }

    /**
     * @return string
     */
    public function last_name(): string
    {
        return $this->user->last_name;
    }

    /**
     * Retrieve the user email.
     *
     * @return string
     */
    public function email(): string
    {
        return $this->user->user_email;
    }

    /**
     * @return string
     */
    public function url(): string
    {
        return $this->user->user_url;
    }

    /**
     * @return string
     */
    public function description(): string
    {
        return $this->user->description;
    }

    /**
     * @param int   $size
     * @param array $attributes
     *
     * @return string
     */
    public function avatar(int $size = 96, array $attributes = []): string
    {
        if (array_key_exists($size, $this->avatar)) {
            $avatar = $this->avatar[$size];
        } else {
            $title = sprintf(__('Image of %s', 'twist'), Str::fromEntities($this->name()));
            $avatar = get_avatar($this->user->user_email, $size, '', $title);
            $avatar = $this->avatar[$size] = Tag::parse($avatar);
        }

        $avatar->attributes(array_merge(['class' => 'avatar photo'], $attributes));

        return $avatar;
    }

    /**
     * @return Profiles
     */
    public function profiles(): Profiles
    {
        if ($this->profiles === null) {
            $this->profiles = new Profiles($this);
        }

        return $this->profiles;
    }

    /**
     * @param string $capability
     *
     * @return bool
     */
    public function can(string $capability): bool
    {
        if ($capability === 'comment') {
            return !(get_option('comment_registration') && !$this->exists());
        }

        if (!$this->exists()) {
            return false;
        }

        $arguments = array_slice(func_get_args(), 1);
        $arguments = array_merge([$capability], $arguments);

        return call_user_func_array([$this->user, 'has_cap'], $arguments);
    }

    /**
     * @param int $number
     *
     * @return Query
     */
    public function posts(int $number = 5): Query
    {
        if ($this->posts === null) {
            $query = [
                'author'         => $this->user->ID,
                'posts_per_page' => $number,
                'orderby'        => 'post_date',
                'order'          => 'DESC',
            ];

            if (isset($GLOBALS['post'])) {
                $query['post__not_in'] = [$GLOBALS['post']->id];
            }

            $this->posts = Query::make($query);
        }

        return $this->posts;
    }

}