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
        <SpotlightProduct
          env={this.state.env}
          featuredProduct={featuredProduct}
        />
        {productCarouselsContainer}
      </main>
    )
  }
}

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
      featuredLabelDisplay = "featured"
    }

    let cDate = new Date(this.props.featuredProduct.created_at);
    cDate = cDate.toString();
    const createdDate = cDate.split(' ')[1] + " " + cDate.split(' ')[2] + " " + cDate.split(' ')[3];

    return(
      <div id="spotlight-product">
        <h2>In the Spotlight</h2>
        <div className="container">
          <div className="spotlight-image">
            <img src={"https://" + imageBaseUrl + "/cache/300x230-1/img/" + this.state.featuredProduct.image_small}/>
          </div>
          <div className="spotlight-info">
            <div className="spotlight-info-wrapper">
              <span className="featured-label">{featuredLabelDisplay}</span>
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
                    <div className="score-bar" style={{"width":this.state.featuredProduct.laplace_score + "%"}}></div>
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
    const containerNumber = Math.ceil(this.state.products.length / itemsPerRow);
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
      offset:itemsPerRow,
      itemsPerRow:itemsPerRow - 1
    },function(){
      if (animateCarousel){
        this.animateProductCarousel('right',animateCarousel);
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
      if (this.state.sliderPosition <= endPoint){
        newSliderPosition = this.state.sliderPosition + (this.state.containerWidth - this.state.itemWidth);
      } else {
        if (!animateCarousel){
          this.getNextProductsBatch();
        }
      }
    }

    this.setState({sliderPosition:newSliderPosition},function(){

      let disableleftArrow = false;
      if (this.state.sliderPosition <= 0){
        disableleftArrow = true;
      }

      let disableRightArrow = false;
      if (this.state.finishedProducts === true){
        disableRightArrow = true;
      }

      this.setState({disableRightArrow:disableRightArrow,disableleftArrow:disableleftArrow});

    });
  }

  getNextProductsBatch(){
    let limit = (this.state.itemsPerRow * (this.state.containerNumber + 1)) - this.state.products.length;
    if (limit <= 0){
      limit = this.state.itemsPerRow;
    }
    console.log(limit);
    let url = "/home/showlastproductsjson/?page=1&limit="+limit+"&offset="+this.state.offset+"&catIDs="+this.props.catIds+"&isoriginal=0";
    const self = this;
    $.ajax({url: url,cache: false}).done(function(response){
        const products = self.state.products.concat(response);
        const offset = self.state.offset + self.state.itemsPerRow;
        let finishedProducts = false;
        if (response.length <= self.state.itemsPerRow * (self.state.containerNumber + 1) - self.state.products.length){
          finishedProducts = true;
        }
        console.log(finishedProducts);
        console.log(response.length);
        self.setState({products:products,offset:offset,finishedProducts:finishedProducts},function(){
          const animateCarousel = true;
          self.updateDimensions(animateCarousel);
        });
    });
  }

  render(){
    let carouselItemsDisplay;
    if (this.state.products && this.state.products.length > 0){
      carouselItemsDisplay = this.state.products.map((product,index) => (
        <ProductCarouselItem
          key={index}
          product={product}
          itemWidth={this.state.itemWidth}
          env={this.props.env}
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
      carouselWrapperStyling = {
        "paddingLeft":this.state.itemWidth / 2,
        "paddingRight":this.state.itemWidth / 2,
        "height":this.state.itemWidth * 1.35
      }
      carouselArrowsMargin = this.state.itemWidth / 4;
    }

    return (
      <div className={"product-carousel " + hpVersionClass}>
        <div className="product-carousel-header">
          <h2><a href={"/browse/cat/" + this.props.catIds + "/"}>{this.props.title} <span className="glyphicon glyphicon-chevron-right"></span></a></h2>
        </div>
        <div className="product-carousel-wrapper" style={carouselWrapperStyling}>
          <div className="product-carousel-left" style={{"left":carouselArrowsMargin}}>
            {carouselArrowLeftDisplay}
          </div>
          <div className="product-carousel-container">
            <div className="product-carousel-slider" style={{"width":this.state.sliderWidth,"left":"-"+this.state.sliderPosition + "px"}}>
              {carouselItemsDisplay}
            </div>
          </div>
          <div className="product-carousel-right" style={{"right":carouselArrowsMargin}}>
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
    let imageUrl = this.props.product.image_small;
    if (imageUrl && this.props.product.image_small.indexOf('https://') === -1 && this.props.product.image_small.indexOf('http://') === -1){
      let imageBaseUrl;
      if (this.props.env === 'live') {
        imageBaseUrl = 'cn.opendesktop.org';
      } else {
        imageBaseUrl = 'cn.opendesktop.cc';
      }
      imageUrl = 'https://' + imageBaseUrl + '/cache/200x171/img/' + this.props.product.image_small;
    }

    let paddingTop;
    let productInfoDisplay = (
      <div className="product-info">
        <span className="product-info-title">{this.props.product.title}</span>
        <span className="product-info-user">{this.props.product.username}</span>
      </div>
    );

    if (window.hpVersion === 2){
      paddingTop = ((this.props.itemWidth * 1.35) / 2) - 10;
      let cDate = new Date(this.props.product.created_at);
      cDate = cDate.toString();
      const createdDate = cDate.split(' ')[1] + " " + cDate.split(' ')[2] + " " + cDate.split(' ')[3];
      productInfoDisplay = (
        <div className="product-info">
          <span className="product-info-title">{this.props.product.title}</span>
          <span className="product-info-category">{this.props.product.cat_title}</span>
          <span className="product-info-date">{createdDate}</span>
          <span className="product-info-commentcount">{this.props.product.count_comments} comments</span>
          <div className="score-info">
            <div className="score-number">
              score {this.props.product.laplace_score + "%"}
            </div>
            <div className="score-bar-container">
              <div className="score-bar" style={{"width":this.props.product.laplace_score + "%"}}></div>
            </div>
          </div>
        </div>
      );
    }

    return (
      <div className="product-carousel-item" style={{"width":this.props.itemWidth}}>
        <div className="product-carousel-item-wrapper">
          <a href={"/p/"+this.props.product.project_id } style={{"paddingTop":paddingTop}}>
            <figure style={{"height":paddingTop}}>
              <img className="very-rounded-corners" src={imageUrl} />
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
