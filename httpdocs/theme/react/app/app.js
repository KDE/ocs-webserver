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
    if (window.view) store.dispatch(setView(view));

    // products
    if (window.products) {
      store.dispatch(setProducts(products));
    }

    // filters
    if (window.filters) {
      store.dispatch(setFilters(filters));
    }

    // top products
    if (window.topProducts){
      store.dispatch(setTopProducts(topProducts));
    }

    // categories
    if (window.categories) {
      store.dispatch(setCategories(categories));

      // current category
      if (window.catId) store.dispatch(setCurrentCategory(catId));

      // parent category
      if (!window.parentCat) {
        const parent_category = categoryHelpers.findParentCategory(categories);
      }

    }

    // supporters
    if (window.supporters){
      store.dispatch(setSupporters(supporters));
    }

    // comments
    if (window.comments) {
      store.dispatch(setComments(comments));
    }

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
    let displayView = <HomePageWrapper/>;
    if (store.getState().view === 'explore'){ displayView = <ExplorePageWrapper/>; }
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
