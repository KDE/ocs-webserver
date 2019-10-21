module.exports = {
  entry: {
          'metaheader':'./metaheader/entry-metaheader.js',
          'metaheader-local':'./metaheader/entry-metaheader-local.js',         
         },
  output: {
     path: `${__dirname}/../bundle`,
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
