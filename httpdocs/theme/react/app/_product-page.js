class ProductView extends React.Component {
  constructor(props){
  	super(props);
  	this.state = {};
  }

  componentDidMount() {

  }

  render(){
    console.log(this.props.product);
    return(
      <div id="product-page">
        <div className="container">
          <ProductViewHeader
            product={this.props.product}
          />
          <ProductViewGallery
            product={this.props.product}
          />
          <ProductViewContent
            product={this.props.product}
          />
        </div>
      </div>
    );
  }
}

const mapStateToProductPageProps = (state) => {
  const product = state.product;
  return {
    product
  }
}

const mapDispatchToProductPageProps = (dispatch) => {
  return {
    dispatch
  }
}

const ProductViewWrapper = ReactRedux.connect(
  mapStateToProductPageProps,
  mapDispatchToProductPageProps
)(ProductView);

class ProductViewHeader extends React.Component {
  render(){
    let imageBaseUrl;
    if (store.getState().env === 'live') {
      imageBaseUrl = 'cn.pling.com';
    } else {
      imageBaseUrl = 'cn.pling.it';
    }
    return (
      <div className="section mdl-grid" id="product-view-header">
        <div className="image-container">
          <img src={'https://' + imageBaseUrl + '/cache/130x130/img/' + this.props.product.image_small} />
        </div>
        <div className="details-container">
          <h1>{this.props.product.title}</h1>
          <div className="info-row">
            <a href={"/member/" + this.props.product.member_id }>
              <span className="avatar"><img src={this.props.product.profile_image_url}/></span>
              <span className="username">{this.props.product.username}</span>
            </a>
            <a href={"/browse/cat/" + this.props.product.project_category_id + "/order/latest/"}>
              {this.props.product.cat_title}
            </a>
          </div>
          <a href="#" className="mdl-button mdl-js-button mdl-button--colored mdl-button--raised mdl-js-ripple-effect mdl-color--primary">
            Download
          </a>
        </div>
      </div>
    );
  }
}

class ProductViewGallery extends React.Component {
  render(){

    let galleryDisplay;
    if (this.props.product.embed_code.length > 0){
      galleryDisplay = (
        <div id="product-view-gallery"
          dangerouslySetInnerHTML={{__html:this.props.product.embed_code}}>
        </div>
      );
    }
    return (
      <div className="section" id="product-view-gallery-container">
        {galleryDisplay}
      </div>
    )
  }
}

class ProductViewContent extends React.Component {
  render(){
    return (
      <div className="section" id="product-view-content-container">
        <p dangerouslySetInnerHTML={{__html:this.props.product.description}}></p>
      </div>
    )
  }
}
