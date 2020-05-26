const entries = {
  //'app-product-relationship':'./modules/app-product-relationship/entry-app-product-relationship.js,
  //'app-supporters':'./modules/app-supporters/entry-app-supporters.js',
  //'category-blocks':'./modules/category-blocks/entry-category-blocks.js',
  //'category-tree':'./modules/category-tree/entry-category-tree.js',
  //'header':'./modules/header/entry-header.js',
  //'metaheader':'./modules/metaheader/entry-metaheader.js',
  //'metaheader-local':'./modules/metaheader/entry-metaheader-local.js',  
  //'home-main-container':'./modules/opendesktop-home/entry-home-main-container.js',
  // 'pling-section':'./modules/pling-section/entry-pling-section.js'
  //'portal-index':'./modules/portal-index/protal-index.js,
  //'product-browse':'./modules/product-browse/entry-product-browse.js',
  'product-media-slider':'./modules/product-media-slider/entry-product-media-slider.js',
  //'tag-rating':'./modules/tag-rating/entry-tag-rating.js
}

const entryFileNames = Object.keys(entries);

module.exports = {
  entry: entries,
  output: {
     path: `${__dirname}/../bundle`,
     filename: '[name]-bundle.js',
     chunkFilename: 'chunks/' + entryFileNames[0] + '/[id].js',
     publicPath: '/theme/react/bundle/'
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
      },{
        test: /\.css$/,
        use: [
          'style-loader',
          'css-loader'
        ]
      },{
        test: /\.(png|jpg|gif|svg)$/i,
        use: [
          {
            loader: 'file-loader?name=[name].[ext]'
          },
        ],
      },{ test: /\.(woff|woff2|eot|ttf|svg)$/, loader: 'url-loader?limit=100000&name=[name].[ext]' }
    ]
  }
};
