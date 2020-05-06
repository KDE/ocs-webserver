module.exports = {
  entry: {
          'metaheader':'./metaheader/entry-metaheader.js',
          //'metaheader-local':'./metaheader/entry-metaheader-local.js',  
          //'home-main-container':'./opendesktop-home/entry-home-main-container.js',
          //'header':'./header/entry-header.js',       
          //'category-tree':'./category-tree/entry-category-tree.js',   
          //'pling-section':'./pling-section/entry-pling-section.js'
          //'app-supporters':'./app-supporters/entry-app-supporters.js'
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
