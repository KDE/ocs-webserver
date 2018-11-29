module.exports = {
  entry: './src/metaheader.js',
  output: {
    path: `${__dirname}/bundle`,
    filename: 'metaheader-bundle.js'
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
