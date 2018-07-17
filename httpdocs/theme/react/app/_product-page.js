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
          <ProductCommentsContainer
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

    let productTagsDisplay;
    if (this.props.product.r_tags_user){
      const tagsArray = this.props.product.r_tags_user.split(',');
      const tags = tagsArray.map((tag,index) => (
        <span className="mdl-chip" key={index}>
            <span className="mdl-chip__text"><span className="glyphicon glyphicon-tag"></span><a href={"search/projectSearchText/"+tag+"/f/tags"}>{tag}</a></span>
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
            <ProductViewHeaderRatings ratings={this.props.product.r_ratings}/>
          </div>
        </div>
      </div>
    );
  }
}

class ProductViewHeaderRatings extends React.Component {
  constructor(props){
  	super(props);
  	this.state = {};
  }

  componentDidMount() {
    const productRating = productHelpers.calculateProductRatings(this.props.ratings);
    this.setState({productRating:productRating});
  }

  render(){
    return (
      <div className="ratings-bar-container">
        <div className="ratings-bar-left">
          <i className="material-icons">remove</i>
        </div>
        <div className="ratings-bar-holder">
          <div className="ratings-bar" style={{"width":this.state.productRating + "%"}}></div>
          <div className="ratings-bar-empty" style={{"width":(100 - this.state.productRating) + "%"}}></div>
        </div>
        <div className="ratings-bar-right">
          <i className="material-icons">add</i>
        </div>
      </div>
    )
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
    const marginLeft = this.state.itemsWidth * (nextItem - 1);
    this.animateGallerySlider(nextItem,marginLeft);
  }

  onRightArrowClick(){
    let nextItem;
    if (this.state.currentItem === this.state.itemsTotal){
      nextItem = 1;
    } else {
      nextItem = this.state.currentItem + 1;
    }
    const marginLeft = this.state.itemsWidth * (nextItem - 1);
    this.animateGallerySlider(nextItem,marginLeft);
  }

  animateGallerySlider(nextItem,marginLeft){
    this.setState({currentItem:nextItem,galleryWrapperMarginLeft:"-"+marginLeft+"px"},function(){
      const galleryHeight = $(".active-gallery-item").find(".media-item").height();
      this.setState({galleryHeight:galleryHeight});
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
        const currentItem = this.state.currentItem;
        const moreItems = this.props.product.r_gallery.map((gi,index) => (
          <div key={index} className={currentItem === (index + 2) ? "active-gallery-item gallery-item" : "gallery-item"} style={{"width":itemsWidth+"px"}}>
            <img className="media-item" src={imageBaseUrl + "/img/" +  gi}/>
          </div>
        ));

        galleryDisplay = (
          <div id="product-gallery" style={{"height":this.state.galleryHeight}}>
            <a className="gallery-arrow arrow-left" onClick={this.onLeftArrowClick}>
              <i className="material-icons">arrow_back_ios</i>
            </a>
            <div style={{"width":this.state.itemsWidth * this.state.itemsTotal +"px","marginLeft":this.state.galleryWrapperMarginLeft}} className="gallery-items-wrapper">
              <div style={{"width":this.state.itemsWidth+"px"}} dangerouslySetInnerHTML={{__html:this.props.product.embed_code}} className={this.state.currentItem === 1 ? "active-gallery-item gallery-item" : "gallery-item"}>
              </div>
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
        <td className="mdl-data-table__cell--non-numericm">{f.title}</td>
        <td>{f.version}</td>
        <td className="mdl-data-table__cell--non-numericm">{f.description}</td>
        <td className="mdl-data-table__cell--non-numericm">{f.packagename}</td>
        <td  className="mdl-data-table__cell--non-numericm">{f.archname}</td>
        <td>{f.downloaded_count}</td>
        <td className="mdl-data-table__cell--non-numericm">{appHelpers.getTimeAgo(f.created_timestamp)}</td>
        <td className="mdl-data-table__cell--non-numericm">{appHelpers.getFileSize(f.size)}</td>
        <td><a href="#"><i className="material-icons">cloud_download</i></a></td>
        <td>{f.ocs_compatible}</td>
      </tr>
    ));

    const summeryRow = productHelpers.getFilesSummary(this.props.files);

    filesDisplay = (
      <tbody>
        {files}
        <tr>
          <td>{summeryRow.total} files (0 archived)</td>
          <td></td>
          <td></td>
          <td></td>
          <td></td>
          <td>{summeryRow.downloads}</td>
          <td></td>
          <td>{appHelpers.getFileSize(summeryRow.fileSize)}</td>
          <td></td>
          <td></td>
        </tr>
      </tbody>
    );

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

class ProductCommentsContainer extends React.Component {
  constructor(props){
  	super(props);
  	this.state = {};
  }

  render(){
    let commentsDisplay;
    const cArray = categoryHelpers.convertCatChildrenObjectToArray(this.props.product.r_comments);
    if (cArray.length > 0){
      const product = this.props.product;
      const comments = cArray.map((c,index) => {
        if (c.level === 1){
          return (
            <CommentItem product={product} comment={c.comment} key={index} level={1}/>
          )
        }
      });
      commentsDisplay = (
        <div className="comment-list">
          {comments}
        </div>
      )
    }
    return (
      <div className="product-view-section" id="product-comments-container">
        <div className="section-header">
          <h3>Comments</h3>
          <span className="comments-counter">{cArray.length} comments</span>
          <p>Please <a href="/login?redirect=ohWn43n4SbmJZWlKUZNl2i1_s5gggiCE">login</a> or <a href="/register">register</a> to add a comment</p>
        </div>
        {commentsDisplay}
      </div>
    )
  }
}

class CommentItem extends React.Component {
  constructor(props){
  	super(props);
  	this.state = {};
    this.filterByCommentLevel = this.filterByCommentLevel.bind(this);
  }

  filterByCommentLevel(val){
    if (val.level > this.props.level && this.props.comment.comment_id === val.comment.comment_parent_id){
      console.log();
      return val;
    }
  }

  render(){
    let commentRepliesContainer;
    const filteredComments = categoryHelpers.convertCatChildrenObjectToArray(this.props.product.r_comments).filter(this.filterByCommentLevel);
    if (filteredComments.length > 0){
      const product = this.props.product;
      const comments = filteredComments.map((c,index) => (
        <CommentItem product={product} comment={c.comment} key={index} level={c.level}/>
      ));
      commentRepliesContainer = (
        <div className="comment-item-replies-container">
          {comments}
        </div>
      );
    }

    let displayIsSupporter;
    if (this.props.comment.issupporter === "1"){
      displayIsSupporter = <span className="is-supporter-display">S</span>
    }

    return(
      <div className="comment-item">
        <div className="comment-user-avatar">
          <img src={this.props.comment.profile_image_url}/>
          {displayIsSupporter}
        </div>
        <div className="comment-item-content">
          <div className="comment-item-header">
            <a className="comment-username" href={"/member/"+this.props.comment.member_id}>{this.props.comment.username}</a>
            <span className="comment-created-at">
              {appHelpers.getTimeAgo(this.props.comment.comment_created_at)}
            </span>
          </div>
          <div className="comment-item-text">
            {this.props.comment.comment_text}
          </div>
        </div>
        {commentRepliesContainer}
      </div>
    );
  }
}
