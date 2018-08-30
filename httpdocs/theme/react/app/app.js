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

    // product (single)
    if (window.product){
      store.dispatch(setProduct(product));
      store.dispatch(setProductFiles(filesJson));
      store.dispatch(setProductUpdates(updatesJson));
      store.dispatch(setProductRatings(ratingsJson));
      store.dispatch(setProductLikes(likeJson));
      store.dispatch(setProductPlings(projectplingsJson));
      store.dispatch(setProductUserRatings(ratingOfUserJson));
      store.dispatch(setProductGallery(galleryPicturesJson));
      store.dispatch(setProductComments(commentsJson));
      store.dispatch(setProductOrigins(originsJson));
      store.dispatch(setProductRelated(relatedJson));
      store.dispatch(setProductMoreProducts(moreProductsJson));
      store.dispatch(setProductMoreProductsOtherUsers(moreProductsOfOtherUsrJson));
      store.dispatch(setProductTags(tagsuserJson,tagssystemJson));
    }

    // pagination
    if (window.pagination){
      store.dispatch(setPagination(pagination));
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
      // set categories
      store.dispatch(setCategories(categories));
      if (window.catId){
        // current categories
        const currentCategories = categoryHelpers.findCurrentCategories(categories,catId);
        store.dispatch(setCurrentCategory(currentCategories.category));
        store.dispatch(setCurrentSubCategory(currentCategories.subcategory));
        store.dispatch(setCurrentSecondSubCategory(currentCategories.secondSubCategory));
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

    // user
    if (window.user){
      store.dispatch(setUser(user));
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
    else if (store.getState().view === 'product'){ displayView = <ProductViewWrapper/>}
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
