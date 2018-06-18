class HomePageTemplateOne extends React.Component {
  constructor(props){
  	super(props);
  	this.state = {};
  }

  render(){
    return (
      <div id="homepage-version-one">
        <SpotlightProductWrapper/>
        <LatestProductsWrapper/>
        <TopProductsWrapper/>
        <CommunitySection/>
        <IntroDiv/>
      </div>
    )
  }
}

class SpotlightProduct extends React.Component {
  constructor(props){
  	super(props);
  	this.state = {};
  }

  componentWillReceiveProps(nextProps) {
    if (nextProps.products && !this.state.product){
      this.setState({product:nextProps.products.ThemeGTK[0]});
    }
  }

  render(){

    let spotlightProduct;
    if (this.state.product){
      const productTimeAgo = appHelpers.getTimeAgo(this.state.product.created_at);
      spotlightProduct = (
        <div className="content-grid mdl-grid mdl-card mdl-shadow--2dp" id="spotlight-product">
          <div className="mdl-cell mdl-cell--4-col mdl-cell--3-col-tablet mdl-cell--1-col-phone">
            <img className="product-image mdl-shadow--2dp" src={"https://cn.pling.it/cache/200x171/img/" + this.state.product.image_small}/>
          </div>
          <div className="mdl-cell mdl-cell--8-col mdl-cell--5-col-tablet mdl-cell--3-col-phone">
            <h2 className="mdl-color-text--primary">{this.state.product.title}</h2>
            <div className="spotlight-product-sub-info">
              <span className="mdl-chip mdl-shadow--2dp mdl-chip--contact">
                  <img className="mdl-chip__contact" src={this.state.product.profile_image_url}></img>
                  <span className="mdl-chip__text">{this.state.product.username}</span>
              </span>
              <span className="mdl-chip mdl-shadow--2dp mdl-chip--contact">
                <span className="mdl-chip__contact mdl-color--primary mdl-color-text--white">
                  <i className="material-icons">category</i>
                </span>
                <span className="mdl-chip__text">{this.state.product.cat_title}</span>
              </span>
              <span className="mdl-chip mdl-shadow--2dp mdl-chip--contact">
                <span className="mdl-chip__contact  mdl-color--primary mdl-color-text--white">
                  <i className="material-icons">date_range</i>
                </span>
                <span className="mdl-chip__text">{productTimeAgo}</span>
              </span>
            </div>
            <div className="spotlight-product-description">
              {this.state.product.description}
            </div>
          </div>
        </div>
      );
    }

    return (
      <div id="spotlight-product-container" className="hp-section">
        <div className="ui container">
            <h2 className="mdl-color-text--primary">in the spotlight</h2>
            {spotlightProduct}
        </div>
      </div>
    )
  }
}

const mapStateToSpotlightProductProps = (state) => {
  const products = state.products;
  return {
    products
  }
}

const mapDispatchToSpotlightProductProps = (dispatch) => {
  return {
    dispatch
  }
}

const SpotlightProductWrapper = ReactRedux.connect(
  mapStateToSpotlightProductProps,
  mapDispatchToSpotlightProductProps
)(SpotlightProduct)

class IntroDiv extends React.Component {
  constructor(props){
  	super(props);
  	this.state = {};
  }

  render(){
    return (
      <div id="intro" className="hp-section">
        <div className="container">
          <div className="mdl-content mdl-grid">
              <div className="mdl-cell mdl-cell--6-col mdl-cell--4-col-tablet mdl-cell--2-col-phone">
                <a href="https://www.opendesktop.org/p/1175480/">
                  <img id="download-app" src="/images/system/download-app.png"/>
                </a>
              </div>
              <div className="mdl-cell mdl-cell--6-col mdl-cell--4-col-tablet mdl-cell--2-col-phone">
                <a id="become-supporter" href="/supprt"><h1>become a supporter</h1></a>
              </div>
          </div>
        </div>
      </div>
    );
  }
}

class LatestProducts extends React.Component {
  constructor(props){
  	super(props);
  	this.state = {};
  }

  componentWillReceiveProps(nextProps) {
    if (nextProps.products && !this.state.products){
      this.setState({products:nextProps.products.ThemesPlasma});
    }
  }

  render(){
    let latestProducts;
    if (this.state.products){
      latestProducts = this.state.products.map((product,index) => (
        <div key={index} className="mdl-cell mdl-cell--3-col mdl-cell--4-col-tablet mdl-cell--2-col-phone">
          <div className="mdl-card mdl-shadow--2dp">
            <div className="mdl-card__title mdl-card--expand" style={{backgroundImage:'url(https://cn.pling.it/cache/200x171/img/' + product.image_small + ')'}}>
              <a href={"/p/"+product.project_id}></a>
            </div>
            <div className="mdl-card__actions mdl-color--primary">
              <a href={"/p/"+product.project_id} className="demo-card-image__filename mdl-color-text--white">{product.title}</a>
            </div>
          </div>
        </div>
      ));
    }


    return (
      <div id="latest-products" className="hp-section products-showcase">
        <div className="container">
          <h2  className="mdl-color-text--primary">latest products</h2>
          <div className="content-grid mdl-grid">
            {latestProducts}
          </div>
        </div>
      </div>
    )
  }
}

const mapStateToLatestProductsProps = (state) => {
  const products = state.products;
  return {
    products
  }
}

