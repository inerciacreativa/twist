<?php

use Twist\Model\Post\Query;
use Twist\Model\Site\Site;
use Twist\Service\Core\ContentCleanerService;
use Twist\Service\Core\HeadCleanerService;
use Twist\Service\Core\SubresourceIntegrityService;
use Twist\Service\Core\ThumbnailGeneratorService;
use Twist\Service\CoreServiceProvider;
use Twist\Twist;

Twist::context()->set('posts', Query::class)->share('site', Site::class);

Twist::theme()
     ->assets('assets', 'assets.json')
     ->services(new CoreServiceProvider())
     ->options([
	     'service' => [
		     HeadCleanerService::id()          => [
			     'enable'    => true,
			     'generator' => true,
			     'edit'      => true,
			     'emoji'     => true,
		     ],
		     ContentCleanerService::id()       => [
			     'enable'     => true,
			     'attributes' => [],
			     'styles'     => [],
			     'comments'   => true,
		     ],
		     ThumbnailGeneratorService::id()   => [
			     'enable' => true,
		     ],
		     SubresourceIntegrityService::id() => [
			     'script'    => false,
			     'style'     => false,
			     'algorithm' => 'sha265',
		     ],
	     ],
     ])
     ->styles([
	     [
		     'id'   => 'twist',
		     'load' => 'styles/app.css',
	     ],
     ])
     ->scripts([
	     [
		     'id'   => 'twist',
		     'load' => 'scripts/app.js',
		     'deps' => ['jquery'],
	     ],
	     [
		     'id'   => 'jquery',
		     'load' => 'scripts/jquery.js',
	     ],
	     [
		     'id'   => 'comment-reply',
		     'load' => static function () {
			     return Query::main()->is_single() && Query::main()
			                                               ->posts()
			                                               ->first()
			                                               ->can_be_commented();
		     },
	     ],
     ])
     ->sidebars([
	     [
		     'id'            => 'sidebar',
		     'name'          => __('Sidebar', 'twist'),
		     'description'   => __('Add widgets here to appear in your sidebar.', 'twist'),
		     'before_widget' => '<aside id="%1$s" class="widget %2$s">',
		     'after_widget'  => '</aside>',
		     'before_title'  => '<h2 class="widget-title">',
		     'after_title'   => '</h2>',
	     ],
	     [
		     'id'            => 'footer-primary',
		     'name'          => __('Primary Footer', 'twist'),
		     'description'   => __('Add widgets here to appear in your footer.', 'twist'),
		     'before_widget' => '<aside id="%1$s" class="widget %2$s">',
		     'after_widget'  => '</aside>',
		     'before_title'  => '<h2 class="widget-title">',
		     'after_title'   => '</h2>',
	     ],
	     [
		     'id'            => 'footer-secondary',
		     'name'          => __('Secondary Footer', 'twist'),
		     'description'   => __('Add widgets here to appear in your footer.', 'twist'),
		     'before_widget' => '<aside id="%1$s" class="widget %2$s">',
		     'after_widget'  => '</aside>',
		     'before_title'  => '<h2 class="widget-title">',
		     'after_title'   => '</h2>',
	     ],
     ])
     ->menus([
	     'primary' => __('Primary Menu', 'twist'),
	     'social'  => __('Social Links Menu', 'twist'),
     ])
     ->contact([
	     'twitter'  => __('Twitter', 'twist'),
	     'facebook' => __('Facebook', 'twist'),
	     'linkedin' => __('LinkedIn', 'twist'),
     ])
     ->thumbnail(850, 510, true);
