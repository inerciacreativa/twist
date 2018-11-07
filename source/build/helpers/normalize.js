const os = require('os');
const upath = require('upath');
const isWindows = os.platform() === 'win32';
const windowsDrive = /^([a-z]):[\\/]{1,2}/gi;
const linuxPath = (match, letter) => {
  return letter.toLowerCase() + '/'
};

/**
 *
 * @param {string} input
 * @param {string} prefix
 * @returns {string}
 */
module.exports.absolute = (input, prefix = '/') => {
  if (isWindows || !windowsDrive.test(input)) {
    return input;
  }

  let output = upath.normalize(input).replace(windowsDrive, linuxPath);

  return upath.join(prefix, output);
};

/**
 *
 * @param {string} base
 * @param {string} folder
 * @returns {string}
 */
module.exports.relative = (base, folder) => (folder === '.') ? base : `${base}/${folder}/`;
