class ProductView extends React.Component {
  constructor(props){
  	super(props);
  	this.state = {tab:'product'};
    this.toggleTab = this.toggleTab.bind(this);
  }

  componentWillReceiveProps(nextProps) {
    if (nextProps.product !== this.props.product){
      this.forceUpdate();
    }
  }

  toggleTab(tab){
    this.setState({tab:tab});
  }

  render(){
    console.log(this.state);
    let galleryDisplay;
    /*if (this.props.product.r_gallery .length > 0){
      galleryDisplay = (
        <ProductViewGallery
          product={this.props.product}
        />
      );
    }*/

    return(
      <div id="product-page">
        <div className="container">
          <ProductViewHeader
            product={this.props.product}
          />
          <ProductNavBar
            onTabToggle={this.toggleTab}
            tab={this.state.tab}
            product={this.props.product}
          />
          {galleryDisplay}
          <ProductViewContent
            product={this.props.product}
            tab={this.state.tab}
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
    console.log(this.props.product);
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

class ProductNavBar extends React.Component {
  constructor(props){
  	super(props);
    this.toggleProductTab = this.toggleProductTab.bind(this);
    this.toggleFilesTab = this.toggleFilesTab.bind(this);
    this.toggleRatingsTab = this.toggleRatingsTab.bind(this);
    this.toggleFavTab = this.toggleFavTab.bind(this);
    this.toggleCommentsTab = this.toggleCommentsTab.bind(this);
  }

  toggleProductTab(){
    this.props.onTabToggle('product');
  }

  toggleFilesTab(){
    this.props.onTabToggle('files');
  }

  toggleRatingsTab(){
    this.props.onTabToggle('ratings');
  }

  toggleFavTab(){
    this.props.onTabToggle('fav');
  }

  toggleCommentsTab(){
    this.props.onTabToggle('comments');
  }

  render(){

    let productNavBarDisplay;
    let filesMenuItem, ratingsMenuItem, commentsMenuItem, favsMenuItem;
    if (this.props.product.r_files.length > 0 ){
      filesMenuItem = <a className={this.props.tab === "files" ? "item active" : "item"} onClick={this.toggleFilesTab}>Files ({this.props.product.r_files.length})</a>
    }
    if (this.props.product.r_ratings.length > 0) {
      ratingsMenuItem = <a className={this.props.tab === "ratings" ? "item active" : "item"} onClick={this.toggleRatingsTab}>Ratings & Reviews</a>
    }
    if (this.props.product.r_plings.length > 0) {
      favsMenuItem = <a className={this.props.tab === "fav" ? "item active" : "item"} onClick={this.toggleFavTab}>Favs ({this.props.product.r_plings.length})</a>
    }
    if (this.props.product.r_comments.length > 0){
      commentsMenuItem = <a className={this.props.tab === "fav" ? "item active" : "item"} onClick={this.toggleCommentsTab}>Comments</a>
    }

    return (
      <div className="explore-top-bar">
        <a className={this.props.tab === "product" ? "item active" : "item"} onClick={this.toggleProductTab}>Product</a>
        {filesMenuItem}
        {ratingsMenuItem}
        {favsMenuItem}
        {commentsMenuItem}
      </div>
    )
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
    let currentTabDisplay;
    if (this.props.tab === 'product'){
      currentTabDisplay = (
        <div className="product-tab" id="product-tab">
          <p dangerouslySetInnerHTML={{__html:this.props.product.description}}></p>
        </div>
      );
    } else if (this.props.tab === 'files'){
      currentTabDisplay = (
        <ProductViewFilesTab
          files={this.props.product.r_files}
        />
      )
    }
    return (
      <div className="section" id="product-view-content-container">
        {currentTabDisplay}
      </div>
    )
  }
}

class ProductViewFilesTab extends React.Component {
  render(){

    let filesDisplay;
    const files = this.props.files.map((f,index) => (
      <tr key={index}>
        <th className="mdl-data-table__cell--non-numericm">{f.title}</th>
        <th>{f.version}</th>
        <th className="mdl-data-table__cell--non-numericm">{f.description}</th>
        <th className="mdl-data-table__cell--non-numericm">{f.packagename}</th>
        <th  className="mdl-data-table__cell--non-numericm">{f.archname}</th>
        <th>{f.downloaded_count}</th>
        <th className="mdl-data-table__cell--non-numericm">{f.created_timestamp}</th>
        <th className="mdl-data-table__cell--non-numericm">{f.size}</th>
        <th>download icon</th>
        <th>{f.ocs_compatible}</th>
      </tr>
    ));
    filesDisplay = <tbody>{files}</tbody>

    return (
      <div id="files-tab" className="product-tab">
        <table className="mdl-data-table mdl-js-data-table mdl-shadow--2dp">
          <thead>
            <tr>
              <th className="mdl-data-table__cell--non-numericm">File (click to download)</th>
              <th  className="mdl-data-table__cell--non-numericm">Version</th>
              <th  className="mdl-data-table__cell--non-numericm">Description</th>
              <th  className="mdl-data-table__cell--non-numericm">Packagetype</th>
              <th  className="mdl-data-table__cell--non-numericm">Architecture</th>
              <th  className="mdl-data-table__cell--non-numericm">Downloads</th>
              <th  className="mdl-data-table__cell--non-numericm">Date</th>
              <th  className="mdl-data-table__cell--non-numericm">Filesize</th>
              <th  className="mdl-data-table__cell--non-numericm">DL</th>
              <th  className="mdl-data-table__cell--non-numericm">OCS-Install</th>
            </tr>
          </thead>
          {filesDisplay}
        </table>
      </div>
    );
  }
}
