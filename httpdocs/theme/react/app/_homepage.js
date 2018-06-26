class HomePage extends React.Component {
  constructor(props){
  	super(props);
  	this.state = {
      device:store.getState().device,
      products:store.getState().products
    };
  }

  componentWillReceiveProps(nextProps) {
    console.log(nextProps);
    if (nextProps.device){
      this.setState({device:nextProps.device});
    }
    if (nextProps.products){
      this.setState({products:nextProps.products});
    }
  }

  render(){
    return (
      <div id="homepage">
        <div className="hp-wrapper">
          <Introduction
            device={this.state.device}
          />
          <ProductGroup
            products={this.state.products.LatestProducts}
            device={this.state.device}
            numRows={1}
            title={'New'}
            link={'https://www.appimagehub.com/browse/ord/latest/'}
          />
          <ProductGroup
            products={this.state.products.TopApps}
            device={this.state.device}
            numRows={1}
            title={'Top Apps'}
            link={'https://www.appimagehub.com/browse/ord/top/'}
          />
          <ProductGroup
            products={this.state.products.TopGames}
            device={this.state.device}
            numRows={1}
            title={'Top Games'}
            link={'https://www.appimagehub.com/browse/cat/6/ord/top/'}
          />
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
      <div id="introduction" className="hp-section">
        <div className="container">
          <article>
            <h2 className="mdl-color-text--primary">Welcome to AppImageHub</h2>
            <p>
              AppImages are self-contained apps which can simply be downloaded & run on any Linux distribution. For easy usage, download AppImageLauncher:
            </p>
            <div className="actions">
              <a href="https://www.appimagehub.com/p/1228228" className="mdl-button mdl-js-button mdl-button--raised mdl-js-ripple-effect mdl-button--colored mdl-color--primary">
                <img src="/theme/react/assets/img/icon-download_white.png"/> AppImageLauncher
              </a>
              <a href="https://www.appimagehub.com/browse" className="mdl-button mdl-js-button mdl-button--raised mdl-js-ripple-effect mdl-button--colored mdl-color--primary">Browse all apps</a>
            </div>
          </article>
        </div>
      </div>
    )
  }
}
