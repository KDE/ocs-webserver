class HomePage extends React.Component {
  constructor(props){
  	super(props);
  	this.state = {
      device:store.getState().device,
      products:store.getState().products
    };
  }

  componentWillReceiveProps(nextProps) {
    if (nextProps.device){
      this.setState({device:nextProps.device});
    }
    if (nextProps.products){
      this.setState({products:nextProps.products});
    }
  }

  render(){
    return (
      <div id="homepage">
        <div className="hp-wrapper">
          <Introduction
            device={this.state.device}
            count={window.totalProjects}
          />
        </div>
      </div>
    )
  }
}

const mapStateToHomePageProps = (state) => {
  const device = state.device;
  const products = state.products;
  return {
    device,
    products
  }
}

const mapDispatchToHomePageProps = (dispatch) => {
  return {
    dispatch
  }
}

const HomePageWrapper = ReactRedux.connect(
  mapStateToHomePageProps,
  mapDispatchToHomePageProps
)(HomePage);

class Introduction extends React.Component {
  render(){

    let introductionText, siteTitle, buttonsContainer;
    if (window.page === "appimages"){
      siteTitle = "AppImageHub";
      introductionText = (
        <p>
          This catalog has {this.props.count} AppImages and counting.<br/>
          AppImages are self-contained apps which can simply be downloaded & run on any Linux distribution. For easy integration, download AppImageLauncher:
        </p>
      );
      buttonsContainer = (
        <div className="actions">
          <a href="/p/1228228" className="mdl-button mdl-js-button mdl-button--raised mdl-js-ripple-effect mdl-button--colored mdl-color--primary">
            <img src="/theme/react/assets/img/icon-download_white.png"/> AppImageLauncher
          </a>
          <a href="/browse" className="mdl-button mdl-js-button mdl-button--raised mdl-js-ripple-effect mdl-button--colored mdl-color--primary">Browse all apps</a>

          <a href="https://chat.opendesktop.org/#/room/#appimagehub:chat.opendesktop.org" className="mdl-button mdl-js-button mdl-button--raised mdl-js-ripple-effect mdl-button--colored mdl-color--primary" style={{ "margin-left":"50px"}}>Join our chat #AppImageHub</a>
        </div>
      );
    } else if (window.page === "libreoffice"){
      siteTitle = "LibreOffice";
      introductionText = (
        <p>
          Extensions add new features to your LibreOffice or make the use of already existing ones easier.
          Currently there are {this.props.count} project(s) available.
        </p>
      );
      buttonsContainer = (
        <div className="actions green">
          <a href={window.baseUrl+"product/add"} className="mdl-button mdl-js-button mdl-button--raised mdl-js-ripple-effect mdl-button--colored mdl-color--primary">Add Extension</a>
          <a href={window.baseUrl+"browse/"} className="mdl-button mdl-js-button mdl-button--raised mdl-js-ripple-effect mdl-button--colored mdl-color--primary">Browse all Extensions</a>
        </div>
      );
    }

    return (
      <div id="introduction" className="section">
        <div className="container">
          <article>
            <h2 className="mdl-color-text--primary">Welcome to {siteTitle}</h2>
            {introductionText}
            {buttonsContainer}
          </article>
        </div>
      </div>
    )
  }
}

class HpIntroSection extends React.Component {
  constructor(props){
  	super(props);
  	this.state = {};
  }
  render(){
    return (
      <div id="homepage-search-container" className="section intro">
        <div className="container">
          <article>
            <p>Search thousands of snaps used by millions of people across 50 Linux distributions</p>
          </article>
          <div id="hp-search-form-container">
            <select className="mdl-selectfield__select">
              <option>categories</option>
            </select>
            <input type="text"/>
            <button>search</button>
          </div>
        </div>
      </div>
    )
  }
}

const mapStateToHpIntroSectionProps = (state) => {
  const categories = state.categories;
  return {
    categories
  }
}

const mapDispatchToHpIntroSectionProps = (dispatch) => {
  return {
    dispatch
  }
}

const HpIntroSectionWrapper = ReactRedux.connect(
  mapStateToHpIntroSectionProps,
  mapDispatchToHpIntroSectionProps
)(HpIntroSection);

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
