class Template extends React.Component {
  constructor(props){
  	super(props);
  	this.state = {};
  }

  render(){
    return (
      <div id="template">
        <IntroDiv/>
        <LatestProductsWrapper/>
        <TopProductsWrapper/>
        <TopSupportersWrapper/>
      </div>
    )
  }
}

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
                <img src="/images/system/download-app.png"/>
              </div>
              <div className="column eight wide computer">
                <p>become a supporter</p>
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
      this.setState({products:nextProps.products.ThemeGTK});
    }
  }

  render(){
    let latestProducts;
    if (this.state.products){
      latestProducts = this.state.products.map((product,index) => (
        <div key={index} className="two wide column computer">
          <img src={"https://cn.pling.it/cache/200x171/img/" + product.image_small}/>
        </div>
      ));
    }
    return (
      <div id="latest-products" className="hp-section">
        <div className="ui container">
          <div className="ui grid">
            <div className="row">
              <div className="column eight wide computer">
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
      this.setState({products:nextProps.products.ThemesPlasma});
    }
  }

  render(){
    let topProducts;
    if (this.state.products){
      topProducts = this.state.products.map((product,index) => (
        <div key={index} className="four wide column computer">
          <img src={"https://cn.pling.it/cache/280x171/img/" + product.image_small}/>
        </div>
      ));
    }
    return (
      <div id="hottest-products" className="hp-section">
        <div className="ui container">
          <div className="ui grid">
            <div className="row">
              <div className="column eight wide computer">
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

class TopSupporters extends React.Component {
  constructor(props){
  	super(props);
  	this.state = {};
  }

  componentWillReceiveProps(nextProps) {
    if (nextProps.supporters && !this.state.supporters){
      this.setState({supporters:nextProps.supporters});
    }
  }

  render(){
    let topSupporters;
    if (this.state.supporters){
      topSupporters = this.state.supporters.map((supporter,index) => (
        <div key={index} className="four wide column computer">
          <img src={"https://cn.pling.it/cache/280x171/img/" + supporter.avatar}/>
        </div>
      ));
    }
    return (
      <div id="top-supporters" className="hp-section">
        <div className="ui container">
          <div className="ui grid">
            <div className="row">
              <div className="column eight wide computer">
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
