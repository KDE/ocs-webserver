window.hpHelpers = (function(){

  function dechex(number) {
    //  discuss at: http://locutus.io/php/dechex/
    // original by: Philippe Baumann
    // bugfixed by: Onno Marsman (https://twitter.com/onnomarsman)
    // improved by: http://stackoverflow.com/questions/57803/how-to-convert-decimal-to-hex-in-javascript
    //    input by: pilus
    //   example 1: dechex(10)
    //   returns 1: 'a'
    //   example 2: dechex(47)
    //   returns 2: '2f'
    //   example 3: dechex(-1415723993)
    //   returns 3: 'ab9dc427'

    if (number < 0) {
      number = 0xFFFFFFFF + number + 1
    }
    return parseInt(number, 10).toString(16)
  }

  function calculateScoreColor(score){
    let blue, red, green, defaultColor = 200;
    if (score > 50){
      red = defaultColor - ((score-50)*4);
      green = defaultColor;
      blue = defaultColor - ((score-50)*4);
    } else if (score < 51){
      red = defaultColor;
      green = defaultColor - ((score-50)*4);
      blue = defaultColor - ((score-50)*4);
    }

    return "rgb("+red+","+green+","+blue+")";
  }

  return {
    dechex,
    calculateScoreColor
  }
}());

class App extends React.Component {
  constructor(props){
  	super(props);
  	this.state = {
      loading:true,
      hpVersion:window.hpVersion
    };
    this.initHomePage = this.initHomePage.bind(this);
    this.updateDimensions = this.updateDimensions.bind(this);
    this.convertDataObject = this.convertDataObject.bind(this);
  }


  componentWillMount() {
    this.updateDimensions();
  }

  componentWillUnmount(){
    window.removeEventListener("resize", this.updateDimensions);
    window.removeEventListener("orientationchange",this.updateDimensions);
  }

  componentDidMount() {
    this.initHomePage();
  }

  initHomePage(){

    window.addEventListener("resize", this.updateDimensions);
    window.addEventListener("orientationchange",this.updateDimensions);

    let env = "live";
    if (location.hostname.endsWith('cc')) {
      env = "test";
    } else if (location.hostname.endsWith('localhost')) {
      env = "test";
    }

    this.setState({env:env},function(){
      this.convertDataObject();
    });

  }

  updateDimensions(){

    const width = window.innerWidth;
    let device;
    if (width >= 910){
      device = "large";
    } else if (width < 910 && width >= 610){
      device = "mid";
    } else if (width < 610){
      device = "tablet";
    }

    this.setState({device:device});

  }

  convertDataObject() {
    let productGroupsArray = [];
    for (var i in window.data) {
      if (i !== "comments" && i !== "featureProducts"){
        const productGroup = {
          title:window.data[i].title,
          catIds:window.data[i].catIds,
          products:JSON.parse(window.data[i].products)
        }
        productGroupsArray.push(productGroup);
      }
    }
    this.setState({productGroupsArray:productGroupsArray,loading:false});
  }

  render(){
    let productCarouselsContainer;
    if (this.state.loading === false){

      productCarouselsContainer = this.state.productGroupsArray.map((pgc,index) => (
          <div key={index} className="section">
            <div className="container">
              <ProductCarousel
                products={pgc.products}
                device={this.state.device}
                title={pgc.title}
                catIds={pgc.catIds}
                link={'/'}
                env={this.state.env}
              />
            </div>
          </div>
      ));
    }

    const featuredProduct = JSON.parse(window.data['featureProducts']);

    return (
      <main id="opendesktop-homepage">
        <SpotlightUser />
        {productCarouselsContainer}
      </main>
    )
  }
}

class SpotlightUser extends React.Component {
  constructor(props){
  	super(props);
  	this.state = {
      page:1,
      loading:true,
      version:1
    };
    this.getSpotlightUser = this.getSpotlightUser.bind(this);
    this.getNextSpotLightUser = this.getNextSpotLightUser.bind(this);
  }

  componentDidMount() {
    this.getSpotlightUser();
  }

  getSpotlightUser(){
    const self = this;
    $.ajax({url: "/home/showspotlightjson?page="+this.state.page,cache: false}).done(function(response){
      console.log(response);
      self.setState({user:response,loading:false});
    });
  }

  getNextSpotLightUser(){
    const page = this.state.page + 1;
    this.setState({page:page,loading:true},function(){
      this.getSpotlightUser();
    })
  }

