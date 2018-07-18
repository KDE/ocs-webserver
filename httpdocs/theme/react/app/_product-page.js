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
    if (nextProps.lightboxGallery !== this.props.lightboxGallery){
      this.forceUpdate();
    }
  }

  toggleTab(tab){
    this.setState({tab:tab});
  }

  render(){
    let productGalleryLightboxDisplay;
    if (this.props.lightboxGallery.show === true){
      productGalleryLightboxDisplay = (
        <ProductGalleryLightbox
          product={this.props.product}
        />
      );
    }
    return(
      <div id="product-page">
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
          {productGalleryLightboxDisplay}
      </div>
    );
  }
}

const mapStateToProductPageProps = (state) => {
  const product = state.product;
  const lightboxGallery = state.lightboxGallery;
  return {
    product,
    lightboxGallery
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
      <div className="wrapper" id="product-view-header">
        <div className="container">
          <div className="section mdl-grid" >
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
                <a href={"/browse/cat/" + this.props.product.project_category_id + "/order/latest?new=1"}>
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
                <ProductViewHeaderRatings product={this.props.product}/>
              </div>
            </div>
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

  render(){
    return (
      <div className="ratings-bar-container">
        <div className="ratings-bar-left">
          <i className="material-icons">remove</i>
        </div>
        <div className="ratings-bar-holder">
          <div className="ratings-bar" style={{"width":this.props.product.laplace_score + "%"}}></div>
          <div className="ratings-bar-empty" style={{"width":(100 - this.props.product.laplace_score) + "%"}}></div>
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
    const productGallery = document.getElementById('product-gallery');
    const itemsWidth = 300;
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
    this.setState({currentItem:nextItem,galleryWrapperMarginLeft:"-"+marginLeft+"px"});
  }

  onGalleryItemClick(num){
    store.dispatch(showLightboxGallery(num));
  }

  render(){
    console.log(this.state);
    let galleryDisplay;
    if (this.props.product.embed_code && this.props.product.embed_code.length > 0){

      let imageBaseUrl;
      if (store.getState().env === 'live') {
        imageBaseUrl = 'http://cn.pling.com';
      } else {
        imageBaseUrl = 'http://cn.pling.it';
      }

      if (this.props.product.r_gallery.length > 0){

        const itemsWidth = this.state.itemsWidth;
        const currentItem = this.state.currentItem;
        const self = this;
        const moreItems = this.props.product.r_gallery.map((gi,index) => (
          <div key={index} onClick={() => this.onGalleryItemClick(index + 2)} className={currentItem === (index + 2) ? "active-gallery-item gallery-item" : "gallery-item"}>
            <img className="media-item" src={imageBaseUrl + "/img/" +  gi}/>
          </div>
        ));

        let arrowLeft, arrowRight;
        if (this.state.currentItem !== 1){
          arrowLeft = (
            <a className="gallery-arrow arrow-left" onClick={this.onLeftArrowClick}>
              <i className="material-icons">chevron_left</i>
            </a>
          );
        }
        if (this.state.currentItem < (this.state.itemsTotal - 1)){
          arrowRight = (
            <a className="gallery-arrow arrow-right" onClick={this.onRightArrowClick}>
              <i className="material-icons">chevron_right</i>
            </a>
          );
        }

        galleryDisplay = (
          <div id="product-gallery">
            {arrowLeft}
            <div className="section">
              <div style={{"width":this.state.itemsWidth * this.state.itemsTotal +"px","marginLeft":this.state.galleryWrapperMarginLeft}} className="gallery-items-wrapper">
                <div onClick={() => this.onGalleryItemClick(1)} dangerouslySetInnerHTML={{__html:this.props.product.embed_code}} className={this.state.currentItem === 1 ? "active-gallery-item gallery-item" : "gallery-item"}>
                </div>
                {moreItems}
              </div>
            </div>
            {arrowRight}
          </div>
        );

      }
    }

    return (
      <div className="section" id="product-view-gallery-container">
        <div className="container">
          <div className="section">
            {galleryDisplay}
          </div>
        </div>
      </div>
    )
  }
}

class ProductGalleryLightbox extends React.Component {
  constructor(props){
  	super(props);
    let currentItem;
    if (store.getState().lightboxGallery){
      currentItem = store.getState().lightboxGallery.currentItem;
    } else {
      currentItem = 1;
    }
  	this.state = {
      currentItem:currentItem,
      loading:true
    };
    this.updateDimensions = this.updateDimensions.bind(this);
    this.toggleNextGalleryItem = this.toggleNextGalleryItem.bind(this);
    this.togglePrevGalleryItem = this.togglePrevGalleryItem.bind(this);
    this.animateGallerySlider = this.animateGallerySlider.bind(this);
    this.onThumbnailClick = this.onThumbnailClick.bind(this);
  }

  componentDidMount() {
    window.addEventListener("resize", this.updateDimensions);
    this.updateDimensions();
  }

  componentWillUnmount(){
    window.removeEventListener("resize", this.updateDimensions);
  }

  updateDimensions(){
    const thumbnailsSectionWidth = document.getElementById('thumbnails-section').offsetWidth;
    const itemsWidth = 300;
    const itemsTotal = this.props.product.r_gallery.length + 1;
    let thumbnailsMarginLeft = 0;
    if ((this.state.currentItem * itemsWidth) > thumbnailsSectionWidth){ thumbnailsMarginLeft = thumbnailsSectionWidth - (this.state.currentItem * itemsWidth); }
    this.setState({
      itemsWidth:itemsWidth,
      itemsTotal:itemsTotal,
      thumbnailsSectionWidth:thumbnailsSectionWidth,
      thumbnailsMarginLeft:thumbnailsMarginLeft,
      loading:false
    });
  }

  toggleNextGalleryItem(){
    const currentItem = this.state.currentItem + 1;
    this.animateGallerySlider(currentItem);
  }

  togglePrevGalleryItem(){
    const currentItem = this.state.currentItem - 1;
    this.animateGallerySlider(currentItem);
  }

  animateGallerySlider(currentItem){
    this.setState({currentItem:currentItem},function(){
      this.updateDimensions();
    });
  }

  onThumbnailClick(num){
    this.animateGallerySlider(num);
  }

  hideLightbox(){
    store.dispatch(hideLightboxGallery());
  }

  render(){

    let imageBaseUrl;
    if (store.getState().env === 'live') {
      imageBaseUrl = 'http://cn.pling.com';
    } else {
      imageBaseUrl = 'http://cn.pling.it';
    }

    const currentItem = this.state.currentItem;
    const self = this;
    const thumbnails = this.props.product.r_gallery.map((gi,index) => (
      <div key={index} onClick={() => self.onThumbnailClick(index + 2)} className={self.state.currentItem === (index + 2) ? "active thumbnail-item" : "thumbnail-item"}>
        <img className="media-item" src={imageBaseUrl + "/img/" +  gi}/>
      </div>
    ));

    let arrowLeft, arrowRight;
    if (this.state.currentItem > 1){
      arrowLeft = (
        <a className="gallery-arrow" onClick={this.togglePrevGalleryItem} id="arrow-left">
          <i className="material-icons">chevron_left</i>
        </a>
      );
    }
    if (this.state.currentItem < (this.props.product.r_gallery.length + 1)){
      arrowRight = (
        <a className="gallery-arrow" onClick={this.toggleNextGalleryItem} id="arrow-right">
          <i className="material-icons">chevron_right</i>
        </a>
      );
    }

    let mainItemDisplay;
    if (currentItem === 1){
      mainItemDisplay = <div dangerouslySetInnerHTML={{__html:this.props.product.embed_code}}></div>
    } else {
      const mainItem = this.props.product.r_gallery[currentItem - 2];
      mainItemDisplay = (
        <img className="media-item" src={imageBaseUrl + "/img/" +  mainItem}/>
      );
    }

    return (
      <div id="product-gallery-lightbox">
        <a id="close-lightbox" onClick={this.hideLightbox}><i className="material-icons">cancel</i></a>
        <div id="lightbox-gallery-main-view">
          {arrowLeft}
          <div className="current-gallery-item">
            {mainItemDisplay}
          </div>
          {arrowRight}
        </div>
        <div id="lightbox-gallery-thumbnails">
          <div className="section" id="thumbnails-section">
            <div id="gallery-items-wrapper" style={{"width":(this.state.itemsTotal * this.state.itemsWidth)+"px","marginLeft":this.state.thumbnailsMarginLeft+"px"}}>
              <div onClick={() => this.onThumbnailClick(1)} dangerouslySetInnerHTML={{__html:this.props.product.embed_code}} className={this.state.currentItem === 1 ? "active thumbnail-item" : "thumbnail-item"}>
              </div>
              {thumbnails}
            </div>
          </div>
        </div>
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
      const activeRatingsNumber = productHelpers.getActiveRatingsNumber(this.props.product.r_ratings);
      ratingsMenuItem = <a className={this.props.tab === "ratings" ? "item active" : "item"} onClick={this.toggleRatingsTab}>Ratings & Reviews ({activeRatingsNumber})</a>
    }
    if (this.props.product.r_plings.length > 0) {
      favsMenuItem = <a className={this.props.tab === "fav" ? "item active" : "item"} onClick={this.toggleFavTab}>Favs ({this.props.product.r_plings.length})</a>
    }
    if (this.props.product.r_comments.length > 0){
      commentsMenuItem = <a className={this.props.tab === "comments" ? "item active" : "item"} onClick={this.toggleCommentsTab}>Comments</a>
    }

    return (
      <div className="wrapper">
        <div className="container">
          <div className="explore-top-bar">
            <a className={this.props.tab === "product" ? "item active" : "item"} onClick={this.toggleProductTab}>Product</a>
            {filesMenuItem}
            {ratingsMenuItem}
            {favsMenuItem}
            {commentsMenuItem}
          </div>
        </div>
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
          <ProductCommentsContainer
            product={this.props.product}
          />
        </div>
      );
    } else if (this.props.tab === 'files'){
      currentTabDisplay = (
        <ProductViewFilesTab
          files={this.props.product.r_files}
        />
      )
    } else if (this.props.tab === 'ratings'){
      currentTabDisplay = (
        <ProductViewRatingsTab
          ratings={this.props.product.r_ratings}
        />
      );
    } else if (this.props.tab === 'favs'){
      currentTabDisplay = <p>favs</p>
    } else if (this.props.tab === 'comments'){
      currentTabDisplay = <p>comments</p>
    }
    return (
      <div className="wrapper">
        <div className="container">
          <div className="section" id="product-view-content-container">
            {currentTabDisplay}
          </div>
        </div>
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
              <th className="mdl-data-table__cell--non-numericm">File</th>
              <th className="mdl-data-table__cell--non-numericm">Version</th>
              <th className="mdl-data-table__cell--non-numericm">Description</th>
              <th className="mdl-data-table__cell--non-numericm">Packagetype</th>
              <th className="mdl-data-table__cell--non-numericm">Architecture</th>
              <th className="mdl-data-table__cell--non-numericm">Downloads</th>
              <th className="mdl-data-table__cell--non-numericm">Date</th>
              <th className="mdl-data-table__cell--non-numericm">Filesize</th>
              <th className="mdl-data-table__cell--non-numericm">DL</th>
              <th className="mdl-data-table__cell--non-numericm">OCS-Install</th>
            </tr>
          </thead>
          {filesDisplay}
        </table>
      </div>
    );
  }
}

class ProductViewRatingsTab extends React.Component {
  constructor(props){
  	super(props);
  	this.state = {
      filter:'active'
    };
    this.filterLikes = this.filterLikes.bind(this);
    this.filterDislikes = this.filterDislikes.bind(this);
    this.filterActive = this.filterActive.bind(this);
    this.setFilter = this.setFilter.bind(this);
  }

  filterLikes(rating){
    if (rating.user_like === "1"){
      return rating;
    }
  }

  filterDislikes(rating){
    if (rating.user_dislike === "1"){
      return rating;
    }
  }

  filterActive(rating){
    if (rating.rating_active === "1"){
      return rating;
    }
  }

  setFilter(filter){
    this.setState({filter:filter});
  }

  render(){

    const ratingsLikes = this.props.ratings.filter(this.filterLikes);
    const ratingsDislikes = this.props.ratings.filter(this.filterDislikes);
    const ratingsActive = this.props.ratings.filter(this.filterActive);

    let ratingsDisplay;
    if (this.props.ratings.length > 0){

      let ratings;
      if (this.state.filter === "all"){ratings = this.props.ratings}
      else if (this.state.filter === "active"){ratings = ratingsActive}
      else if (this.state.filter === "dislikes"){ratings = ratingsDislikes}
      else if (this.state.filter === "likes"){ratings = ratingsLikes}

      const ratingsItems = ratings.map((r,index) => (
        <RatingItem
          key={index}
          rating={r}
        />
      ));

      ratingsDisplay = (
        <div className="product-ratings-list comment-list">
          {ratingsItems}
        </div>
      );

    }
    const subMenuItemClassName = " mdl-button mdl-js-button mdl-button--raised mdl-js-ripple-effect";
    const subMenuActiveItemClassName = "active mdl-button--colored mdl-color--primary item"
    return (
      <div id="ratings-tab" className="product-tab">
        <div className="ratings-filters-menu">
          <span className="btn-container" onClick={() => this.setFilter("dislikes")}>
            <a className={this.state.filter === "dislikes" ? subMenuActiveItemClassName + subMenuItemClassName : subMenuItemClassName} onClick={this.showDislikes}>show dislikes ({ratingsDislikes.length})</a>
          </span>
          <span className="btn-container" onClick={() => this.setFilter("likes")}>
            <a onClick={this.setDislikesFilter} className={this.state.filter === "likes" ? subMenuActiveItemClassName + subMenuItemClassName : subMenuItemClassName} onClick={this.showLikes}>show likes ({ratingsLikes.length})</a>
          </span>
          <span className="btn-container" onClick={() => this.setFilter("active")}>
            <a onClick={this.setDislikesFilter} className={this.state.filter === "active" ? subMenuActiveItemClassName + subMenuItemClassName : subMenuItemClassName} onClick={this.showActive}>show active reviews ({ratingsActive.length})</a>
          </span>
          <span className="btn-container" onClick={() => this.setFilter("all")}>
            <a onClick={this.setDislikesFilter} className={this.state.filter === "all" ? subMenuActiveItemClassName + subMenuItemClassName : subMenuItemClassName} onClick={this.showAll}>show all ({this.props.ratings.length})</a>
          </span>
        </div>
        {ratingsDisplay}
      </div>
    )
  }
}

class RatingItem extends React.Component {
  constructor(props){
  	super(props);
  	this.state = {};
  }

  render(){
    return (
      <div className="product-rating-item comment-item">
        <div className="rating-user-avatar comment-user-avatar">
          <img src={this.props.rating.profile_image_url}/>
        </div>
        <div className="rating-item-content comment-item-content">
          <div className="rating-item-header comment-item-header">
            <a href={"/member/"+this.props.rating.member_id}>{this.props.rating.username}</a>
            <span className="comment-created-at">{appHelpers.getTimeAgo(this.props.rating.created_at)}</span>
          </div>
          <div className="rating-item-text comment-item-text">
            {this.props.rating.comment_text}
          </div>
        </div>
      </div>
    )
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
