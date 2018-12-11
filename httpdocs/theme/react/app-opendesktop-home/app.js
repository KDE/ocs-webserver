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
          title:i,
          products:JSON.parse(window.data[i])
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
  	this.state = {};
  }

  componentDidMount() {
    console.log(this.props.featuredProduct);
  }

  render(){

    let imageBaseUrl;
    if (this.props.env === 'live') {
      imageBaseUrl = 'cn.opendesktop.org';
    } else {
      imageBaseUrl = 'cn.opendesktop.cc';
    }

    let description = this.props.featuredProduct.description;
    if (description.length > 295){
      description = this.props.featuredProduct.description.substring(0,295) + "...";
    }

    return(
      <div id="spotlight-product">
        <h2>In the Spotlight</h2>
        <div className="container">
          <div className="spotlight-image">
            <img src={"https://" + imageBaseUrl + "/cache/300x230-1/img/" + this.props.featuredProduct.image_small}/>
          </div>
          <div className="spotlight-info">
            <div className="info-top">
              <h2><a href={"/p/"+this.props.featuredProduct.project_id}>title</a></h2>
              <h3>category</h3>
              <div className="user-info">
                <img src={this.props.featuredProduct.profile_image_url}/>
                {this.props.featuredProduct.username}
              </div>
            </div>
            <div className="info-description">
              {description}
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
    let showRightArrow = false;
    if (this.props.products.length > 5){
      showRightArrow = true;
    }
  	this.state = {
      showRightArrow:showRightArrow,
      showLeftArrow:false
    };
    this.updateDimensions = this.updateDimensions.bind(this);
    this.animateProductCarousel = this.animateProductCarousel.bind(this);
  }

  componentWillMount() {
    window.addEventListener("resize", this.updateDimensions);
  }

  componentDidMount() {
    this.updateDimensions();
  }

  updateDimensions(){
    const containerWidth = $('#main-content').width();
    const containerNumber = Math.ceil(this.props.products / 5);
    const sliderWidth = containerWidth * containerNumber;
    const itemWidth = containerWidth / 5;
    this.setState({
      sliderPosition:0,
      containerWidth:containerWidth,
      sliderWidth:sliderWidth,
      itemWidth:itemWidth
    });
  }

  animateProductCarousel(dir){

    let newSliderPosition = this.state.sliderPosition;
    if (dir === 'left'){
      newSliderPosition = this.state.sliderPosition - this.state.containerWidth;
    } else {
      newSliderPosition = this.state.sliderPosition + this.state.containerWidth;
    }

    this.setState({sliderPosition:newSliderPosition},function(){

      let showLeftArrow = true,
          showRightArrow = true;
      const endPoint = this.state.sliderWidth - this.state.containerWidth;
      if (this.state.sliderPosition <= 0){
        showLeftArrow = false;
      }
      if (this.state.sliderPosition >= endPoint){
        showRightArrow = false;
      }

      this.setState({
        showLeftArrow:showLeftArrow,
        showRightArrow:showRightArrow
      });

    });

  }

  render(){

    let carouselItemsDisplay;
    if (this.props.products && this.props.products.length > 0){
      carouselItemsDisplay = this.props.products.map((product,index) => (
        <ProductCarouselItem
          key={index}
          product={product}
          itemWidth={this.state.itemWidth}
          env={this.props.env}
        />
      ));
    }

    let rightArrowDisplay, leftArrowDisplay;
    if (this.state.showLeftArrow){
      leftArrowDisplay = (
        <div className="product-carousel-left">
          <a onClick={() => this.animateProductCarousel('left')} className="carousel-arrow arrow-left">
            <span className="glyphicon glyphicon-chevron-left"></span>
          </a>
        </div>
      );
    }
    if (this.state.showRightArrow){
      rightArrowDisplay = (
        <div className="product-carousel-right">
          <a onClick={() => this.animateProductCarousel('right')} className="carousel-arrow arrow-right">
            <span className="glyphicon glyphicon-chevron-right"></span>
          </a>
        </div>
      );
    }

    return (
      <div className="product-carousel">
        <div className="product-carousel-header">
          <h2><a href={this.props.link}>{this.props.title.match(/[A-Z][a-z]+/g).join(' ')} <span className="glyphicon glyphicon-chevron-right"></span></a></h2>
        </div>
        <div className="product-carousel-wrapper">
          {leftArrowDisplay}
          <div className="product-carousel-container">
            <div className="product-carousel-slider" style={{"width":this.state.sliderWidth,"left":"-"+this.state.sliderPosition + "px"}}>
              {carouselItemsDisplay}
            </div>
          </div>
          {rightArrowDisplay}
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
    let imageBaseUrl;
    if (this.props.env === 'live') {
      imageBaseUrl = 'cn.opendesktop.org';
    } else {
      imageBaseUrl = 'cn.opendesktop.cc';
    }
    return (
      <div className="product-carousel-item" style={{"width":this.props.itemWidth}}>
        <a href={"/p/"+this.props.product.project_id }>
          <figure>
            <img className="very-rounded-corners" src={'https://' + imageBaseUrl + '/cache/200x171/img/' + this.props.product.image_small} />
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
