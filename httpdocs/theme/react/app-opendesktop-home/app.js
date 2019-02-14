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

    this.setState({env:env});
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

  render(){
    const featuredProduct = JSON.parse(window.data['featureProducts']);
    return (
      <main id="opendesktop-homepage">
        <SpotlightProduct
          env={this.state.env}
          device={this.state.device}
          featuredProduct={featuredProduct}
        />
      </main>
    )
  }
}

class SpotlightProduct extends React.Component {
  constructor(props){
  	super(props);
  	this.state = {
      featuredProduct:this.props.featuredProduct,
      type:"featured",
      featuredPage:0,
      loading:true
    };
    this.onSpotlightMenuClick = this.onSpotlightMenuClick.bind(this);
  }

  componentDidMount() {
    this.onSpotlightMenuClick('plinged');
  }

  onSpotlightMenuClick(val){

    this.setState({type:val,loading:true},function(){

      let url = "/home/showfeaturejson/page/";
      let featuredPage = this.state.featuredPage;
      if (this.state.type === "plinged"){
        url = "/home/getnewactiveplingedproductjson?limit=1&offset=" + this.state.featuredPage;
        featuredPage = this.state.featuredPage + 1;
      } else if (this.state.type === "random"){
        url += "0";
      } else {
        url += "1";
      }
      const self = this;

      $.ajax({url: url,cache: false}).done(function(response){

        let featuredProduct = response;
        if (self.state.type === "plinged"){
          featuredProduct = response[0];
        }

        console.log(featuredProduct);

        self.setState({
          featuredProduct:featuredProduct,
          featuredPage:featuredPage,
          loading:false
        });
      });
    });
  }

  render(){

    let spotlightProductDisplay;
    if (this.state.loading){
      spotlightProductDisplay = (
        <SpotlightProductDummy />
      );
    } else {

      let productImageUrl;
      if (this.state.type === "plinged"){
        productImageUrl = this.state.featuredProduct.image_small;
      } else {
        let imageBaseUrl;
        if (this.props.env === 'live') {
          imageBaseUrl = 'cn.opendesktop.org';
        } else {
          imageBaseUrl = 'cn.opendesktop.cc';
        }
        productImageUrl = "https://" + imageBaseUrl + "/cache/300x230-1/img/" +  this.state.featuredProduct.image_small;
      }

      let description = this.state.featuredProduct.description;
      if (description && description.length > 295){
        description = this.state.featuredProduct.description.substring(0,295) + "...";
      }

      let featuredLabelDisplay;
      if (this.state.type === "featured"){
        featuredLabelDisplay = (
          <span className="featured-label">featured</span>
        );
      } else if (this.state.type === "plinged"){
        featuredLabelDisplay = (
          <span className="featured-label plinged">plinged</span>
        );
      }

      let cDate = new Date(this.state.featuredProduct.created_at);
      cDate = cDate.toString();
      const createdDate = cDate.split(' ')[1] + " " + cDate.split(' ')[2] + " " + cDate.split(' ')[3];
      // const productScoreColor = window.hpHelpers.calculateScoreColor(this.state.featuredProduct.laplace_score);

      let commentCount;
      if (this.state.featuredProduct.count_comments){
        commentCount = this.state.featuredProduct.count_comments;
      } else {
        commentCount = "0";
      }

      let categoryDisplay = this.state.featuredProduct.category;
      if (this.state.type === "plinged"){
        categoryDisplay = this.state.featuredProduct.cat_title;
      }

      spotlightProductDisplay = (
        <div className="container">
          <div className="spotlight-image">
            <img className="product-image" src={productImageUrl}/>
            <figure className="user-avatar">
              <img src={this.state.featuredProduct.profile_image_url}/>
            </figure>
          </div>
          <div className="spotlight-info">
            <div className="spotlight-info-wrapper">
              {featuredLabelDisplay}
              <div className="info-top">
                <h2><a href={"/p/" + this.state.featuredProduct.project_id}>{this.state.featuredProduct.title}</a></h2>
                <h3>{categoryDisplay}</h3>
                <div className="user-info">
                  <img src={this.state.featuredProduct.profile_image_url}/>
                  {this.state.featuredProduct.username}
                </div>
                <span>{commentCount} comments</span>
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
              <a onClick={() => this.onSpotlightMenuClick('plinged')}>plinged</a>
            </div>
          </div>
        </div>
      );
    }

    return(
      <div id="spotlight-product">
        <h2>In the Spotlight</h2>
        {spotlightProductDisplay}
      </div>
    );
  }
}

class SpotlightProductDummy extends React.Component {
  constructor(props){
  	super(props);
  	this.state = {};
  }

