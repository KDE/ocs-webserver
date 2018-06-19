class HomePageTemplateOne extends React.Component {
  constructor(props){
  	super(props);
  	this.state = {};
    this.updateDimensions = this.updateDimensions.bind(this);
  }

  componentWillMount(){
    // this.updateDimensions();
  }

  componentDidMount() {
    window.addEventListener("resize", this.updateDimensions);
  }

  componentWillUnmount(){
    window.removeEventListener("resize", this.updateDimensions);
  }

  updateDimensions(){
    const device = appHelpers.getDeviceWidth(window.innerWidth);
    this.setState({device:device});
  }

  render(){
    return (
      <div id="homepage-version-one">
        <Introduction/>
        <LatestProductsWrapper/>
        <TopProductsWrapper/>
        <FullImageProductsWrapper/>
        <PaddedImageProductsWrapper/>
      </div>
    )
  }
}

class Introduction extends React.Component {
  render(){
    return (
      <div id="introduction" className="hp-section">
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
      const limit = appHelpers.getNumberOfProducts(this.props.device);
      latestProducts = this.state.products.slice(0,limit).map((product,index) => (
        <div key={index} className="product square">
            <div className="content">
              <div className="product-wrapper mdl-shadow--2dp">
                <a href={"/p/"+product.project_id}>
                  <div className="product-image-container">
                    <figure>
                      <img src={'https://cn.pling.it/cache/200x171/img/' + product.image_small} />
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
            <h3  className="mdl-color-text--primary">Round Images Layout</h3>
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
      const limit = appHelpers.getNumberOfProducts(this.props.device);
      topProducts = this.state.products.slice(0,limit).map((product,index) => (
        <div key={index} className="product square">
            <div className="content">
              <div className="product-wrapper mdl-shadow--2dp">
                <a href={"/p/"+product.project_id}>
                  <div className="product-image-container">
                    <figure>
                      <img className="squared" src={'https://cn.pling.it/cache/200x171/img/' + product.image_small} />
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
            <h3 className="mdl-color-text--primary">Square Images Layout</h3>
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

class FullImageProducts extends React.Component {
  constructor(props){
  	super(props);
  	this.state = {};
  }

  componentWillReceiveProps(nextProps) {
    console.log(nextProps);
    if (nextProps.products && !this.state.products){
      let products;
      if (nextProps.products.TopProducts.elements.length > 0){
        products = nextProps.products.TopProducts.elements;
      } else {
        products = nextProps.products.Apps;
      }
      this.setState({products:products});
    }
  }

  render(){
    let topProducts;
    if (this.state.products){
      const limit = appHelpers.getNumberOfProducts(this.props.device);
      topProducts = this.state.products.slice(0,limit).map((product,index) => (
        <div key={index} className="product square">
            <div className="content">
              <div className="product-wrapper mdl-shadow--2dp">
                <a href={"/p/"+product.project_id}>
                  <div className="product-image-container">
                    <figure className="no-padding">
                      <img className="full" src={'https://cn.pling.it/cache/200x171/img/' + product.image_small} />
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
            <h3 className="mdl-color-text--primary">Full Images Layout</h3>
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

const mapStateToFullImageProductsProps = (state) => {
  const products = state.products;
  return {
    products
  }
}

const mapDispatchToFullImageProductsProps = (dispatch) => {
  return {
    dispatch
  }
}

const FullImageProductsWrapper = ReactRedux.connect(
  mapStateToFullImageProductsProps,
  mapDispatchToFullImageProductsProps
)(FullImageProducts)

class PaddedImageProducts extends React.Component {
  constructor(props){
  	super(props);
  	this.state = {};
  }

  componentWillReceiveProps(nextProps) {
    console.log(nextProps);
    if (nextProps.products && !this.state.products){
      let products;
      if (nextProps.products.TopProducts.elements.length > 0){
        products = nextProps.products.TopProducts.elements;
      } else {
        products = nextProps.products.Apps;
      }
      this.setState({products:products});
    }
  }

  render(){
    let topProducts;
    if (this.state.products){
      const limit = appHelpers.getNumberOfProducts(this.props.device);
      topProducts = this.state.products.slice(0,limit).map((product,index) => (
        <div key={index} className="product square">
            <div className="content">
              <div className="product-wrapper mdl-shadow--2dp">
                <a href={"/p/"+product.project_id}>
                  <div className="product-image-container">
                    <figure className="no-padding">
                      <img className="full padded" src={'https://cn.pling.it/cache/200x171/img/' + product.image_small} />
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
            <h3 className="mdl-color-text--primary">Padded Images Layout</h3>
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

const mapStateToPaddedImageProductsProps = (state) => {
  const products = state.products;
  return {
    products
  }
}

const mapDispatchToPaddedImageProductsProps = (dispatch) => {
  return {
    dispatch
  }
}

const PaddedImageProductsWrapper = ReactRedux.connect(
  mapStateToPaddedImageProductsProps,
  mapDispatchToPaddedImageProductsProps
)(PaddedImageProducts)
