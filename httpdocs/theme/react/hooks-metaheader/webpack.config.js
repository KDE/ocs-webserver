module.exports = {
  entry: './app/hooks-metaheader.js',
  output: {
    path: `${__dirname}/`,
    filename: 'hooks-metaheader.js'
  },
  module: {
    rules: [
      {
        test: /\.jsx?$/,
        use: {
          loader: 'babel-loader',
          options: {
            presets: [
              '@babel/preset-env',
              '@babel/preset-react'
            ]
          }
        },
        exclude: /node_modules/
      }
    ]
  }
};