  render(){
    return(
      <div className="container dummy-product">
        <div className="spotlight-image">
          <figure className="user-avatar">
            <div className="ajax-loader"></div>
          </figure>
        </div>
        <div className="spotlight-info">
          <div className="spotlight-info-wrapper">
            <div className="info-top">
              <h2></h2>
              <h3></h3>
              <div className="user-info">
                <figure><span className="glyphicon glyphicon-user"></span></figure>
                <span></span>
              </div>
              <span className="comments-count"></span>
              <div className="score-info">
                <div className="score-number"></div>
                <div className="score-bar-container">
                  <div className="score-bar" style={{"width":"50%"}}></div>
                </div>
                <div className="score-bar-date"></div>
              </div>
            </div>
            <div className="info-description">
              <span></span>
              <span></span>
              <span></span>
              <span className="half"></span>
            </div>
          </div>
        </div>
      </div>
    );
  }
}

class SpotlightUser extends React.Component {
  constructor(props){
  	super(props);
  	this.state = {
      loading:true,
      version:2
    };
    this.updateDimensions = this.updateDimensions.bind(this);
    this.getSpotlightUser = this.getSpotlightUser.bind(this);
  }

  componentWillMount() {
    window.addEventListener("resize", this.updateDimensions);
  }

  componentDidMount() {
    this.updateDimensions();
    this.getSpotlightUser();
  }

  updateDimensions(){
    const containerWidth = $('#main-content').width();
    const userProductsPerRow = 4;
    const userProductsDimensions = containerWidth / userProductsPerRow;
    this.setState({itemWidth:userProductsDimensions,itemHeight:userProductsDimensions});
  }

  getSpotlightUser(page){
    if (!page) { page = 0 }
    this.setState({loading:true,page: page},function(){
      let url = "/home/showspotlightjson?page=" + this.state.page;
      const self = this;
      $.ajax({url: url,cache: false}).done(function(response){
        self.setState({user:response,loading:false},function(){
          const height = $('#user-container').height();
          if (height > 0){
            this.setState({containerHeight:height});
          }
        });
      });
    });
  }

  render(){

    let spotlightUserDisplay;
    if (this.state.loading){
      let loadingStyle;
      if (this.state.containerHeight){
        loadingStyle = {
          "height":this.state.containerHeight
        }
      }
      spotlightUserDisplay = (
        <div className="loading-container" style={loadingStyle}>
          <div className="ajax-loader"></div>
        </div>
      );
    } else {
      let userProducts;
      if (this.state.itemWidth){
        userProducts = this.state.user.products.map((p,index) => (
          <SpotlightUserProduct
            key={index}
            itemHeight={this.state.itemHeight}
            itemWidth={this.state.itemWidth}
            product={p}
          />
        ));
      }
      spotlightUserDisplay = (
        <div id="spotlight-user">
          <div className="user-container">
            <figure>
              <img src={this.state.user.profile_image_url}/>
            </figure>
            <h2><a href={"/u/"+this.state.user.username}>{this.state.user.username}</a></h2>
          </div>
          <div className="products-container">
            {userProducts}
          </div>
        </div>
      );
    }

    let prevButtonDisplay;
    if (this.state.page > 0){
      prevButtonDisplay = (
        <a onClick={() => this.getSpotlightUser(this.state.page - 1)} className="spotlight-user-next">
          {"< Prev"}
        </a>
      );
    }

    let nextButtonDisplay;
    if (this.state.page < 8){
      nextButtonDisplay = (
        <a onClick={() => this.getSpotlightUser(this.state.page + 1)} className="spotlight-user-next">
          {"Next >"}
        </a>
      );
    }

    let versionCssClass;
    if (this.state.version === 2){
      versionCssClass = "v-two"
    }

    return(
      <div id="spotlight-user-container" className={versionCssClass}>
        <h2>In the Spotlight</h2>
        {spotlightUserDisplay}
        <div className="spotlight-user-buttons">
          {prevButtonDisplay}
          {nextButtonDisplay}
        </div>
      </div>
    )
  }
}

class SpotlightUserProduct extends React.Component {
  constructor(props){
  	super(props);
  	this.state = {};
  }

  componentDidMount() {
    console.log(this.props);
  }

  render(){
    let userProductStyle;
    if (this.props.itemWidth){
      userProductStyle = {
        "height":this.props.itemHeight,
        "width":this.props.itemWidth
      }
    }
    return (
      <div style={userProductStyle} className="spotlight-user-product">
        <figure>
          <img src={this.props.product.image_small}/>
        </figure>
        <div className="product-title-overlay">
          <div className="product-title">
            {this.props.product.title}
          </div>
        </div>
        <div className="product-plings-counter">
          <img src="/images/system/pling-btn-active.png"/>
          <span>{this.props.product.sum_plings}</span>
        </div>
      </div>
    )
  }
}

ReactDOM.render(
    <App />,
    document.getElementById('main-content')
);
