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
        <SpotlightUser
          env={this.state.env}
          device={this.state.device}
        />
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
    this.setState({loading:true},function(){
      let url = "/home/showfeaturejson/page/";
      if (val === "random"){ url += "0"; }
      else { url += "1"; }
      const self = this;
      $.ajax({url: url,cache: false}).done(function(response){
          self.setState({featuredProduct:response});
      });
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
      featuredLabelDisplay = (
        <span className="featured-label">featured</span>
      );
    }

    let cDate = new Date(this.state.featuredProduct.created_at);
    cDate = cDate.toString();
    const createdDate = cDate.split(' ')[1] + " " + cDate.split(' ')[2] + " " + cDate.split(' ')[3];
    const productScoreColor = window.hpHelpers.calculateScoreColor(this.state.featuredProduct.laplace_score);

    let loadingContainerDisplay;
    if (this.state.loading){
      loadingContainerDisplay = (
        <div className="loading-container">
          <div className="ajax-loader"></div>
        </div>
      );
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
                    <div className="score-bar" style={{"width":this.state.featuredProduct.laplace_score + "%","backgroundColor":productScoreColor}}></div>
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
        {loadingContainerDisplay}
      </div>
    );
  }
}

class SpotlightUser extends React.Component {
  constructor(props){
  	super(props);
  	this.state = {
      loading:true
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
        self.setState({user:response,loading:false});
      });
    });
  }

  render(){

    let spotlightUserDisplay;
    if (this.state.loading){
      spotlightUserDisplay = (
        <div className="loading-container">
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
    if (this.state.page < 10){
      nextButtonDisplay = (
        <a onClick={() => this.getSpotlightUser(this.state.page + 1)} className="spotlight-user-next">
          {"Next >"}
        </a>
      );
    }

    return(
      <div id="spotlight-user-container">
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
