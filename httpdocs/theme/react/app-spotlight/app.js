class SpotlightUser extends React.Component {
  constructor(props){
  	super(props);
  	this.state = {
      page:1,
      loading:true,
      version:2
    };
    this.updateDimensions = this.updateDimensions.bind(this);
    this.getSpotlightUser = this.getSpotlightUser.bind(this);
    this.getNextSpotLightUser = this.getNextSpotLightUser.bind(this);
  }

  componentDidMount() {
    window.addEventListener("resize", this.updateDimensions);
    window.addEventListener("orientationchange",this.updateDimensions);
    this.updateDimensions();
    this.getSpotlightUser();
  }

  updateDimensions(){
    const containerWidth = $('.content').width();
    const containerPaddingLeft = $('.sidebar-left').width();
    this.setState({containerWidth:containerWidth,containerPaddingLeft:containerPaddingLeft});
  }

  getSpotlightUser(){
    const self = this;
    $.ajax({url: "/home/showspotlightjson?page="+this.state.page,cache: false}).done(function(response){
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
                <a href={"/p/"+p.project_id}>
                  {p.title}
                </a>
              </span>
            </div>
          </div>
        </div>
      ));

      let productsContainerCssClass;
      if (this.state.version === 1){
        if (this.state.user.products.length === 2){
          productsContainerCssClass = "one-row";
        } else if (this.state.user.products.length === 1){
          productsContainerCssClass = "one-row single-product";
        }
      }

      console.log(this.state);

      spotlightUserDisplay = (
        <div id="spotlight-user">
          <div className="spotlight-user-image">
            <figure>
              <img src={this.state.user.profile_image_url}/>
            </figure>
            <div className="user-info">
              <span className="username">
                <a href={"/u/"+this.state.user.username}>{this.state.user.username}</a>
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

    return(
      <div id="spotlight-user-container">
        <h2>In the spotlight</h2>
        {spotlightUserDisplay}
      </div>
    );
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

ReactDOM.render(
    <SpotlightUser />,
    document.getElementById('spotlight-container')
);
