const {InjectManifest} = require('workbox-webpack-plugin');
const path = require('path');

module.exports = (config) => {
  const target = config.workbox.hash ? require('./helpers/hash')(config.workbox.source, config.cache.name) : config.workbox.source;

  config.cache.files[`${config.workbox.source}`] = target;

  return {
    plugins: [
      new InjectManifest({
        'swSrc': path.join(config.folder.source, config.workbox.source),
        'swDest': target,
        'precacheManifestFilename': path.join(config.folder.scripts, 'precache_[manifestHash].js'),
        'importWorkboxFrom': config.workbox.import,
      }),
    ],
  }
};
