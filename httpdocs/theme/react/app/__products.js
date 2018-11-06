class ProductGroupScrollWrapper extends React.Component {
  constructor(props){
  	super(props);
  	this.state = {
      products:[],
      offset:0
    };
    this.onProductGroupScroll = this.onProductGroupScroll.bind(this);
    this.loadMoreProducts = this.loadMoreProducts.bind(this);
  }

  componentWillMount() {
    window.addEventListener("scroll", this.onProductGroupScroll);
  }

  componentDidMount() {
    this.loadMoreProducts();
  }

  onProductGroupScroll(){
    const end = $("footer").offset().top;
    const viewEnd = $(window).scrollTop() + $(window).height();
    const distance = end - viewEnd;
    if (distance < 0 && this.state.loadingMoreProducts !== true){
      this.setState({loadingMoreProducts:true},function(){
        this.loadMoreProducts();
      });
    }
  }

  loadMoreProducts(){
    const itemsPerScroll = 50;
    const moreProducts = store.getState().products.slice(this.state.offset,this.state.offset + itemsPerScroll);
    const products = this.state.products.concat(moreProducts);
    const offset = this.state.offset + itemsPerScroll;
    this.setState({
      products:products,
      offset:offset,
      loadingMoreProducts:false
    });
  }

  render(){
    let loadingMoreProductsDisplay;
    if (this.state.loadingMoreProducts){
      loadingMoreProductsDisplay = (
        <div className="product-group-scroll-loading-container">
          <div className="icon-wrapper">
            <span className="glyphicon glyphicon-refresh spinning"></span>
          </div>
        </div>
      );
    }
    return (
      <div className="product-group-scroll-wrapper">
        <ProductGroup
          products={this.state.products}
          device={this.props.device}
        />
        {loadingMoreProductsDisplay}
      </div>
    )
  }
}

class ProductGroup extends React.Component {
  render(){
    let products;
    if (this.props.products){
      let productsArray = this.props.products;
      if (this.props.numRows){
        const limit = productHelpers.getNumberOfProducts(this.props.device,this.props.numRows);
        productsArray = productsArray.slice(0,limit)
      }
      products = productsArray.map((product,index) => (
        <ProductGroupItem
          key={index}
          product={product}
        />
      ));
    }

    let sectionHeader;
    if (this.props.title){
      sectionHeader = (
        <div className="section-header">
          <h3 className="mdl-color-text--primary">{this.props.title}</h3>
          <div className="actions">
            <a href={this.props.link } className="mdl-button mdl-js-button mdl-button--colored mdl-button--raised mdl-js-ripple-effect mdl-color--primary">see more</a>
          </div>
        </div>
      );
    }
    return (
      <div className="products-showcase">
        {sectionHeader}
        <div className="products-container row">
          {products}
        </div>
      </div>
    )
  }
}

class ProductGroupItem extends React.Component {
  render(){
    let imageBaseUrl;
    if (store.getState().env === 'live') {
      imageBaseUrl = 'cn.opendesktop.org';
    } else {
      imageBaseUrl = 'cn.pling.it';
    }
    return (
      <div className="product square">
          <div className="content">
            <div className="product-wrapper mdl-shadow--2dp">
              <a href={"/p/"+this.props.product.project_id }>
                <div className="product-image-container">
                  <figure>
                    <img className="very-rounded-corners" src={'https://' + imageBaseUrl + '/cache/200x171/img/' + this.props.product.image_small} />
                    <span className="product-info-title">{this.props.product.title}</span>
                  </figure>
                </div>
                <div className="product-info">
                  <span className="product-info-description">{this.props.product.description}</span>
                </div>
              </a>
            </div>
        </div>
      </div>
    )
  }
}
