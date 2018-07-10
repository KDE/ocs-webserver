class ProductView extends React.Component {
  constructor(props){
  	super(props);
  	this.state = {tab:'product'};
    this.toggleProductTab = this.toggleProductTab.bind(this);
    this.toggleFilesTab = this.toggleFilesTab.bind(this);
    this.toggleRatingsTab = this.toggleRatingsTab.bind(this);
    this.toggleFavTab = this.toggleFavTab.bind(this);
  }

  componentWillReceiveProps(nextProps) {
    if (nextProps.product !== this.props.product){
      this.forceUpdate();
    }
  }

  toggleProductTab(){
    this.setState({tab:'product'});
  }

  toggleFilesTab(){
    this.setState({tab:'files'});
  }

  toggleRatingsTab(){
    this.setState({tab:'ratings'});
  }

  toggleFavTab(){
    this.setState({tab:'fav'});
  }

  render(){
    console.log(this.state);
    let galleryDisplay;
    if (this.props.product.r_gallery.length > 0){
      galleryDisplay = (
        <ProductViewGallery
          product={this.props.product}
        />
      );
    }

    return(
      <div id="product-page">
        <div className="container">
          <ProductViewHeader
            product={this.props.product}
          />
          {galleryDisplay}
          <div className="explore-top-bar">
            <a className={this.state.tab === "product" ? "item active" : "item"} onClick={this.toggleProductTab}>Product</a>
            <a className={this.state.tab === "files" ? "item active" : "item"} onClick={this.toggleFilesTab}>Files</a>
            <a className={this.state.tab === "ratings" ? "item active" : "item"} onClick={this.toggleRatingsTab}>Ratings & Reviews</a>
            <a className={this.state.tab === "fav" ? "item active" : "item"} onClick={this.toggleFavTab}>Favs</a>
          </div>
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
          <img src={'https://' + imageBaseUrl + '/cache/140x140/img/' + this.props.product.image_small} />
        </div>
        <div className="details-container">
          <h1>{this.props.product.title}</h1>
          <div className="info-row">
            <a className="user" href={"/member/" + this.props.product.member_id }>
              <span className="avatar"><img src={this.props.product.profile_image_url}/></span>
              <span className="username">{this.props.product.username}</span>
            </a>
            <a href={"/browse/cat/" + this.props.product.project_category_id + "/order/latest/"}>
              <span>{this.props.product.cat_title}</span>
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
