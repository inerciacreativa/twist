/** Import local dependencies */
import Router from './util/Router';
import common from './routes/common';
import home from './routes/home';
import single from './routes/single';

/**
 * Populate Router instance with DOM routes
 * @type {Router} routes - An instance of our router
 */
const routes = new Router({
  common,
  home,
  single,
});

/** Load Events */
$(document).ready(() => routes.loadEvents());