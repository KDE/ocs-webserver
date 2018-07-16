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
    return(
      <div id="product-page">
        <div className="container">
          <ProductViewHeader
            product={this.props.product}
          />
          <ProductViewGallery
            product={this.props.product}
          />
          <ProductNavBar
            onTabToggle={this.toggleTab}
            tab={this.state.tab}
            product={this.props.product}
          />
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

    let imageBaseUrl;
    if (store.getState().env === 'live') {
      imageBaseUrl = 'cn.pling.com';
    } else {
      imageBaseUrl = 'cn.pling.it';
    }

    let productTagsDisplay;
    if (this.props.product.r_tags_user){
      const tagsArray = this.props.product.r_tags_user.split(',');
      const tags = tagsArray.map((tag,index) => (
        <span className="mdl-chip" key={index}>
            <span className="mdl-chip__text"><a href={"search/projectSearchText/"+tag+"/f/tags"}>{tag}</a></span>
        </span>
      ));
      productTagsDisplay = (
        <div className="product-tags">
          {tags}
        </div>
      );
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
            {productTagsDisplay}
          </div>
          <a href="#" className="mdl-button mdl-js-button mdl-button--colored mdl-button--raised mdl-js-ripple-effect mdl-color--primary">
            Download
          </a>
          <div id="product-view-header-right-side">
            <div className="likes">
              <i className="plingheart fa fa-heart-o heartgrey"></i>
              <span>{this.props.product.r_likes.length}</span>
            </div>
            <div className="ratings-bar-container">
              <div className="ratings-bar-left">
                <i className="material-icons">remove</i>
              </div>
              <div className="ratings-bar-holder">
                <div className="ratings-bar"></div>
                <div className="ratings-bar-empty"></div>
              </div>
              <div className="ratings-bar-right">
                <i className="material-icons">add</i>
              </div>
            </div>
          </div>
        </div>
      </div>
    );
  }
}

class ProductViewGallery extends React.Component {
  constructor(props){
  	super(props);
  	this.state = {
      loading:true,
      currentItem:1,
      galleryWrapperMarginLeft:0
    };
    this.updateDimensions = this.updateDimensions.bind(this);
    this.onLeftArrowClick = this.onLeftArrowClick.bind(this);
    this.onRightArrowClick = this.onRightArrowClick.bind(this);
    this.animateGallerySlider = this.animateGallerySlider.bind(this);
  }

  componentDidMount() {
    window.addEventListener("resize", this.updateDimensions);
    this.updateDimensions();
  }

  componentWillUnmount(){
    window.removeEventListener("resize", this.updateDimensions);
  }

  updateDimensions(){
    const itemsWidth = document.getElementById('product-gallery').offsetWidth;
    const itemsTotal = this.props.product.r_gallery.length + 1;
    this.setState({
      itemsWidth:itemsWidth,
      itemsTotal:itemsTotal,
      loading:false
    });
  }

  onLeftArrowClick(){
    let nextItem;
    if (this.state.currentItem <= 1){
      nextItem = this.state.itemsTotal;
    } else {
      nextItem = this.state.currentItem - 1;
    }
    console.log(nextItem);
    const marginLeft = this.state.itemsWidth * (this.state.currentItem);
    this.animateGallerySlider(nextItem,marginLeft);
  }

  onRightArrowClick(){
    let nextItem;
    if (this.state.currentItem === this.state.itemsTotal){
      nextItem = 1;
    } else {
      nextItem = this.state.currentItem + 1;
    }
    console.log(nextItem);
    const marginLeft = this.state.itemsWidth * (this.state.currentItem);
    this.animateGallerySlider(nextItem,marginLeft);
  }

  animateGallerySlider(nextItem,marginLeft){
    this.setState({currentItem:nextItem,galleryWrapperMarginLeft:"-"+marginLeft+"px"},function(){
      console.log(this.state);
    });
  }

  render(){

    let galleryDisplay;
    if (this.props.product.embed_code.length > 0){

      let imageBaseUrl;
      if (store.getState().env === 'live') {
        imageBaseUrl = 'http://cn.pling.com';
      } else {
        imageBaseUrl = 'http://cn.pling.it';
      }

      if (this.props.product.r_gallery.length > 0){

        const itemsWidth = this.state.itemsWidth;
        const moreItems = this.props.product.r_gallery.map((gi,index) => (
          <div key={index} style={{"width":itemsWidth+"px"}} className="gallery-item">
            <img src={imageBaseUrl + "/img/" +  gi}/>
          </div>
        ));

        galleryDisplay = (
          <div id="product-gallery">
            <a className="gallery-arrow arrow-left" onClick={this.onLeftArrowClick}>
              <i className="material-icons">arrow_back_ios</i>
            </a>
            <div style={{"width":this.state.itemsWidth * this.state.itemsTotal +"px","marginLeft":this.state.galleryWrapperMarginLeft}} className="gallery-items-wrapper">
              <div style={{"width":this.state.itemsWidth+"px"}} dangerouslySetInnerHTML={{__html:this.props.product.embed_code}} className="gallery-item"></div>
              {moreItems}
            </div>
            <a className="gallery-arrow arrow-right" onClick={this.onRightArrowClick}>
              <i className="material-icons">arrow_forward_ios</i>
            </a>
          </div>
        );

      }
    }

    return (
      <div className="section" id="product-view-gallery-container">
        {galleryDisplay}
      </div>
    )
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
      commentsMenuItem = <a className={this.props.tab === "comments" ? "item active" : "item"} onClick={this.toggleCommentsTab}>Comments</a>
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
    } else if (this.props.tab === 'ratings'){
      currentTabDisplay = <p>ratings</p>
    } else if (this.props.tab === 'favs'){
      currentTabDisplay = <p>favs</p>
    } else if (this.props.tab === 'comments'){
      currentTabDisplay = <p>comments</p>
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