  render(){
    let spotlightUserDisplay;
    if (this.state.loading){
      spotlightUserDisplay = (
        <div id="spotlight-user" className="loading">
          <div className="ajax-loader"></div>
        </div>
      );
    } else {
      const users = this.state.user.products.map((p,index) => (
        <div key={index} className="plinged-product">
          <div className="product-wrapper">
            <figure>
              <img src={p.image_small}/>
            </figure>
            <div className="product-info">
              <span className="title">
                {p.title}
              </span>
            </div>
          </div>
        </div>
      ));

      let productsContainerCssClass;
      if (this.state.user.products.length === 2){
        productsContainerCssClass = "one-row";
      } else if (this.state.user.product.length === 1){
        productsContainerCssClass = "one-row single-product";
      }

      spotlightUserDisplay = (
        <div id="spotlight-user">
          <div className="spotlight-user-image">
            <figure>
              <img src={this.state.user.profile_image_url}/>
            </figure>
            <div className="user-info">
              <span className="username">
                <a href={"/u/"+this.state.user.member_id}>{this.state.user.username}</a>
              </span>
              <span className="user-plings">
                <img src="/images/system/pling-btn-active.png" />
                {this.state.user.cnt}
              </span>
            </div>
          </div>
          <div className={"spotlight-user-plinged-products" + " " + productsContainerCssClass}>
            {users}
            <a className="next-button" onClick={this.getNextSpotLightUser}>next</a>
          </div>
        </div>
      );
    }

    let versionClassCss;
    if (this.state.version === 2){
      versionClassCss = "version-two";
    } else if (this.state.version === 3){
      versionClassCss = "version-three";
    }


    return(
      <div id="spotlight-user-container" className={versionClassCss}>
        <h2>In the spotlight</h2>
        {spotlightUserDisplay}
      </div>
    );
  }
}
/*
class SpotlightProduct extends React.Component {
  constructor(props){
  	super(props);
  	this.state = {
      featuredProduct:this.props.featuredProduct
    };
    this.onSpotlightMenuClick = this.onSpotlightMenuClick.bind(this);
  }

  onSpotlightMenuClick(val){
    let url = "/home/showfeaturejson/page/";
    if (val === "random"){ url += "0"; }
    else { url += "1"; }
    const self = this;
    $.ajax({url: url,cache: false}).done(function(response){
        self.setState({featuredProduct:response});
    });
  }

  render(){

    let imageBaseUrl;
    if (this.props.env === 'live') {
      imageBaseUrl = 'cn.opendesktop.org';
    } else {
      imageBaseUrl = 'cn.opendesktop.cc';
    }

    let description = this.state.featuredProduct.description;
    if (description && description.length > 295){
      description = this.state.featuredProduct.description.substring(0,295) + "...";
    }

    let featuredLabelDisplay;
    if (this.state.featuredProduct.featured === "1"){
      featuredLabelDisplay = <span className="featured-label">featured</span>
    }

    const cDate = new Date(this.props.featuredProduct.changed_at);
    const createdDate = jQuery.timeago(cDate);
    const productScoreColor = window.hpHelpers.calculateScoreColor(this.props.featuredProduct.laplace_score);

    return(
      <div id="spotlight-product">
        <h2>In the Spotlight</h2>
        <div className="container">
          <div className="spotlight-image">
            <img src={this.state.featuredProduct.image_small}/>
          </div>
          <div className="spotlight-info">
            <div className="spotlight-info-wrapper">
              {featuredLabelDisplay}
              <div className="info-top">
                <h2><a href={"/p/" + this.state.featuredProduct.project_id}>{this.state.featuredProduct.title}</a></h2>
                <h3>{this.state.featuredProduct.category}</h3>
                <div className="user-info">
                  <img src={this.state.featuredProduct.profile_image_url}/>
                  {this.state.featuredProduct.username}
                </div>
                <span>{this.state.featuredProduct.comment_count} comments</span>
                <div className="score-info">
                  <div className="score-number">
                    score {this.state.featuredProduct.laplace_score + "%"}
                  </div>
                  <div className="score-bar-container">
                    <div className={"score-bar"} style={{"width":this.state.featuredProduct.laplace_score + "%","backgroundColor":productScoreColor}}></div>
                  </div>
                  <div className="score-bar-date">
                    {createdDate}
                  </div>
                </div>
              </div>
              <div className="info-description">
                {description}
              </div>
            </div>
            <div className="spotlight-menu">
              <a onClick={() => this.onSpotlightMenuClick('random')}>random</a>
              <a onClick={() => this.onSpotlightMenuClick('featured')}>featured</a>
            </div>
          </div>
        </div>
      </div>
    );
  }
}
*/
class ProductCarousel extends React.Component {
  constructor(props){
  	super(props);
  	this.state = {
      products:this.props.products,
      offset:5,
      disableleftArrow:true
    };
    this.updateDimensions = this.updateDimensions.bind(this);
    this.animateProductCarousel = this.animateProductCarousel.bind(this);
    this.getNextProductsBatch = this.getNextProductsBatch.bind(this);
  }

