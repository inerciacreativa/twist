const AssetsManifest = require('webpack-assets-manifest');

module.exports = (config) => {
  return {
    plugins: [
      new AssetsManifest({
        output: config.cache.manifest,
        space: 2,
        writeToDisk: false,
        assets: config.cache.files,
        replacer: require('./helpers/manifest'),
      }),
    ],
  };
};
