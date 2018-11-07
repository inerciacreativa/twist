const cssnanoConfig = {
  preset: ['default', {discardComments: {removeAll: true}}],
};

module.exports = ({options}) => {
  return {
    parser: options.env.production ? 'postcss-safe-parser' : undefined,
    plugins: {
      cssnano: options.env.production ? cssnanoConfig : false,
      autoprefixer: true,
    },
  };
};