  componentWillMount() {
    window.addEventListener("resize", this.updateDimensions);
  }

  componentDidMount() {
    this.updateDimensions();
  }

  updateDimensions(animateCarousel){
    let itemsPerRow = 5;
    if (window.hpVersion === 2){
      if (this.props.device === 'large'){
        itemsPerRow = 6;
      } else if (this.props.device === 'mid'){
        itemsPerRow = 5;
      } else if (this.props.device === 'tablet'){
        itemsPerRow = 2;
      }
    }

    const containerWidth = $('#main-content').width();
    const containerNumber = Math.ceil(this.state.products.length / (itemsPerRow - 1));
    const itemWidth = containerWidth / itemsPerRow;
    const sliderWidth = (containerWidth - itemWidth) * containerNumber;
    let sliderPosition = 0;
    if (this.state.sliderPosition){
      sliderPosition = this.state.sliderPosition;
    }
    this.setState({
      sliderPosition:sliderPosition,
      containerWidth:containerWidth,
      containerNumber:containerNumber,
      sliderWidth:sliderWidth,
      itemWidth:itemWidth,
      itemsPerRow:itemsPerRow - 1
    },function(){
      if (animateCarousel){
        this.animateProductCarousel('right',animateCarousel);
      } else if (this.state.finishedProducts){
        this.setState({disableRightArrow:true});
      }
    });
  }

  animateProductCarousel(dir,animateCarousel){
    let newSliderPosition = this.state.sliderPosition;
    const endPoint = this.state.sliderWidth - (this.state.containerWidth - this.state.itemWidth);

    if (dir === 'left'){
      if (this.state.sliderPosition > 0){
        newSliderPosition = this.state.sliderPosition - (this.state.containerWidth - this.state.itemWidth);
      }
    } else {
      if (Math.trunc(this.state.sliderPosition) < Math.trunc(endPoint)){
        newSliderPosition = this.state.sliderPosition + (this.state.containerWidth - this.state.itemWidth);
      } else {
        if (!animateCarousel){
          if (this.state.products.length >= 15 || this.state.finishedProducts){
            newSliderPosition = 0;
          } else {
            this.getNextProductsBatch();
          }
        }
      }
    }

    this.setState({sliderPosition:newSliderPosition},function(){

      let disableleftArrow = false;
      if (this.state.sliderPosition <= 0){
        disableleftArrow = true;
      }

      let disableRightArrow = false;
      /*if (this.state.sliderPosition >= endPoint && this.state.finishedProducts === true){
        disableRightArrow = true;
      }*/

      this.setState({disableRightArrow:disableRightArrow,disableleftArrow:disableleftArrow});

    });
  }

  getNextProductsBatch(){
    this.setState({disableRightArrow:true},function(){
      let limit = (this.state.itemsPerRow * (this.state.containerNumber + 1)) - this.state.products.length;
      if (limit <= 0){
        limit = this.state.itemsPerRow;
      }

      let url;
      if (!this.props.catIds){
        url = "/home/getnewactiveplingedproductjson/?limit="+limit+"&offset="+this.state.offset;
      } else {
        url = "/home/showlastproductsjson/?page=1&limit="+limit+"&offset="+this.state.offset+"&catIDs="+this.props.catIds+"&isoriginal=0";
      }

      const self = this;
      $.ajax({url: url,cache: false}).done(function(response){

          let products = self.state.products,
              finishedProducts = false,
              animateCarousel = true;

          if (response.length > 0){
            products = products.concat(response);
          } else {
            finishedProducts = true;
            animateCarousel = false;
          }

          if (response.length < limit){
            finishedProducts = true;
          }

          self.setState({
            products:products,
            offset:self.state.offset + response.length,
            finishedProducts:finishedProducts},function(){
              self.updateDimensions(animateCarousel);
          });
      });
    });
  }

