class ProductView extends React.Component {
  constructor(props){
  	super(props);
  	this.state = {
      tab:'comments',
      showDownloadSection:false
    };
    this.toggleTab = this.toggleTab.bind(this);
    this.toggleDownloadSection = this.toggleDownloadSection.bind(this);
  }

  componentDidMount() {
    let downloadTableHeight = $('#product-download-section').find('#files-tab').height();
    downloadTableHeight += 80;
    this.setState({downloadTableHeight:downloadTableHeight});
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

  toggleDownloadSection(){
    let showDownloadSection = this.state.showDownloadSection === true ? false : true;
    this.setState({showDownloadSection:showDownloadSection});
  }

  render(){

    let productGalleryDisplay;
    if (this.props.product.r_gallery.length > 0){
      productGalleryDisplay = (
        <ProductViewGallery
          product={this.props.product}
        />
      );
    }

    let productGalleryLightboxDisplay;
    if (this.props.lightboxGallery.show === true){
      productGalleryLightboxDisplay = (
        <ProductGalleryLightbox
          product={this.props.product}
        />
      );
    }

    let downloadSectionDisplayHeight;
    if (this.state.showDownloadSection === true){
      downloadSectionDisplayHeight = this.state.downloadTableHeight;
    }

    return(
      <div id="product-page">
        <div id="product-download-section" style={{"height":downloadSectionDisplayHeight}}>
          <ProductViewFilesTab
            product={this.props.product}
            files={this.props.product.r_files}
          />
        </div>
        <ProductViewHeader
          product={this.props.product}
          user={this.props.user}
          onDownloadBtnClick={this.toggleDownloadSection}
        />
        {productGalleryDisplay}
        <ProductDescription
          product={this.props.product}
        />
        <ProductNavBar
          onTabToggle={this.toggleTab}
          tab={this.state.tab}
          product={this.props.product}
        />
        <ProductViewContent
          product={this.props.product}
          user={this.props.user}
          tab={this.state.tab}
        />
        {productGalleryLightboxDisplay}
      </div>
    );
  }
}

const mapStateToProductPageProps = (state) => {
  const product = state.product;
  const user = state.user;
  const lightboxGallery = state.lightboxGallery;
  return {
    product,
    user,
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
  constructor(props){
  	super(props);
  	this.state = {};
  }

  render(){

    let imageBaseUrl;
    if (store.getState().env === 'live') {
      imageBaseUrl = 'cn.opendesktop.org';
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
            <div className="product-view-header-left">
              <figure className="image-container">
                <img src={'https://' + imageBaseUrl + '/cache/140x140/img/' + this.props.product.image_small} />
              </figure>
              <div className="product-info">
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
              </div>
            </div>
            <div className="product-view-header-right">
              <div className="details-container">
                <a onClick={this.props.onDownloadBtnClick} href="#" className="mdl-button mdl-js-button mdl-button--colored mdl-button--raised mdl-js-ripple-effect mdl-color--primary">
                  Download
                </a>
                <ProductViewHeaderLikes
                  product={this.props.product}
                  user={this.props.user}
                />
                <div id="product-view-header-right-side">
                  <ProductViewHeaderRatings
                    product={this.props.product}
                    user={this.props.user}
                  />
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    );
  }
}

class ProductViewHeaderLikes extends React.Component {
  constructor(props){
  	super(props);
  	this.state = {};
    this.onUserLike = this.onUserLike.bind(this);
  }

  componentDidMount() {
    const user = store.getState().user;
    const likedByUser = productHelpers.checkIfLikedByUser(user,this.props.product.r_likes);
    this.setState({likesTotal:this.props.product.r_likes.length,likedByUser:likedByUser});
  }

  onUserLike(){
    if (this.props.user.username){
      const url = "/p/"+this.props.product.project_id+"/followproject/";
      const self = this;
      $.ajax({url: url,cache: false}).done(function(response){
        // error
        if (response.status === "error"){
          self.setState({msg:response.msg});
        } else {
          // delete
          if (response.action === "delete"){
            const likesTotal = self.state.likesTotal - 1;
            self.setState({likesTotal:likesTotal,likedByUser:false});
          }
          // insert
          else {
            const likesTotal = self.state.likesTotal + 1;
            self.setState({likesTotal:likesTotal,likedByUser:true});
          }
        }
      });
    } else {
      this.setState({msg:'please login to like'});
    }
  }

  render(){
    let cssContainerClass, cssHeartClass;
    if (this.state.likedByUser === true){
      cssContainerClass = "liked-by-user";
      cssHeartClass = "plingheart fa heartproject fa-heart";
    } else {
      cssHeartClass = "plingheart fa fa-heart-o heartgrey";
    }

    return (
      <div className={cssContainerClass} id="likes-container">
        <div className="likes">
          <i className={cssHeartClass}></i>
          <span onClick={this.onUserLike}>{this.state.likesTotal}</span>
        </div>
        <div className="likes-label-container">{this.state.msg}</div>
      </div>
    );
  }
}

class ProductViewHeaderRatings extends React.Component {
  constructor(props){
  	super(props);
  	this.state = {
      userIsOwner:'',
      action:'',
      laplace_score:this.props.product.laplace_score
    };
    this.onRatingFormResponse = this.onRatingFormResponse.bind(this);
  }

  componentDidMount() {

    let userIsOwner = false;
    if (this.props.user && this.props.user.member_id === this.props.product.member_id){
      userIsOwner = true;
    }
    let userRating = -1;
    if (userIsOwner === false){
      userRating = productHelpers.getLoggedUserRatingOnProduct(this.props.user,this.props.product.r_ratings);
    }
    this.setState({userIsOwner:userIsOwner,userRating:userRating});
  }

  onRatingBtnClick(action){
    this.setState({showModal:false},function(){
      this.setState({action:action,showModal:true},function(){
        $('#ratings-form-modal').modal('show');
      });
    });
  }

  onRatingFormResponse(modalResponse,val){
    const self = this;
    this.setState({errorMsg:''},function(){
      jQuery.ajax({
        data:{},
        url:'/p/'+this.props.product.project_id+'/loadratings/',
        method:'get',
        error:function(jqXHR,textStatus,errorThrown){
          self.setState({errorMsg:textStatus + " " + errorThrown});
          $('#ratings-form-modal').modal('hide');
        },
        success: function(response){
          // const laplace_score = productHelpers.calculateProductLaplaceScore(response);
          store.dispatch(setProductRatings(response));
          if (modalResponse.status !== "ok") self.setState({errorMsg:modalResponse.status + " - " + modalResponse.message});
          self.setState({laplace_score:modalResponse.laplace_score},function(){

          });
          $('#ratings-form-modal').modal('hide');
        }
      });
    });
  }

  render(){

    let ratingsFormModalDisplay;
    if (this.state.showModal === true){
      if (this.props.user.username){
        ratingsFormModalDisplay = (
          <RatingsFormModal
            user={this.props.user}
            userIsOwner={this.state.userIsOwner}
            userRating={this.state.userRating}
            action={this.state.action}
            product={this.props.product}
            onRatingFormResponse={this.onRatingFormResponse}
          />
        );
      } else {
        ratingsFormModalDisplay = (
          <div className="modal please-login" id="ratings-form-modal" tabIndex="-1" role="dialog">
            <div className="modal-dialog" role="document">
              <div className="modal-content">
                <div className="modal-header">
                  <h4 className="modal-title">Please Login</h4>
                  <button type="button" id="review-modal-close" className="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                  </button>
                </div>
                <div className="modal-body">
                  <a href="/login/">Login</a>
                </div>
              </div>
            </div>
          </div>
        );
      }
    }

    return (
      <div className="ratings-bar-container">
        <div className="ratings-bar-left" onClick={() => this.onRatingBtnClick('minus')}>
          <i className="material-icons">remove</i>
        </div>
        <div className="ratings-bar-holder">
          <div className="green ratings-bar" style={{"width":this.state.laplace_score + "%"}}></div>
          <div className="ratings-bar-empty" style={{"width":(100 - this.state.laplace_score) + "%"}}></div>
        </div>
        <div className="ratings-bar-right" onClick={() => this.onRatingBtnClick('plus')}>
          <i className="material-icons">add</i>
        </div>
        {ratingsFormModalDisplay}
        <p className="ratings-bar-error-msg-container">{this.state.errorMsg}</p>
      </div>
    )
  }
}

class RatingsFormModal extends React.Component {
  constructor(props){
  	super(props);
  	this.state = {
      action:this.props.action
    };
    this.submitRatingForm = this.submitRatingForm.bind(this);
    this.onTextAreaInputChange = this.onTextAreaInputChange.bind(this);
  }

  componentDidMount() {
    let actionIcon;
    if (this.props.action === 'plus'){
      actionIcon = '+';
    } else if (this.props.action === 'minus') {
      actionIcon = '-';
    }
    this.setState({action:this.props.action,actionIcon:actionIcon,text:actionIcon},function(){
      this.forceUpdate();
    });
  }

  onTextAreaInputChange(e){
    this.setState({text:e.target.value});
  }

  submitRatingForm(){
    this.setState({loading:true},function(){
      const self = this;
      let v;
      if (this.state.action === 'plus'){
        v = '1';
      } else {
        v = '2';
      }

      jQuery.ajax({
        data:{
          p:this.props.product.project_id,
          m:this.props.user.member_id,
          v:v,
          pm:this.props.product.member_id,
          otxt:this.state.text,
          userrate:this.props.userRating,
          msg:this.state.text
        },
        url:'/productcomment/addreplyreview/',
        method:'post',
        error: function(){
          const msg = "Service is temporarily unavailable. Our engineers are working quickly to resolve this issue. <br/>Find out why you may have encountered this error.";
          self.setState({msg:msg});
        },
        success: function(response){
          self.props.onRatingFormResponse(response,v);
        }
      });
    });
  }

  render(){
    let textAreaDisplay, modalBtnDisplay;
    if (!this.props.user){
      textAreaDisplay = (
        <p>Please login to comment</p>
      );
      modalBtnDisplay = (
        <button type="button" className="btn btn-secondary" data-dismiss="modal">Close</button>
      );
    } else {
      if (this.props.userIsOwner){
        textAreaDisplay = (
          <p>Project owner not allowed</p>
        );
        modalBtnDisplay = (
          <button type="button" className="btn btn-secondary" data-dismiss="modal">Close</button>
        );
      } else if (this.state.text) {
        textAreaDisplay = (
          <textarea onChange={this.onTextAreaInputChange} defaultValue={this.state.text} className="form-control"></textarea>
        );
        if (this.state.loading !== true){

          if (this.state.msg){
            modalBtnDisplay = (
              <p>{this.state.msg}</p>
            )
          } else {
            modalBtnDisplay = (
              <button onClick={this.submitRatingForm} type="button" className="btn btn-primary">Rate Now</button>
            );
          }

        } else {
          modalBtnDisplay = (
            <span className="glyphicon glyphicon-refresh spinning"></span>
          );
        }

      }
    }

    return (
      <div className="modal" id="ratings-form-modal" tabIndex="-1" role="dialog">
        <div className="modal-dialog" role="document">
          <div className="modal-content">
            <div className="modal-header">
              <div className={this.props.action + " action-icon-container"}>
                {this.state.actionIcon}
              </div>
              <h5 className="modal-title">Add Comment (min. 1 char):</h5>
              <button type="button" id="review-modal-close" className="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
              </button>
            </div>
            <div className="modal-body">
              {textAreaDisplay}
            </div>
            <div className="modal-footer">
              {modalBtnDisplay}
            </div>
          </div>
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

    let galleryDisplay;

    if (this.props.product.embed_code && this.props.product.embed_code.length > 0){

      let imageBaseUrl;
      if (store.getState().env === 'live') {
        imageBaseUrl = 'http://cn.opendesktop.org';
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

        galleryDisplay = (
          <div id="product-gallery">
            <a className="gallery-arrow arrow-left" onClick={this.onLeftArrowClick}>
              <i className="material-icons">chevron_left</i>
            </a>
            <div className="section">
              <div style={{"width":this.state.itemsWidth * this.state.itemsTotal +"px","marginLeft":this.state.galleryWrapperMarginLeft}} className="gallery-items-wrapper">
                <div onClick={() => this.onGalleryItemClick(1)} dangerouslySetInnerHTML={{__html:this.props.product.embed_code}} className={this.state.currentItem === 1 ? "active-gallery-item gallery-item" : "gallery-item"}>
                </div>
                {moreItems}
              </div>
            </div>
            <a className="gallery-arrow arrow-right" onClick={this.onRightArrowClick}>
              <i className="material-icons">chevron_right</i>
            </a>
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

  togglePrevGalleryItem(){
    let nextItem;
    if (this.state.currentItem <= 1){
      nextItem = this.state.itemsTotal;
    } else {
      nextItem = this.state.currentItem - 1;
    }

    this.animateGallerySlider(nextItem);
  }

  toggleNextGalleryItem(){
    let nextItem;
    if (this.state.currentItem === this.state.itemsTotal){
      nextItem = 1;
    } else {
      nextItem = this.state.currentItem + 1;
    }
    this.animateGallerySlider(nextItem);
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
      imageBaseUrl = 'http://cn.opendesktop.org';
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
          <a className="gallery-arrow" onClick={this.togglePrevGalleryItem} id="arrow-left">
            <i className="material-icons">chevron_left</i>
          </a>
          <div className="current-gallery-item">
            {mainItemDisplay}
          </div>
          <a className="gallery-arrow" onClick={this.toggleNextGalleryItem} id="arrow-right">
            <i className="material-icons">chevron_right</i>
          </a>
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

class ProductDescription extends React.Component {
  constructor(props){
  	super(props);
  	this.state = {};
  }
  render(){
    return (
      <div id="product-description" className="section">
        <div className="container">
          <div className="main-content">
            <article>
              <p dangerouslySetInnerHTML={{__html:this.props.product.description}}></p>
            </article>
            <aside>
              <ul>
                <li><span className="key">License</span><span className="val">{this.props.product.project_license_title}</span></li>
                <li><span className="key">Last Update</span><span className="val">{this.props.product.changed_at.split(' ')[0]}</span></li>
              </ul>
            </aside>
          </div>
        </div>
      </div>
    )
  }
}

class ProductNavBar extends React.Component {
  render(){
    let productNavBarDisplay;
    let filesMenuItem, ratingsMenuItem, favsMenuItem, plingsMenuItem;
    if (this.props.product.r_files.length > 0 ){
      filesMenuItem = <a className={this.props.tab === "files" ? "item active" : "item"} onClick={() => this.props.onTabToggle('files')}>Files ({this.props.product.r_files.length})</a>
    }
    if (this.props.product.r_ratings.length > 0) {
      const activeRatingsNumber = productHelpers.getActiveRatingsNumber(this.props.product.r_ratings);
      ratingsMenuItem = <a className={this.props.tab === "ratings" ? "item active" : "item"} onClick={() => this.props.onTabToggle('ratings')}>Ratings & Reviews ({activeRatingsNumber})</a>
    }
    if (this.props.product.r_likes.length > 0) {
      favsMenuItem = <a className={this.props.tab === "favs" ? "item active" : "item"} onClick={() => this.props.onTabToggle('favs')}>Favs ({this.props.product.r_likes.length})</a>
    }
    if (this.props.product.r_plings.length > 0){
      plingsMenuItem = <a className={this.props.tab === "plings" ? "item active" : "item"} onClick={() => this.props.onTabToggle('plings')}>Plings ({this.props.product.r_plings.length})</a>
    }
    return (
      <div className="wrapper">
        <div className="container">
          <div className="explore-top-bar">
            <a className={this.props.tab === "comments" ? "item active" : "item"} onClick={() => this.props.onTabToggle('comments')}>Comments ({this.props.product.r_comments.length})</a>
            {filesMenuItem}
            {ratingsMenuItem}
            {favsMenuItem}
            {plingsMenuItem}
          </div>
        </div>
      </div>
    )
  }
}

class ProductViewContent extends React.Component {
  render(){

    let currentTabDisplay;
    if (this.props.tab === 'comments'){
      currentTabDisplay = (
        <div className="product-tab" id="comments-tab">
          <ProductCommentsContainer
            product={this.props.product}
            user={this.props.user}
          />
        </div>
      );
    } else if (this.props.tab === 'files'){
      currentTabDisplay = (
        <ProductViewFilesTab
          product={this.props.product}
          files={this.props.product.r_files}
        />
      )
    } else if (this.props.tab === 'ratings'){
      currentTabDisplay = (
        <ProductViewRatingsTabWrapper
          ratings={this.props.product.r_ratings}
        />
      );
    } else if (this.props.tab === 'favs'){
      currentTabDisplay = (
        <ProductViewFavTab
          likes={this.props.product.r_likes}
        />
      );
    } else if (this.props.tab === 'plings'){
      currentTabDisplay = (
        <ProductViewPlingsTab
          plings={this.props.product.r_plings}
        />
      );
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

class ProductCommentsContainer extends React.Component {
  constructor(props){
  	super(props);
    this.state = {}
  }

  render(){
    let commentsDisplay;
    const cArray = categoryHelpers.convertCatChildrenObjectToArray(this.props.product.r_comments);
    if (cArray.length > 0){
      const product = this.props.product;
      const comments = cArray.map((c,index) => {
        if (c.level === 1){
          return (
            <CommentItem user={this.props.user} product={product} comment={c.comment} key={index} level={1}/>
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
        <CommentForm
          user={this.props.user}
          product={this.props.product}
        />
        {commentsDisplay}
      </div>
    )
  }
}

class CommentForm extends React.Component {
  constructor(props){
  	super(props);
    this.state = {
      text:'',
      errorMsg:'',
      errorTitle:'',
      loading:false
    };
    this.updateCommentText = this.updateCommentText.bind(this);
    this.submitComment = this.submitComment.bind(this);
    this.updateComments = this.updateComments.bind(this);
  }

  updateCommentText(e){
    this.setState({text:e.target.value});
  }

  submitComment(){
    this.setState({loading:true},function(){
      const msg = this.state.text;
      const self = this;
      let data = {
        p:this.props.product.project_id,
        m:this.props.user.member_id,
        msg:this.state.text
      }
      if (this.props.comment){
        data.i = this.props.comment.comment_id;
      }
      jQuery.ajax({
        data:data,
        url:'/productcomment/addreply/',
        type:'post',
        dataType:'json',
        error:function(jqXHR,textStatus,errorThrown){
          const results = JSON && JSON.parse(jqXHR.responseText) || $.parseJSON(jqXHR.responseText);
          self.setState({
              errorMsg:results.message,
              errorTitle:results.title,
              login_url:results.login_url,
              status:'error'});
        },
        success:function(results){
          let baseUrl;
          if (store.getState().env === 'live') {
            baseUrl = 'cn.opendesktop.org';
          } else {
            baseUrl = 'cn.pling.it';
          }
          $.ajax({url: '/productcomment?p='+self.props.product.project_id,cache: false}).done(function(response){
            self.updateComments(response);
          });
        }
      });
    });
  }

  updateComments(response){
    store.dispatch(setProductComments(response));
    this.setState({text:'',loading:false},function(){
      if (this.props.hideReplyForm){
        this.props.hideReplyForm();
      }
    });
  }

  render(){

    let commentFormDisplay;
    if (this.props.user.username){
      if (this.state.loading){
        commentFormDisplay = (
          <div className="comment-form-container">
            <p><span className="glyphicon glyphicon-refresh spinning"></span> posting comment</p>
          </div>
        );
      } else {
        let submitBtnDisplay;
        if (this.state.text.length === 0){
          submitBtnDisplay = (
            <button disabled="disabled" type="button" className="mdl-button mdl-js-button mdl-button--raised mdl-js-ripple-effect mdl-button--colored mdl-color--primary">
              send
            </button>
          );
        } else {
          submitBtnDisplay = (
            <button onClick={this.submitComment} type="button" className="mdl-button mdl-js-button mdl-button--raised mdl-js-ripple-effect mdl-button--colored mdl-color--primary">
              <span className="glyphicon glyphicon-send"></span>
              send
            </button>
          );
        }

        let errorDisplay;
        if (this.state.status === 'error'){
          errorDisplay = (
            <div className="comment-form-error-display-container">
              <div dangerouslySetInnerHTML={{__html:this.state.errorTitle}}></div>
              <div dangerouslySetInnerHTML={{__html:this.state.errorMsg}}></div>
            </div>
          )
        }

        commentFormDisplay = (
          <div className="comment-form-container">
            <span>Add Comment</span>
            <textarea className="form-control" onChange={this.updateCommentText} value={this.state.text}></textarea>
            {errorDisplay}
            {submitBtnDisplay}
          </div>
        );
      }
    } else {
      commentFormDisplay = (
        <p>Please <a href="/login?redirect=ohWn43n4SbmJZWlKUZNl2i1_s5gggiCE">login</a> or <a href="/register">register</a> to add a comment</p>
      );
    }


    return (
      <div id="product-page-comment-form-container">
        {commentFormDisplay}
      </div>
    )
  }
}

class CommentItem extends React.Component {
  constructor(props){
  	super(props);
  	this.state = {
      showCommentReplyForm:false
    };
    this.filterByCommentLevel = this.filterByCommentLevel.bind(this);
    this.onToggleReplyForm = this.onToggleReplyForm.bind(this);
    this.onReportComment = this.onReportComment.bind(this);
    this.onConfirmReportClick = this.onConfirmReportClick.bind(this);
  }

  filterByCommentLevel(val){
    if (val.level > this.props.level && this.props.comment.comment_id === val.comment.comment_parent_id){
      return val;
    }
  }

  onToggleReplyForm(){
    const showCommentReplyForm = this.state.showCommentReplyForm === true ? false : true;
    this.setState({showCommentReplyForm:showCommentReplyForm});
  }

  onReportComment(){
    $('#report-'+this.props.comment.comment_id).modal('show');
  }

  onConfirmReportClick(commentId,productId){
    jQuery.ajax({
        data: {
          i:commentId,
          p:productId
        },
        url: "/report/comment/",
        type: "POST",
        dataType: "json",
        error: function (jqXHR, textStatus, errorThrown) {
            var results = JSON && JSON.parse(jqXHR.responseText) || $.parseJSON(jqXHR.responseText);
            $("#report-"+commentId).find('.modal-header-text').empty().append(results.title);
            $("#report-"+commentId).find('.modal-body').empty().append(results.message);
            setTimeout(function () {
                $("#report-"+commentId).modal('hide');
            }, 2000);
        },
        success: function (results) {
          if (results.status == 'ok') {
            $("#report-"+commentId).find(".comment-report-p").empty().html(results.message.split('</p>')[0].split('<p>')[1]);
          }
          if (results.status == 'error') {
            if (results.message != '') {
              $("#report-"+commentId).find(".comment-report-p").empty().html(results.message);
            } else {
              $("#report-"+commentId).find(".comment-report-p").empty().html('Service is temporarily unavailable.');
            }
          }
          setTimeout(function () {
              $("#report-"+commentId).modal('hide');
          }, 2000);
        }
    });
  }

  render(){
    let commentRepliesContainer;
    const filteredComments = categoryHelpers.convertCatChildrenObjectToArray(this.props.product.r_comments).filter(this.filterByCommentLevel);
    if (filteredComments.length > 0){
      const product = this.props.product;
      const user = this.props.user;
      const comments = filteredComments.map((c,index) => (
        <CommentItem user={user} product={product} comment={c.comment} key={index} level={c.level}/>
      ));
      commentRepliesContainer = (
        <div className="comment-item-replies-container">
          {comments}
        </div>
      );
    }

    let displayIsSupporter;
    if (this.props.comment.issupporter === "1"){
      displayIsSupporter = (
        <li>
          <span className="is-supporter-display uc-icon">S</span>
        </li>
      );
    }

    let displayIsCreater;
    if (this.props.comment.member_id === this.props.product.member_id){
      displayIsCreater = (
        <li>
          <span className="is-creater-display uc-icon">C</span>
        </li>
      );
    }

    let commentReplyFormDisplay;
    if (this.state.showCommentReplyForm){
      commentReplyFormDisplay = (
        <CommentForm
          comment={this.props.comment}
          user={this.props.user}
          product={this.props.product}
          hideReplyForm={this.onToggleReplyForm}
        />
      );
    }

    return(
      <div className="comment-item">
        <div className="comment-user-avatar">
          <img src={this.props.comment.profile_image_url}/>
        </div>
        <div className="comment-item-content">
          <div className="comment-item-header">
            <ul>
              <li>
                <a className="comment-username" href={"/member/"+this.props.comment.member_id}>{this.props.comment.username}</a>
              </li>
              {displayIsSupporter}
              {displayIsCreater}
              <li>
                <span className="comment-created-at">
                  {appHelpers.getTimeAgo(this.props.comment.comment_created_at)}
                </span>
              </li>
            </ul>
          </div>
          <div className="comment-item-text">
            {this.props.comment.comment_text}
          </div>
          <div className="comment-item-actions">
            <a onClick={this.onToggleReplyForm}>
              <i className="material-icons reverse">reply</i>
              <span>Reply</span>
            </a>
            <a onClick={this.onReportComment}>
              <i className="material-icons">warning</i>
              <span>Report</span>
            </a>
            <ReportCommentModal
              comment={this.props.comment}
              product={this.props.product}
              user={this.props.user}
              onConfirmReportClick={this.onConfirmReportClick}
            />
          </div>
        </div>
        {commentReplyFormDisplay}
        {commentRepliesContainer}
      </div>
    );
  }
}

class ReportCommentModal extends React.Component {
  constructor(props){
  	super(props);
  	this.state = {
      status:"ready"
    };
  }

  onConfirmReportClick(commmentId,productId){
    this.setState({status:"loading"},function(){
      this.props.onConfirmReportClick(commmentId,productId);
    });
  }

  render(){
    let confirmActionButtonIconDisplay;
    if (this.state.status === "ready"){
      confirmActionButtonIconDisplay = (<i className="material-icons reverse">reply</i>);
    } else if (this.state.status === "loading"){
      confirmActionButtonIconDisplay = (<span className="glyphicon glyphicon-refresh spinning"></span>);
    }

    return (
      <div className="modal report-comment-modal" id={"report-"+this.props.comment.comment_id} tabIndex="-1" role="dialog">
        <div className="modal-dialog" role="document">
          <div className="modal-content">
            <div className="modal-header">
              <h4 className="modal-title">Report Comment</h4>
              <button type="button" id="review-modal-close" className="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
              </button>
            </div>
            <div className="modal-body">
              <p className="comment-report-p">Do you really want to report this comment?</p>
            </div>
            <div className="modal-footer">
              <a onClick={() => this.onConfirmReportClick(this.props.comment.comment_id,this.props.product.project_id)}>
                {confirmActionButtonIconDisplay} yes
              </a>
            </div>
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
      <ProductViewFilesTabItem
        product={this.props.product}
        key={index}
        file={f}
      />
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

class ProductViewFilesTabItem extends React.Component {
  constructor(props){
  	super(props);
  	this.state = {downloadLink:""};
  }

  componentDidMount() {
    let baseUrl, downloadLinkUrlAttr;
    if (store.getState().env === 'live') {
      baseUrl = 'opendesktop.org';
      downloadLinkUrlAttr = "https%3A%2F%dl.opendesktop.org%2Fapi%2F";
    } else {
      baseUrl = 'pling.cc';
      downloadLinkUrlAttr = "https%3A%2F%2Fcc.ppload.com%2Fapi%2F";
    }

    const f = this.props.file;
    const timestamp =  Math.floor((new Date().getTime() / 1000)+3600)
    const fileDownloadHash = appHelpers.generateFileDownloadHash(f,store.getState().env);
    let downloadLink = "https://"+baseUrl+
                       "/p/"+this.props.product.project_id+
                       "/startdownload?file_id="+f.id+
                       "&file_name="+f.title+
                       "&file_type="+f.type+
                       "&file_size="+f.size+
                       "&url="+downloadLinkUrlAttr+
                       "files%2Fdownload%2Fid%2F"+f.id+
                       "%2Fs%2F"+fileDownloadHash+
                       "%2Ft%2F"+timestamp+
                       "%2Fu%2F"+this.props.product.member_id+
                       "%2F"+f.title;

    this.setState({downloadLink:downloadLink});
  }

  render(){
    const f = this.props.file;
    return (
      <tr>
        <td className="mdl-data-table__cell--non-numericm">
          <a href={this.state.downloadLink}>{f.title}</a>
        </td>
        <td>{f.version}</td>
        <td className="mdl-data-table__cell--non-numericm">{f.description}</td>
        <td className="mdl-data-table__cell--non-numericm">{f.packagename}</td>
        <td  className="mdl-data-table__cell--non-numericm">{f.archname}</td>
        <td>{f.downloaded_count}</td>
        <td className="mdl-data-table__cell--non-numericm">{appHelpers.getTimeAgo(f.created_timestamp)}</td>
        <td className="mdl-data-table__cell--non-numericm">{appHelpers.getFileSize(f.size)}</td>
        <td><a href={this.state.downloadLink}><i className="material-icons">cloud_download</i></a></td>
        <td>{f.ocs_compatible}</td>
      </tr>
    )
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

const mapStateToProductViewRatingsTabProps = (state) => {
  const ratings = state.product.r_ratings;
  return {
    ratings
  }
}

const mapDispatchToProductViewRatingsTabProps = (dispatch) => {
  return {
    dispatch
  }
}

const ProductViewRatingsTabWrapper = ReactRedux.connect(
  mapStateToProductViewRatingsTabProps,
  mapDispatchToProductViewRatingsTabProps
)(ProductViewRatingsTab);

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

class ProductViewFavTab extends React.Component {
  constructor(props){
  	super(props);
  	this.state = {};
  }
  render(){
    let favsDisplay;
    if (this.props.likes){
      const favs = this.props.likes.map((like,index) => (
        <UserCardItem
          key={index}
          like={like}
        />
      ));
      favsDisplay = (
        <div className="favs-list supporter-list">{favs}</div>
      );
    }
    return (
      <div className="product-tab" id="fav-tab">
        {favsDisplay}
      </div>
    )
  }
}

class ProductViewPlingsTab extends React.Component {
  constructor(props){
  	super(props);
  	this.state = {};
  }
  render(){
    let plingsDisplay;
    if (this.props.plings){
      const plings = this.props.plings.map((pling,index) => (
        <UserCardItem
          key={index}
          pling={pling}
        />
      ));
      plingsDisplay = (
        <div className="plings-list supporter-list">{plings}</div>
      );
    }
    return (
      <div className="product-tab" id="plings-tab">
        {plingsDisplay}
      </div>
    )
  }
}

class UserCardItem extends React.Component {
  constructor(props){
  	super(props);
  	this.state = {};
  }
  render(){
    let item;
    if (this.props.like){
      item = this.props.like;
    } else if (this.props.pling){
      item = this.props.pling;
    }

    let cardTypeDisplay;
    if (this.props.like){
      cardTypeDisplay = (
        <i className="fa fa-heart myfav" aria-hidden="true"></i>
      );
    } else if (this.props.pling){
      cardTypeDisplay = (
        <img src="/images/system/pling-btn-active.png"/>
      );
    }

    return (
      <div className="supporter-list-item">
        <div className="item-content">
          <div className="user-avatar">
            <img src={item.profile_image_url}/>
          </div>
          <span className="username"><a href={"/member/"+item.member_id}>{item.username}</a></span>
          <span className="card-type-holder">
            {cardTypeDisplay}
          </span>
          <span className="created-at">
            {appHelpers.getTimeAgo(item.created_at)}
          </span>
        </div>
      </div>
    )
  }
}
