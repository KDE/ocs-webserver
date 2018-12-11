class App extends React.Component {
  constructor(props){
  	super(props);
  	this.state = {
      loading:true
    };
    this.convertDataObject = this.convertDataObject.bind(this);
  }

  componentDidMount() {
    console.log('opendesktop app homepage');
    console.log(window.data);
    this.convertDataObject(window.data);
  }

  convertDataObject(data) {
    for (var i = 0; i < data.length; i++) {
      console.log(data[i])
      console.log([i]);
    }
    console.log('finished converting');
  }

  render(){
    let productCarouselsContainer;
    if (this.state.loading === false){
      productCarouselsContainer = (
        <div id="product-carousels-container">
          <div className="section">
            <div className="container">
              <ProductCarousel
                products={this.state.products.LatestProducts}
                device={this.state.device}
                title={'New'}
                link={'/browse/ord/latest/'}
              />
            </div>
          </div>
          <div className="section">
            <div className="container">
              <ProductCarousel
                products={this.state.products.LatestProducts}
                device={this.state.device}
                title={'New'}
                link={'/browse/ord/latest/'}
              />
            </div>
          </div>
        </div>
      );
    }

    return (
      <main id="opendesktop-homepage">

      </main>
    )
  }
}


class ProductCarousel extends React.Component {
  constructor(props){
  	super(props);
  	this.state = {
      showRightArrow:true,
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
    const containerWidth = $('#introduction').find('.container').width();
    const sliderWidth = containerWidth * 3;
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
        />
      ));
    }

    let rightArrowDisplay, leftArrowDisplay;
    if (this.state.showLeftArrow){
      leftArrowDisplay = (
        <div className="product-carousel-left">
          <a onClick={() => this.animateProductCarousel('left')} className="carousel-arrow arrow-left">
            <i className="material-icons">chevron_left</i>
          </a>
        </div>
      );
    }
    if (this.state.showRightArrow){
      rightArrowDisplay = (
        <div className="product-carousel-right">
          <a onClick={() => this.animateProductCarousel('right')} className="carousel-arrow arrow-right">
            <i className="material-icons">chevron_right</i>
          </a>
        </div>
      );
    }

    return (
      <div className="product-carousel">
        <div className="product-carousel-header">
          <h2><a href={this.props.link}>{this.props.title}<i className="material-icons">chevron_right</i></a></h2>
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
    if (store.getState().env === 'live') {
      imageBaseUrl = 'cn.opendesktop.org';
    } else {
      imageBaseUrl = 'cn.pling.it';
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
