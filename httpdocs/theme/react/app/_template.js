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
      this.setState({products:nextProps.products.ThemeGTK});
    }
  }

  render(){
    let latestProducts;
    if (this.state.products){
      latestProducts = this.state.products.map((product,index) => (
        <div key={index} className="three wide column computer grid-image-container">
          <a href={"/p/"+product.project_id}>
            <img src={"https://cn.pling.it/cache/200x171/img/" + product.image_small}/>
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
      this.setState({products:nextProps.products.ThemesPlasma});
    }
  }

  render(){
    let topProducts;
    if (this.state.products){
      topProducts = this.state.products.map((product,index) => (
        <div key={index} className="three wide column computer grid-image-container">
          <a href={"/p/"+product.project_id}>
            <img src={"https://cn.pling.it/cache/280x171/img/" + product.image_small}/>
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
      </div>
    )
  }
}
