<?php

if (file_exists(__DIR__ . '/vendor/autoload.php')) {
    include_once __DIR__ . '/vendor/autoload.php';
}

include_once __DIR__ . '/engine/helpers.php';
include_once __DIR__ . '/engine/boot.php';

/**
 * Sets up theme defaults.
 */
add_action('after_setup_theme', function () {
	add_theme_support('customize-selective-refresh-widgets');
	add_theme_support('title-tag');
	add_theme_support('html5', [
		'gallery',
		'caption',
	]);

	add_theme_support('post-thumbnails');
	add_theme_support('post-formats', [
        'aside',
        'image',
        'video',
        'quote',
        'link',
        'gallery',
        'audio',
    ]);

	add_theme_support('custom-logo', [
		'height'      => 250,
		'width'       => 250,
		'flex-height' => true,
		'flex-width'  => true,
	]);

	set_post_thumbnail_size(850, 510, true);

    register_nav_menus([
        'primary' => __('Primary Menu', 'twist'),
        'social'  => __('Social Links Menu', 'twist'),
    ]);
}, 1);

/**
 * Register widget areas.
 */
add_action('widgets_init', function () {
    register_sidebar([
        'name'          => __('Sidebar', 'twist'),
        'id'            => 'sidebar',
        'description'   => __('Add widgets here to appear in your sidebar.', 'twist'),
        'before_widget' => '<aside id="%1$s" class="widget %2$s">',
        'after_widget'  => '</aside>',
        'before_title'  => '<h2 class="widget-title">',
        'after_title'   => '</h2>',
    ]);

    register_sidebar([
        'name'          => __('Primary Footer', 'twist'),
        'id'            => 'footer-primary',
        'description'   => __('Add widgets here to appear in your footer.', 'twist'),
        'before_widget' => '<aside id="%1$s" class="widget %2$s">',
        'after_widget'  => '</aside>',
        'before_title'  => '<h2 class="widget-title">',
        'after_title'   => '</h2>',
    ]);

    register_sidebar([
        'name'          => __('Secondary Footer', 'twist'),
        'id'            => 'footer-secondary',
        'description'   => __('Add widgets here to appear in your footer.', 'twist'),
        'before_widget' => '<aside id="%1$s" class="widget %2$s">',
        'after_widget'  => '</aside>',
        'before_title'  => '<h2 class="widget-title">',
        'after_title'   => '</h2>',
    ]);
}, 1);

/**
 * Register theme assets.
 */
add_filter('wp_enqueue_scripts', function () {
    wp_enqueue_style('twist', Twist\asset_url('styles/main.css', true), false, null);
    wp_enqueue_script('twist', Twist\asset_url('scripts/main.js', true), ['jquery'], null, true);

    if (is_single() && comments_open() && get_option('thread_comments')) {
        wp_enqueue_script('comment-reply');
    }
}, 1);

/**
 * Register user contact methods.
 */
add_filter('user_contactmethods', function (array $methods) {
	return array_merge($methods, [
		'googleplus' => __('Google+', 'twist'),
		'twitter'    => __('Twitter', 'twist'),
		'facebook'   => __('Facebook', 'twist'),
		'linkedin'   => __('LinkedIn', 'twist'),
	]);
});
