const webpack = require('webpack');
const merge = require('webpack-merge');
const path = require('path');
const CleanPlugin = require('clean-webpack-plugin');
const CopyPlugin = require('copy-globs-webpack-plugin');
const ExtractTextPlugin = require('extract-text-webpack-plugin');
const FriendlyErrorsPlugin = require('friendly-errors-webpack-plugin');

const config = require('./config');
const file = (config.cache.enabled) ? config.cache.name : '[name]';

let webpackConfig = {
  context: config.path.source,
  entry: config.entry,
  devtool: config.env.development ? '#source-map' : undefined,
  output: {
    path: config.path.target,
    publicPath: config.path.public,
    filename: `${config.folder.scripts}/${file}.js`,
  },
  stats: {
    hash: false,
    version: false,
    timings: false,
    children: false,
    errors: true,
    errorDetails: true,
    warnings: false,
    chunks: false,
    modules: false,
    reasons: true,
    source: false,
    publicPath: true,
  },
  module: {
    rules: [
      {
        enforce: 'pre',
        test: /\.(js|s[ca]ss|css)$/,
        include: config.path.source,
        loader: 'import-glob',
      },
      {
        test: /\.js$/,
        exclude: [/(node_modules)(?![/|\\](bootstrap|foundation-sites))/],
        use: [
          {loader: 'buble', options: {objectAssign: 'Object.assign'}},
        ],
      },
      {
        test: /\.(sa|sc|c)ss$/,
        include: config.path.source,
        use: ExtractTextPlugin.extract({
          fallback: 'style',
          use: [
            {
              loader: 'css',
              options: {sourceMap: config.env.development},
            },
            {
              loader: 'postcss', options: {
                config: {path: __dirname, ctx: config},
                sourceMap: config.env.development,
              },
            },
            {
              loader: 'resolve-url',
              options: {sourceMap: config.env.development},
            },
            {
              loader: 'sass',
              options: {sourceMap: config.env.development},
            },
          ],
        }),
      },
      {
        test: /\.(ttf|eot|woff2?|png|jpe?g|gif|svg|ico)$/,
        include: config.path.source,
        loader: 'url',
        options: {
          limit: 4096,
          name: `[path]${file}.[ext]`,
        },
      },
      {
        test: /\.(ttf|eot|woff2?|png|jpe?g|gif|svg|ico)$/,
        include: /node_modules/,
        loader: 'url',
        options: {
          limit: 4096,
          outputPath: 'vendor/',
          name: `${file}.[ext]`,
        },
      },
    ],
  },
  resolve: {
    modules: [
      config.path.source,
      'node_modules',
    ],
    enforceExtension: false,
  },
  resolveLoader: {
    moduleExtensions: ['-loader'],
  },
  plugins: [
    new CleanPlugin([path.join(config.folder.target, '**/*')], {
      root: config.path.root,
      verbose: false,
    }),
    new CopyPlugin({
      pattern: config.copy,
      output: `[path]${file}.[ext]`,
      manifest: config.cache.files,
    }),
    new ExtractTextPlugin({
      filename: `${config.folder.styles}/${file}.css`,
      allChunks: true,
      disable: (config.watch.enabled),
    }),
    new webpack.LoaderOptionsPlugin({
      minimize: config.env.production,
      debug: config.watch.enabled,
      stats: {colors: true},
    }),
    new webpack.LoaderOptionsPlugin({
      test: /\.css|s[ac]ss$/,
      options: {
        output: {path: config.path.target},
        context: config.path.source,
      },
    }),
    new FriendlyErrorsPlugin(),
  ],
};

if (config.jquery.enabled) {
  webpackConfig = merge(webpackConfig, require('./webpack.jquery')(config));
}

if (!config.watch.enabled && (config.lint.scripts || config.lint.styles)) {
  webpackConfig = merge(webpackConfig, require('./webpack.lint')(config));
}

if (config.env.production) {
  webpackConfig = merge(webpackConfig, require('./webpack.optimize')(config));

  if (config.workbox.enabled) {
    webpackConfig = merge(webpackConfig, require('./webpack.workbox')(config));
  }

  if (config.cache.enabled) {
    webpackConfig = merge(webpackConfig, require('./webpack.manifest')(config));
  }
}

if (config.watch.enabled) {
  webpackConfig.entry = require('./helpers/hmr-loader')(webpackConfig.entry);
  webpackConfig = merge(webpackConfig, require('./webpack.watch')(config));
}

module.exports = webpackConfig;
