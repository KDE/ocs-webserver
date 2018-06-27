const { Provider, connect } = ReactRedux;
const store = Redux.createStore(reducer);

class App extends React.Component {
  constructor(props){
  	super(props);
  	this.state = {
      loading:true,
      version:1
    };
    this.updateDimensions = this.updateDimensions.bind(this);
  }

  componentWillMount() {
    // device
    this.updateDimensions();
  }

  componentDidMount() {
    // domain
    store.dispatch(setDomain(window.location.hostname));
    // env
    const env = appHelpers.getEnv(window.location.hostname);
    store.dispatch(setEnv(env));
    // device
    window.addEventListener("resize", this.updateDimensions);
    // view
    store.dispatch(setView(view));
    // products
    store.dispatch(setProducts(products));
    // filters
    store.dispatch(setFilters(filters));
    // finish loading
    this.setState({loading:false});
  }

  componentWillUnmount(){
    // device
    window.removeEventListener("resize", this.updateDimensions);
  }

  updateDimensions(){
    const device = appHelpers.getDeviceWidth(window.innerWidth);
    store.dispatch(setDevice(device));
  }

  render(){
    console.log(store.getState());
    let displayView;
    if (store.getState().view === 'home'){
      displayView = <HomePageWrapper/>;
    } else if (store.getState().view === 'explore'){
      displayView = <ExplorePageWrapper/>;
    }
    return (
      <div id="app-root">
        {displayView}
      </div>
    )
  }
}

class AppWrapper extends React.Component {
  render(){
    return (
      <Provider store={store}>
        <App/>
      </Provider>
    )
  }
}

ReactDOM.render(
    <AppWrapper />,
    document.getElementById('explore-content')
);
