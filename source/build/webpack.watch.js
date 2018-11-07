const webpack = require('webpack');
const url = require('url');
const BrowserSyncPlugin = require('browsersync-webpack-plugin');

module.exports = (config) => {
  const target = process.env.DEVURL || config.watch.url;
  /**
   * We do this to enable injection over SSL.
   */
  if (url.parse(target).protocol === 'https:') {
    process.env.NODE_TLS_REJECT_UNAUTHORIZED = 0;
  }

  return {
    output: {
      pathinfo: true,
      publicPath: config.watch.proxy + config.path.public,
    },
    devtool: '#cheap-module-source-map',
    stats: false,
    plugins: [
      new webpack.optimize.OccurrenceOrderPlugin(),
      new webpack.HotModuleReplacementPlugin(),
      new webpack.NoEmitOnErrorsPlugin(),
      new BrowserSyncPlugin({
        target,
        proxyUrl: config.watch.proxy,
        watch: config.watch.files,
        open: config.watch.open,
        delay: config.watch.delay,
        disableHostCheck: true,
        advanced: {
          browserSync: {
            https: config.watch.https,
          },
        },
      }),
    ],
  };
};
