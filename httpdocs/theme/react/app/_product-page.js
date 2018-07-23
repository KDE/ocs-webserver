class ProductView extends React.Component {
  constructor(props){
  	super(props);
  	this.state = {
      tab:'product',
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
          onDownloadBtnClick={this.toggleDownloadSection}
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
  constructor(props){
  	super(props);
  	this.state = {};
  }

  onUserLike(){
    /*var PartialsButtonHeartDetail = (function () {
        return {
            setup: function () {
                $('body').on('click', '.partialbuttonfollowproject', function (event) {
                    event.preventDefault();
                    var url = $(this).attr("data-href");
                    // data-href - /p/1249209/followproject/
                    var target = $(this).attr("data-target");
                    var auth = $(this).attr("data-auth");
                    var toggle = $(this).data('toggle');
                    var pageFragment = $(this).attr("data-fragment");

                    if (!auth) {
                        $('#like-product-modal').modal('show');
                        return;
                    }

                    // product owner not allow to heart copy from voting....
                    var loginuser = $('#like-product-modal').find('#loginuser').val();
                    var productcreator = $('#like-product-modal').find('#productcreator').val();
                    if (loginuser == productcreator) {
                        // ignore
                        $('#like-product-modal').find('#votelabel').text('Project owner not allowed');
                        $('#like-product-modal').find('.modal-body').empty();
                        $('#like-product-modal').modal('show');
                        return;
                    }

                  var spin = $('<span class="glyphicon glyphicon-refresh spinning" style="opacity: 0.6; z-index:1000;position: absolute; left:24px;top: 4px;"></span>');
                     $(target).prepend(spin);

                    $.ajax({
                              url: url,
                              cache: false
                            })
                          .done(function( response ) {
                            $(target).find('.spinning').remove();
                            if(response.status =='error'){
                                 $(target).html( response.msg );
                            }else{
                                if(response.action=='delete'){
                                    //$(target).find('.likelabel').html(response.cnt +' Likes');
                                    $(target).find('.plingtext').html(response.cnt);
                                    $(target).find('.plingtext').addClass('heartnumberpurple');
                                     $(target).find('.plingheart').removeClass('heartproject').addClass('heartgrey');
                                     $(target).find('.plingheart').removeClass('fa-heart').addClass('fa-heart-o');


                                }else{
                                    //$(target).find('.likelabel').html(response.cnt +' Likes');
                                    $(target).find('.plingtext').html(response.cnt);
                                    //$(target).find('.plingtext').html(response.cnt+' Fans');
                                    $(target).find('.plingtext').removeClass('heartnumberpurple');
                                    $(target).find('.plingheart').removeClass('heartgrey').addClass('heartproject');
                                    $(target).find('.plingheart').removeClass('fa-heart-o').addClass('fa-heart');
                                }
                            }
                          });
                    return false;
                });
            }
        }
    })();*/
  }

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
              <a onClick={this.props.onDownloadBtnClick} href="#" className="mdl-button mdl-js-button mdl-button--colored mdl-button--raised mdl-js-ripple-effect mdl-color--primary">
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
            <a className={this.props.tab === "product" ? "item active" : "item"} onClick={() => this.props.onTabToggle('product')}>Product</a>
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
          product={this.props.product}
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
  	this.state = {};
  }

  /*
  var PartialCommentReviewForm = (function () {
      return {
          setup: function () {
              this.initForm();
          },
          initForm: function () {
              $('body').on("submit", 'form.product-add-comment-review', function (event) {
                  event.preventDefault();
                  event.stopImmediatePropagation();
                  var c = $.trim($('#commenttext').val());
                  if(c.length<1)
                  {
                          if($('#review-product-modal').find('#votelabel').find('.warning').length==0)
                          {
                              $('#review-product-modal').find('#votelabel').append("</br><span class='warning' style='color:red'> Please give a comment, thanks!</span>");
                          }
                          return;
                  }

                  $(this).find(':submit').attr("disabled", "disabled");
                  $(this).find(':submit').css("white-space", "normal");
                  var spin = $('<span class="glyphicon glyphicon-refresh spinning" style="position: relative; left: 0;top: 0px;"></span>');
                  $(this).find(':submit').append(spin);

                  jQuery.ajax({
                      data: $(this).serialize(),
                      url: this.action,
                      type: this.method,
                      error: function (jqXHR, textStatus, errorThrown) {
                          $('#review-product-modal').modal('hide');
                          var msgBox = $('#generic-dialog');
                          msgBox.modal('hide');
                          msgBox.find('.modal-header-text').empty().append('Please try later.');
                          msgBox.find('.modal-body').empty().append("<span class='error'>Service is temporarily unavailable. Our engineers are working quickly to resolve this issue. <br/>Find out why you may have encountered this error.</span>");
                          setTimeout(function () {
                              msgBox.modal('show');
                          }, 900);
                      },
                      success: function (results) {
                          $('#review-product-modal').modal('hide');
                          location.reload();
                      }
                  });
                  return false;
              });
          }
      }
  })();


  var AjaxForm = (function () {
      return {
          setup: function (idElement, idTargetElement) {
              var target = $(idTargetElement);
              $('body').on("submit", 'form.product-add-comment', function (event) {
                  event.preventDefault();
                  event.stopImmediatePropagation();
                  $(this).find('button').attr("disabled", "disabled");
                  $(this).find('.glyphicon.glyphicon-send').removeClass('glyphicon-send').addClass('glyphicon-refresh spinning');

                  jQuery.ajax({
                      data: $(this).serialize(),
                      url: this.action,
                      type: this.method,
                      dataType: "json",

                      error: function (jqXHR, textStatus, errorThrown) {
                          var results = JSON && JSON.parse(jqXHR.responseText) || $.parseJSON(jqXHR.responseText);
                          var msgBox = $('#generic-dialog');
                          msgBox.modal('hide');
                          msgBox.find('.modal-header-text').empty().append(results.title);
                          msgBox.find('.modal-body').empty().append(results.message);
                          setTimeout(function () {
                              msgBox.modal('show');
                          }, 900);
                      },
                      success: function (results) {
                          if (results.status == 'ok') {
                              $(target).empty().html(results.data);
                          }
                          if (results.status == 'error') {
                              if (results.message != '') {
                                  alert(results.message);
                              } else {
                                  alert('Service is temporarily unavailable.');
                              }
                          }
                      }
                  });

                  return false;
              });
          }
      }
  })();


   */

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

    let displayIsCreater;
    if (this.props.comment.member_id === this.props.product.member_id){
      displayIsCreater = <span className="is-creater-display">C</span>
    }

    return(
      <div className="comment-item">
        <div className="comment-user-avatar">
          <img src={this.props.comment.profile_image_url}/>
          {displayIsSupporter}
          {displayIsCreater}
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
      baseUrl = 'cn.pling.com';
      downloadLinkUrlAttr = "cc.pling.com/api/";
    } else {
      baseUrl = 'cn.pling.it';
      downloadLinkUrlAttr = "cc.pling.it/api/";
    }

    const f = this.props.file;
    const fileDownloadHash = appHelpers.generateFileDownloadHash(f,store.getState().env);

    // var downloadUrl = "https://<?= $_SERVER["SERVER_NAME"]?>/p/<?= $this->product->project_id ?>/startdownload?file_id=" + this.id + "&file_name=" + this.name + "&file_type=" + this.type + "&file_size=" + this.size + "&url=" + encodeURIComponent(pploadApiUri + 'files/downloadfile/id/' + this.id + '/s/' + hash + '/t/' + timetamp + '/u/' + userid + '/' + this.name);
    // var downloadLink = '<a href="' + downloadUrl + '" id="data-link' + this.id + '">' + this.name + '</a>';

    let downloadLink = "https://"+baseUrl+
                       "/p/"+this.props.product.project_id+
                       "/startdownload?file_id="+f.id+
                       "&file_name="+f.title+
                       "&file_type="+f.type+
                       "&file_size="+f.size+
                       "&url="+downloadLinkUrlAttr+
                       "files/downloadfile/id/"+f.id+
                       "/s/"+f.hash+
                       "/t/"+f.created_timestamp+
                       "/u/"+this.props.product.member_id+
                       "/"+f.title;


    /*https://david.pling.cc/p/747/startdownload?file_id=1519124607&amp;
    file_name=1519124607-download-app-old.png&amp;
    file_type=image/png&amp;
    file_size=21383&amp;
    url=https%3A%2F%2Fcc.ppload.com%2Fapi%2Ffiles%2Fdownloadfile%2Fid
    %2F1519124607%2Fs
    %2Fd66c71127c9aae29e58e03ddd85de57a%2Ft
    %2F1532003618%2Fu
    %2F%2F1519124607-download-app-old.png
    */
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
        <div className="favs-list cards">{favs}</div>
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
        <div className="plings-list cards">{plings}</div>
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
      <div className="user-card-item">
        <div className="card-content">
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
