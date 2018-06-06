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
        <TopSupportersWrapper/>
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
      spotlightProduct = (
          <div className="ui grid segment" id="spotlight-product">
            <div className="column four wide computer">
              <img className="product-image" src={"https://cn.pling.it/cache/200x171/img/" + this.state.product.image_small}/>
            </div>
            <div className="column twelve wide computer">
              <h2>{this.state.product.title}</h2>
              <div className="spotlight-product-sub-info">
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
          <div className="row">
            <h2>in the spotlight</h2>
            {spotlightProduct}
          </div>
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
        <div className="ui container">
          <div className="ui grid">
            <div className="row">
              <div className="column eight wide computer">
                <a href="https://www.opendesktop.org/p/1175480/">
                  <img id="download-app" src="/images/system/download-app.png"/>
                </a>
              </div>
              <div className="column eight wide computer">
                <a id="become-supporter" href="/supprt"><h1>become a supporter</h1></a>
              </div>
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
        <div key={index} className="three wide column computer grid-image-container">
          <a href={"/p/"+product.project_id}>
            <img className="product-image" src={"https://cn.pling.it/cache/200x171/img/" + product.image_small}/>
          </a>
        </div>
      ));
    }

    return (
      <div id="latest-products" className="hp-section">
        <div className="ui container">
          <div className="ui grid">
            <div className="row">
              <div className="column sixtenn wide computer">
                <h2>latest products</h2>
              </div>
            </div>
            <div className="row">
              {latestProducts}
            </div>
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
        <div key={index} className="three wide column computer grid-image-container">
          <a href={"/p/"+product.project_id}>
            <img className="product-image" src={"https://cn.pling.it/cache/280x171/img/" + product.image_small}/>
          </a>
        </div>
      ));
    }
    return (
      <div id="hottest-products" className="hp-section">
        <div className="ui container">
          <div className="ui grid">
            <div className="row">
              <div className="column sixtenn wide computer">
                <h2>hottest products</h2>
              </div>
            </div>
            <div className="row">
              {topProducts}
            </div>
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
        <div className="ui container grid">
          <div className="row">
            <div id="latest-rss-news-container" className="column eight wide computer">
              <LatestRssNewsPosts/>
            </div>
            <div id="latest-blog-posts-container" className="column eight wide computer">
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
        <div className="item rss-news-post" key={index}>
          <h3><a href={np.url}>{np.title}</a></h3>
          <p dangerouslySetInnerHTML={{__html:np.excerpt}}></p>
        </div>
      ));
    }
    return(
      <div id="latest-rss-news">
        <h2>latest rss news</h2>
        <div className="ui menu vertical">
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
        <div className="item rss-news-post" key={index}>
          <h3><a href={bp.url}>{bp.title}</a></h3>
          <p dangerouslySetInnerHTML={{__html:bp.excerpt}}></p>
        </div>
      ));
    }
    return (
      <div id="latest-blog-posts">
        <h2>Latest Blog Posts</h2>
        <div className="ui menu vertical">
          {blogPostsDisplay}
        </div>
      </div>
    )
  }
}

class TopSupporters extends React.Component {
  constructor(props){
  	super(props);
  	this.state = {};
  }

  componentWillReceiveProps(nextProps) {
    console.log(nextProps);
    if (nextProps.supporters && !this.state.supporters){
      this.setState({supporters:nextProps.supporters});
    }
  }

  render(){
    let topSupporters;
    if (this.state.supporters){
      topSupporters = this.state.supporters.map((supporter,index) => (
        <TopSupportersItem
          key={index}
          supporter={supporter}
        />
      ));
    }

    return (
      <div id="top-supporters" className="hp-section">
        <div className="ui container">
          <div className="ui grid">
            <div className="row">
              <div className="column sixteen wide computer">
                <h2>top supporters </h2>
              </div>
            </div>
            <div className="row">
              {topSupporters}
            </div>
          </div>
        </div>
      </div>
    )
  }
}

const mapStateToTopSupportersProps = (state) => {
  const supporters = state.users // temp
  return {
    supporters
  }
}

const mapDispatchToTopSupportersProps = (dispatch) => {
  return {
    dispatch
  }
}

const TopSupportersWrapper = ReactRedux.connect(
  mapStateToTopSupportersProps,
  mapDispatchToTopSupportersProps
)(TopSupporters)

class TopSupportersItem extends React.Component {
  constructor(props){
  	super(props);
  	this.state = {};
  }

  render(){
    return (
      <div className="four wide column computer grid-image-container">
        <a href={"/member/"+this.props.supporter.member_id}>
          <div className="ui grid supporter-info-wrapper">
            <div className="eight wide column computer">
              <img src={"https://cn.pling.it/cache/280x171/img/" + this.props.supporter.avatar} onError={(e)=>{e.target.src="/images_sys/cc-icons-png/by.large.png"}}/>
            </div>
            <div className="eight wide column computer">
              <div className="supporter-name">
                  <h3>{this.props.supporter.username}</h3>
              </div>
            </div>
          </div>
        </a>
      </div>
    )
  }
}
