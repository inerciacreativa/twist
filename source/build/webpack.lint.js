const webpack = require('webpack');
const path = require('path');
const StyleLintPlugin = require('stylelint-webpack-plugin');

module.exports = (config) => {
  const webpackConfig = {
    plugins: [],
  };

  if (config.lint.styles) {
    webpackConfig.plugins.push(new StyleLintPlugin({
      configFile: path.join(config.path.root, '.stylelintrc'),
      syntax: 'scss',
      emitErrors: true,
      failOnError: false,
    }));
  }

  if (config.lint.scripts) {
    webpackConfig.module = {
      rules: [
        {
          enforce: 'pre',
          test: /\.js$/,
          include: config.path.source,
          use: 'eslint',
        },
      ],
    };

    webpackConfig.plugins.push(new webpack.LoaderOptionsPlugin({
      test: /\.js$/,
      options: {
        eslint: {
          failOnWarning: false,
          failOnError: true,
        },
      },
    }));
  }

  return webpackConfig;
};
