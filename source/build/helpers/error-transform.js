function cleanStackTrace(message) {
  return message.replace(/^\s*at\s.*:\d+:\d+[\s\)]*\n/gm, '');
}

function cleanMessage(message) {
  return message.replace(/^Module build failed.*:\s/, "Module build failed: Module failed because of a stylelint error.\n");
}

function isWebpackError(e) {
  if (Array.isArray(e.originalStack) && e.originalStack.length && e.hasOwnProperty("webpackError")) {
    return e.originalStack
      .some(stackframe => stackframe.fileName && stackframe.fileName.indexOf('stylelint-webpack-plugin') > 0);
  }

  return false;
}

function transform(error) {
  if (isWebpackError(error)) {
    error = Object.assign({}, error, {
      message: cleanStackTrace(cleanMessage(error.message) + '\n'),
      name: 'Lint error',
      type: 'stylelint-error',
    });

    error.webpackError = error.message;
  }

  return error;
}

module.exports = transform;