  render(){
    let carouselItemsDisplay;
    if (this.state.products && this.state.products.length > 0){
      let plingedProduct = false;
      if (!this.props.catIds) plingedProduct = true;
      carouselItemsDisplay = this.state.products.map((product,index) => (
        <ProductCarouselItem
          key={index}
          product={product}
          itemWidth={this.state.itemWidth}
          env={this.props.env}
          plingedProduct={plingedProduct}
        />
      ));
    }

    let carouselArrowLeftDisplay;
    if (this.state.disableleftArrow){
      carouselArrowLeftDisplay = (
        <a className="carousel-arrow arrow-left disabled">
          <span className="glyphicon glyphicon-chevron-left"></span>
        </a>
      )
    } else {
      carouselArrowLeftDisplay = (
        <a onClick={() => this.animateProductCarousel('left')} className="carousel-arrow arrow-left">
          <span className="glyphicon glyphicon-chevron-left"></span>
        </a>
      );
    }

    let carouselArrowRightDisplay;
    if (this.state.disableRightArrow){
      carouselArrowRightDisplay = (
        <a className="carousel-arrow arrow-right disabled">
          <span className="glyphicon glyphicon-chevron-right"></span>
        </a>
      )
    } else {
      carouselArrowRightDisplay = (
        <a onClick={() => this.animateProductCarousel('right')} className="carousel-arrow arrow-right">
          <span className="glyphicon glyphicon-chevron-right"></span>
        </a>
      );
    }


    let hpVersionClass = "one";
    let carouselWrapperStyling = {};
    let carouselArrowsMargin;
    if (window.hpVersion === 2 && this.state.itemWidth){
      hpVersionClass = "two";
      let itemHeightMultiplier;
      if (this.state.itemWidth > 150){
        itemHeightMultiplier = 1.35;
      } else {
        itemHeightMultiplier = 1.85;
      }
      carouselWrapperStyling = {
        "paddingLeft":this.state.itemWidth / 2,
        "paddingRight":this.state.itemWidth / 2,
        "height":this.state.itemWidth * itemHeightMultiplier
      }
      carouselArrowsMargin = this.state.itemWidth / 4;
    }

    let titleLink = "/browse/cat/" + this.props.catIds + "/";
    if (!this.props.catIds){
      titleLink = "/community#plingedproductsPanel";
    }

    return (
      <div className={"product-carousel " + hpVersionClass}>
        <div className="product-carousel-header">
          <h2><a href={titleLink}>{this.props.title} <span className="glyphicon glyphicon-chevron-right"></span></a></h2>
        </div>
        <div className="product-carousel-wrapper" style={carouselWrapperStyling}>
          <div className="product-carousel-left" style={{"width":carouselArrowsMargin,"left":"0"}}>
            {carouselArrowLeftDisplay}
          </div>
          <div className="product-carousel-container">
            <div className="product-carousel-slider" style={{"width":this.state.sliderWidth,"left":"-"+this.state.sliderPosition + "px"}}>
              {carouselItemsDisplay}
            </div>
          </div>
          <div className="product-carousel-right" style={{"width":carouselArrowsMargin,"right":"0"}}>
            {carouselArrowRightDisplay}
          </div>
        </div>
      </div>
    )
  }
}

class ProductCarouselItem extends React.Component {
  constructor(props){
  	super(props);
  	this.state = {};
  }

  render(){

    let paddingTop;
    let productInfoDisplay = (
      <div className="product-info">
        <span className="product-info-title">{this.props.product.title}</span>
        <span className="product-info-user">{this.props.product.username}</span>
      </div>
    );

    if (window.hpVersion === 2){

      paddingTop = ((this.props.itemWidth * 1.35) / 2) - 10;
      let lastDate;
      if (this.props.product.changed_at){
        lastDate = this.props.product.changed_at;
      } else {
        lastDate = this.props.product.created_at;
      }

      const cDate = new Date(lastDate);
      const createdDate = jQuery.timeago(cDate)
      const productScoreColor = window.hpHelpers.calculateScoreColor(this.props.product.laplace_score);

      let scoreDisplay;
      if (this.props.plingedProduct){
        scoreDisplay = (
          <div className="score-info plings">
            <img src="/images/system/pling-btn-active.png" />
            {this.props.product.sum_plings}
          </div>
        );
      } else {
        scoreDisplay = (
          <div className="score-info">
            <div className="score-number">
              score {this.props.product.laplace_score + "%"}
            </div>
            <div className="score-bar-container">
              <div className={"score-bar"} style={{"width":this.props.product.laplace_score + "%","backgroundColor":productScoreColor}}></div>
            </div>
          </div>
        );
      }

      productInfoDisplay = (
        <div className="product-info">
          <span className="product-info-title">{this.props.product.title}</span>
          <span className="product-info-category">{this.props.product.cat_title}</span>
          <span className="product-info-date">{createdDate}</span>
          {scoreDisplay}
        </div>
      );
    }

    return (
      <div className="product-carousel-item" style={{"width":this.props.itemWidth}}>
        <div className="product-carousel-item-wrapper">
          <a href={"/p/"+this.props.product.project_id } style={{"paddingTop":paddingTop}}>
            <figure style={{"height":paddingTop}}>
              <img className="very-rounded-corners" src={this.props.product.image_small} />
            </figure>
            {productInfoDisplay}
          </a>
        </div>
      </div>
    )
  }
}

ReactDOM.render(
    <App />,
    document.getElementById('main-content')
);
