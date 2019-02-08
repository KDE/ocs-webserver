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

    let hpDisplayWrapper;
    if (window.hpVersion === 1){
      hpDisplayWrapper = (
        <div className="carousels-wrapper">
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
                products={this.state.products.TopApps}
                device={this.state.device}
                title={'Top Apps'}
                link={'/browse/ord/top/'}
              />
            </div>
          </div>
          <div className="section"> 
            <div className="container">
              <ProductCarousel
                products={this.state.products.TopGames}
                device={this.state.device}
                title={'Top Games'}
                link={'/browse/cat/6/ord/top/'}
              />
            </div>
          </div>
        </div>
      )
    } else if (window.hpVersion === 2) {
      hpDisplayWrapper = (
        <div className="carousels-wrapper">
          <div className="section">
            <div className="container">
              <ProductCarouselV2
                products={this.state.products.LatestProducts}
                device={this.state.device}
                title={'New'}
                link={'/browse/ord/latest/'}
              />
            </div>
          </div>
          <div className="section">
            <div className="container">
              <ProductCarouselV2
                products={this.state.products.TopApps}
                device={this.state.device}
                title={'Top Apps'}
                link={'/browse/ord/top/'}
              />
            </div>
          </div>
          <div className="section">
            <div className="container">
              <ProductCarouselV2
                products={this.state.products.TopGames}
                device={this.state.device}
                title={'Top Games'}
                link={'/browse/cat/6/ord/top/'}
              />
            </div>
          </div>
        </div>
      )
    }

    return (
      <div id="homepage">
        <div className="hp-wrapper">
          <Introduction
            device={this.state.device}
            count={this.state.products.TotalProjects}
          />
          {hpDisplayWrapper}
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
    return (
      <div id="introduction" className="section">
        <div className="container">
          <article>
            <h2 className="mdl-color-text--primary">Welcome to AppImageHub</h2>
            <p>
              This catalog has {this.props.count} AppImages and counting.<br/>
              AppImages are self-contained apps which can simply be downloaded & run on any Linux distribution. For easy integration, download AppImageLauncher:
            </p>
            <div className="actions">
              <a href="/p/1228228" className="mdl-button mdl-js-button mdl-button--raised mdl-js-ripple-effect mdl-button--colored mdl-color--primary">
                <img src="/theme/react/assets/img/icon-download_white.png"/> AppImageLauncher
              </a>
              <a href="/browse" className="mdl-button mdl-js-button mdl-button--raised mdl-js-ripple-effect mdl-button--colored mdl-color--primary">Browse all apps</a>
            </div>
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

/* version 2 */

class ProductCarouselV2 extends React.Component {
  constructor(props){
  	super(props);
  	this.state = {
      products:this.props.products,
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
      if (this.props.device === 'very huge' || this.props.device === 'huge' || this.props.device === 'full'){
        itemsPerRow = 6;
      } else if ( this.props.device === 'large' || this.props.device === 'mid'){
        itemsPerRow = 5;
      } else if (this.props.device === 'tablet'){
        itemsPerRow = 3;
      } else if (this.props.device === 'phone'){
        itemsPerRow = 2;
      }
    }

    const containerWidth = $('#introduction').find('.container').width();
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
      console.log(this.state);
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
        newSliderPosition = 0
        /*if (!animateCarousel){
        if (this.state.products.length >= 15 || this.state.finishedProducts){
            newSliderPosition = 0;
          } else {
            this.getNextProductsBatch();
          }
        }*/
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
        <ProductCarouselItemV2
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

    return (
      <div className={"product-carousel-v2 " + hpVersionClass}>
        <div className="product-carousel-header">
          <h2><a href={this.props.link}>{this.props.title} <span className="glyphicon glyphicon-chevron-right"></span></a></h2>
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

class ProductCarouselItemV2 extends React.Component {
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
      const productScoreColor = window.appHelpers.calculateScoreColor(this.props.product.laplace_score);


      const scoreDisplay = (
        <div className="score-info">
          <div className="score-number">
            score {this.props.product.laplace_score + "%"}
          </div>
          <div className="score-bar-container">
            <div className={"score-bar"} style={{"width":this.props.product.laplace_score + "%","backgroundColor":productScoreColor}}></div>
          </div>
        </div>
      );

      productInfoDisplay = (
        <div className="product-info">
          <span className="product-info-title">{this.props.product.title}</span>
          <span className="product-info-category">{this.props.product.cat_title}</span>
          <span className="product-info-date">{createdDate}</span>
          {scoreDisplay}
        </div>
      );
    }

    let imageBaseUrl;
    if (store.getState().env === 'live') {
      imageBaseUrl = 'cn.opendesktop.org';
    } else {
      imageBaseUrl = 'cn.pling.it';
    }

    return (
      <div className="product-carousel-item" style={{"width":this.props.itemWidth}}>
        <div className="product-carousel-item-wrapper">
          <a href={"/p/"+this.props.product.project_id } style={{"paddingTop":paddingTop}}>
            <figure style={{"height":paddingTop}}>
              <img className="very-rounded-corners" src={'https://' + imageBaseUrl + '/cache/200x171/img/' + this.props.product.image_small} />
            </figure>
            {productInfoDisplay}
          </a>
        </div>
      </div>
    )
  }
}
