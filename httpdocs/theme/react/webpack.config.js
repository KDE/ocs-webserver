module.exports = {
  entry: {
          //'metaheader':'./src/entry-metaheader.js',
          //'metaheader-local':'./src/entry-metaheader-local.js',
          //'home-main-container':'./src/entry-home-main-container.js'
          'pling-section':'./src/entry-pling-section.js'
         },
  output: {
     path: `${__dirname}/bundle`,
    filename: '[name]-bundle.js'
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
