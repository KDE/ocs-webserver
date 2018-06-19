class HomePageTemplateOne extends React.Component {
  constructor(props){
  	super(props);
  	this.state = {};
  }

  render(){
    return (
      <div id="homepage-version-one">
        <Introduction/>
        <LatestProductsWrapper/>
        <TopProductsWrapper/>
      </div>
    )
  }
}

class Introduction extends React.Component {
  render(){
    return (
      <div id="Introduction" className="hp-section">
        <div className="container">
          <article>
            <h2 className="mdl-color-text--primary">App Images Hub, right here</h2>
            <p>Welcome to appimagehub, the home of hundreds of apps which can be easily installed on any Linux distribution. Browse the apps online, from your app center or the command line.</p>
            <div className="actions">
              <button className="mdl-button mdl-js-button mdl-button--raised mdl-js-ripple-effect mdl-button--colored mdl-color--primary">browse</button>
              <button className="mdl-button mdl-js-button mdl-button--raised mdl-js-ripple-effect mdl-button--colored mdl-color--primary">join</button>
            </div>
          </article>
        </div>
      </div>
    )
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
        <div key={index} className="product square">
            <div className="content">
              <div className="product-wrapper mdl-shadow--2dp">
                <a href={"/p/"+product.project_id}>
                  <div className="product-image-container">
                    <figure>
                      <img className="mdl-shadow--2dp" src={'https://cn.pling.it/cache/200x171/img/' + product.image_small} />
                    </figure>
                  </div>
                  <div className="product-info mdl-color--primary">
                    <span className="product-info-title">{product.title}</span>
                    <span className="product-info-description">{product.description}</span>
                  </div>
                </a>
              </div>
          </div>
        </div>
      ));
    }


    return (
      <div id="latest-products" className="hp-section products-showcase">
        <div className="container">
          <div className="section-header">
            <h2  className="mdl-color-text--primary">latest products</h2>
            <div className="actions">
              <button className="mdl-button mdl-js-button mdl-button--colored mdl-button--raised mdl-js-ripple-effect mdl-color--primary">show more</button>
            </div>
          </div>
          <div className="products-container row">
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
        console.log(nextProps.products);
        products = nextProps.products.Apps;
      }
      this.setState({products:products});
    }
  }

  render(){
    let topProducts;
    if (this.state.products){
      topProducts = this.state.products.map((product,index) => (
        <div key={index} className="product square">
            <div className="content">
              <div className="product-wrapper mdl-shadow--2dp">
                <a href={"/p/"+product.project_id}>
                  <div className="product-image-container">
                    <figure>
                      <img className="mdl-shadow--2dp" src={'https://cn.pling.it/cache/200x171/img/' + product.image_small} />
                    </figure>
                  </div>
                  <div className="product-info mdl-color--primary">
                    <span className="product-info-title">{product.title}</span>
                    <span className="product-info-description">{product.description}</span>
                  </div>
                </a>
              </div>
          </div>
        </div>
      ));
    }
    return (
      <div id="hottest-products" className="hp-section products-showcase">
        <div className="container">
          <div className="section-header">
            <h2  className="mdl-color-text--primary">hottest products</h2>
            <div className="actions">
              <button className="mdl-button mdl-js-button mdl-button--colored mdl-button--raised mdl-js-ripple-effect mdl-color--primary">show more</button>
            </div>
          </div>
          <div className="products-container row">
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
              <div id="latest-rss-news-container" className="community-section-div mdl-cell mdl-cell--6-col mdl-cell--4-col-tablet mdl-cell--4-col-phone">
                <LatestRssNewsPosts/>
              </div>
              <div id="latest-blog-posts-container" className="community-section-div mdl-cell mdl-cell--6-col mdl-cell--4-col-tablet mdl-cell--4-col-phone">
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
