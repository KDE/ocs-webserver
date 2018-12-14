class App extends React.Component {
  constructor(props){
  	super(props);
  	this.state = {
      loading:true
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
    console.log(window.data);
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
    if (this.state.featuredProduct.feature === "1"){
      featuredLabelDisplay = "featured"
    }

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
                <div className="score-info">
                  <div className="score-number">
                    score {this.state.featuredProduct.laplace_score + "%"}
                  </div>
                  <div className="score-bar-container">
                    <div className="score-bar" style={{"width":this.state.featuredProduct.laplace_score + "%"}}></div>
                  </div>
                  <div className="score-bar-date">

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
      products:this.props.products
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

    /*let itemsPerRow;
    if (this.props.device === 'large'){
      itemsPerRow = 5;
    } else if (this.props.device === 'mid'){
      itemsPerRow = 4;
    } else if (this.props.device === 'tablet'){
      itemsPerRow = 3;
    }*/

    const containerWidth = $('#main-content').width();
    const containerNumber = Math.ceil(this.props.products.length / 5);
    const sliderWidth = containerWidth * containerNumber;
    const itemWidth = containerWidth / 5;
    let sliderPosition = 0;
    if (this.state.sliderPosition){
      sliderPosition = this.state.sliderPosition;
    }
    this.setState({
      sliderPosition:sliderPosition,
      containerWidth:containerWidth,
      sliderWidth:sliderWidth,
      itemWidth:itemWidth
    },function(){
      if (animateCarousel){
        this.animateProductCarousel('right');
      }
    });
  }

  animateProductCarousel(dir){
    let newSliderPosition = this.state.sliderPosition;
    if (dir === 'left'){
      if (this.state.sliderPosition > 0){
        newSliderPosition = this.state.sliderPosition - this.state.containerWidth;
      }
    } else {
      const endPoint = this.state.sliderWidth - this.state.containerWidth;
      if (this.state.sliderPosition < endPoint){
        newSliderPosition = this.state.sliderPosition + this.state.containerWidth;
      } else {
        this.getNextProductsBatch();
      }
    }
    this.setState({sliderPosition:newSliderPosition});
  }

  getNextProductsBatch(){
    let offset = "5";
    if (this.state.offset){offset = this.state.offset;}
    let url = "/home/showlastproductsjson/?page=1&limit=5&offset="+offset+"&catIDs="+this.props.catIds+"&isoriginal=0";
    console.log(url);
    const self = this;
    $.ajax({url: url,cache: false}).done(function(response){
        const products = self.state.products.concat(response);
        self.setState({products:products},function(){
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

    return (
      <div className="product-carousel">
        <div className="product-carousel-header">
          <h2><a href={this.props.link}>{this.props.title} <span className="glyphicon glyphicon-chevron-right"></span></a></h2>
        </div>
        <div className="product-carousel-wrapper">
          <div className="product-carousel-left">
            <a onClick={() => this.animateProductCarousel('left')} className="carousel-arrow arrow-left">
              <span className="glyphicon glyphicon-chevron-left"></span>
            </a>
          </div>
          <div className="product-carousel-container">
            <div className="product-carousel-slider" style={{"width":this.state.sliderWidth,"left":"-"+this.state.sliderPosition + "px"}}>
              {carouselItemsDisplay}
            </div>
          </div>
          <div className="product-carousel-right">
            <a onClick={() => this.animateProductCarousel('right')} className="carousel-arrow arrow-right">
              <span className="glyphicon glyphicon-chevron-right"></span>
            </a>
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
    if (this.props.product.image_small.indexOf('https://') === -1 && this.props.product.image_small.indexOf('http://') === -1){
      let imageBaseUrl;
      if (this.props.env === 'live') {
        imageBaseUrl = 'cn.opendesktop.org';
      } else {
        imageBaseUrl = 'cn.opendesktop.cc';
      }
      imageUrl = 'https://' + imageBaseUrl + '/cache/200x171/img/' + this.props.product.image_small;
    }


    return (
      <div className="product-carousel-item" style={{"width":this.props.itemWidth}}>
        <a href={"/p/"+this.props.product.project_id }>
          <figure>
            <img className="very-rounded-corners" src={imageUrl} />
          </figure>
          <div className="product-info">
            <span className="product-info-title">{this.props.product.title}</span>
            <span className="product-info-user">{this.props.product.username}</span>
          </div>
        </a>
      </div>
    )
  }
}

ReactDOM.render(
    <App />,
    document.getElementById('main-content')
);