const mapDispatchToLatestProductsProps = (dispatch) => {
  return {
    dispatch
  }
}

const LatestProductsWrapper = ReactRedux.connect(
  mapStateToLatestProductsProps,
  mapDispatchToLatestProductsProps
)(LatestProducts);

class TopProducts extends React.Component {
  constructor(props){
  	super(props);
  	this.state = {};
  }

  componentWillReceiveProps(nextProps) {
    if (nextProps.products && !this.state.products){
      let products;
      if (nextProps.products.TopProducts.elements.length > 0){
        products = nextProps.products.TopProducts.elements;
      } else {
        products = nextProps.products.ThemesPlasma;
      }
      this.setState({products:products});
    }
  }

  render(){
    let topProducts;
    if (this.state.products){
      topProducts = this.state.products.map((product,index) => (
        <div key={index} className="mdl-cell mdl-cell--3-col mdl-cell--4-col-tablet mdl-cell--2-col-phone">
          <div className="mdl-card mdl-shadow--2dp">
            <div className="mdl-card__title mdl-card--expand" style={{backgroundImage:'url(https://cn.pling.it/cache/200x171/img/' + product.image_small + ')'}}>
              <a href={"/p/"+product.project_id}></a>
            </div>
            <div className="mdl-card__actions  mdl-color--primary">
              <a href={"/p/"+product.project_id} className="demo-card-image__filename  mdl-color-text--white">{product.title}</a>
            </div>
          </div>
        </div>

      ));
    }
    return (
      <div id="hottest-products" className="products-showcase hp-section">
        <div className="container">
          <h2  className="mdl-color-text--primary">hottest products</h2>
          <div className="content-grid mdl-grid">
            {topProducts}
          </div>
        </div>
      </div>
    )
  }
}

const mapStateToTopProductsProps = (state) => {
  const products = state.products;
  return {
    products
  }
}

const mapDispatchToTopProductsProps = (dispatch) => {
  return {
    dispatch
  }
}

const TopProductsWrapper = ReactRedux.connect(
  mapStateToTopProductsProps,
  mapDispatchToTopProductsProps
)(TopProducts)

class CommunitySection extends React.Component {
  constructor(props){
  	super(props);
  	this.state = {};
  }

  render(){
    return (
      <div id="community-section" className="hp-section">
        <div className="container">
          <div className="mdl-content mdl-grid">
              <div id="latest-rss-news-container" className="community-section-div mdl-cell mdl-cell--6-col mdl-cell--3-col-tablet mdl-cell--2-col-phone">
                <LatestRssNewsPosts/>
              </div>
              <div id="latest-blog-posts-container" className="community-section-div mdl-cell mdl-cell--6-col mdl-cell--3-col-tablet mdl-cell--2-col-phone">
                <LatestBlogPosts/>
              </div>
          </div>
        </div>
      </div>
    )
  }
}

class LatestRssNewsPosts extends React.Component {
  constructor(props){
  	super(props);
  	this.state = {};
    this.getLatestRssNewsPosts = this.getLatestRssNewsPosts.bind(this);
  }

  componentDidMount() {
    this.getLatestRssNewsPosts()
  }

  getLatestRssNewsPosts(){
    const json_url = "https://blog.opendesktop.org/?json=1&callback=?"
    const self = this;
    $.getJSON(json_url, function (res) {
      self.setState({posts:res.posts})
    });
  }

  render(){
    let rssNewsPostsDisplay;
    if (this.state.posts){
      rssNewsPostsDisplay = this.state.posts.slice(0,3).map((np,index) => (
        <div className="mdl-list__item rss-news-post" key={index}>
          <div className="mdl-list__item-text-body">
            <h3><a href={np.url}>{np.title}</a></h3>
            <p dangerouslySetInnerHTML={{__html:np.excerpt}}></p>
          </div>
        </div>
      ));
    }
    return(
      <div id="latest-rss-news" className="mdl-shadow--2dp">
        <h2 className="mdl-color--primary mdl-color-text--white">latest rss news</h2>
        <div className="mdl-list">
          {rssNewsPostsDisplay}
        </div>
      </div>
    )
  }
}

class LatestBlogPosts extends React.Component {
  constructor(props){
  	super(props);
  	this.state = {};
    this.getLatestBlogPosts = this.getLatestBlogPosts.bind(this);
  }

  componentDidMount() {
    this.getLatestBlogPosts()
  }

  getLatestBlogPosts(){
    const urlforum = 'https://forum.opendesktop.org/';
    const json_url =urlforum+'latest.json';
    const self = this;
    $.ajax(json_url).then(function (result) {
      self.setState({posts:result.topic_list.topics});
    });
  }

  render(){
    let blogPostsDisplay;
    if (this.state.posts){
      blogPostsDisplay = this.state.posts.slice(0,3).map((bp,index) => (
        <div className="mdl-list__item rss-news-post" key={index}>
          <div className="mdl-list__item-text-body">
            <h3><a href={bp.url}>{bp.title}</a></h3>
            <p dangerouslySetInnerHTML={{__html:bp.excerpt}}></p>
          </div>
        </div>
      ));
    }
    return (
      <div id="latest-blog-posts" className="mdl-shadow--2dp">
        <h2 className="mdl-color--primary mdl-color-text--white">Latest Blog Posts</h2>
        <div className="mdl-list">
          {blogPostsDisplay}
        </div>
      </div>
    )
  }
}
