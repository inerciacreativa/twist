const path = require('path');
const {argv} = require('yargs');
const merge = require('webpack-merge');
const desire = require('./helpers/desire');
const normalize = require('./helpers/normalize');

const userConfig = merge(desire(`${__dirname}/../config`), desire(`${__dirname}/../config-local`));
const isProduction = !!((argv.env && argv.env.production) || argv.p);

const config = merge({
  path: {
    root: process.cwd(),
    prefix: '/',
    public: '/app',
  },
  folder: {
    source: 'source',
    target: 'assets',
    styles: 'styles',
    scripts: 'scripts',
    images: 'images',
  },
  lint: {
    styles: true,
    scripts: true,
  },
  watch: {
    enabled: !!argv.watch,
    proxy: "http://localhost:3000",
    https: false,
    open: false,
    files: [],
    delay: 250,
  },
  cache: {
    enabled: isProduction,
    manifest: 'assets.json',
    name: '[name]_[hash:12]',
    files: {},
  },
  jquery: {
    enabled: true,
    bundle: false,
  },
  workbox: {
    enabled: false,
    source: 'scripts/service-worker.js',
    hash: true,
    import: 'cdn',
  },
}, userConfig);


if (config.watch.https.key) {
  config.watch.https.key = normalize.absolute(config.watch.https.key, config.path.prefix);
}

if (config.watch.https.cert) {
  config.watch.https.cert = normalize.absolute(config.watch.https.cert, config.path.prefix);
}

module.exports = merge(config, {
  env: Object.assign({
    production: isProduction,
    development: !isProduction,
  }, argv.env),
  path: {
    public: normalize.relative(config.path.public, config.folder.target),
    source: path.join(config.path.root, config.folder.source),
    target: path.join(config.path.root, config.folder.target),
  },
  copy: `${config.folder.images}/**/*`,
});

if (process.env.NODE_ENV === undefined) {
  process.env.NODE_ENV = isProduction ? 'production' : 'development';
}
/**
 * If your publicPath differs between environments, but you know it at compile
 * time, then set REDUKT_TARGET as an environment variable before compiling.
 * Example: REDUKT_TARGET=/wp-content/themes/twist/dist yarn build:production
 */
if (process.env.REDUKT_TARGET) {
  module.exports.path.public = process.env.REDUKT_TARGET;
}

/**
 * If you don't know your publicPath at compile time, then uncomment the lines
 * below and use WordPress's wp_localize_script() to set REDUKT_TARGET global.
 * Example:
 *   wp_localize_script('twist/main.js', 'REDUKT_TARGET',
 * get_theme_file_uri('dist/'))
 */
// Object.keys(module.exports.entry).forEach(id =>
//   module.exports.entry[id].unshift(path.join(__dirname,
// 'helpers/target.js')));
