window.appHelpers = function () {

  function getEnv(domain) {
    let env;
    if (this.splitByLastDot(domain) === 'com') {
      env = 'live';
    } else {
      env = 'test';
    }
    return env;
  }

  function getDeviceWidth(width) {
    let device;
    if (width > 1500) {
      device = "huge";
    } else if (width < 1500 && width > 1250) {
      device = "full";
    } else if (width < 1250 && width >= 1000) {
      device = "large";
    } else if (width < 1000 && width >= 661) {
      device = "mid";
    } else if (width < 661 && width >= 400) {
      device = "tablet";
    } else if (width < 400) {
      device = "phone";
    }
    return device;
  }

  function splitByLastDot(text) {
    var index = text.lastIndexOf('.');
    return text.slice(index + 1);
  }

  function getTimeAgo(datetime) {
    const a = timeago().format(datetime);
    return a;
  }

  return {
    getEnv,
    getDeviceWidth,
    splitByLastDot,
    getTimeAgo
  };
}();
window.productHelpers = function () {

  function getNumberOfProducts(device, numRows) {
    let num;
    if (device === "huge") {
      num = 6;
    } else if (device === "full") {
      num = 5;
    } else if (device === "large") {
      num = 4;
    } else if (device === "mid") {
      num = 3;
    } else if (device === "tablet") {
      num = 2;
    } else if (device === "phone") {
      num = 1;
    }
    if (numRows) num = num * numRows;
    return num;
  }

  return {
    getNumberOfProducts
  };
}();
class ProductGroup extends React.Component {
  render() {
    let products;
    if (this.props.products) {
      let productsArray = this.props.products;
      if (this.props.numRows) {
        const limit = productHelpers.getNumberOfProducts(this.props.device, this.props.numRows);
        productsArray = productsArray.slice(0, limit);
      }
      products = productsArray.map((product, index) => React.createElement(ProductGroupItem, {
        key: index,
        product: product
      }));
    }

    let sectionHeader;
    if (this.props.title) {
      sectionHeader = React.createElement(
        "div",
        { className: "section-header" },
        React.createElement(
          "h3",
          { className: "mdl-color-text--primary" },
          this.props.title
        ),
        React.createElement(
          "div",
          { className: "actions" },
          React.createElement(
            "a",
            { href: this.props.link, className: "mdl-button mdl-js-button mdl-button--colored mdl-button--raised mdl-js-ripple-effect mdl-color--primary" },
            "see more"
          )
        )
      );
    }
    return React.createElement(
      "div",
      { className: "products-showcase" },
      sectionHeader,
      React.createElement(
        "div",
        { className: "products-container row" },
        products
      )
    );
  }
}

class ProductGroupItem extends React.Component {
  render() {
    let imageBaseUrl;
    if (store.getState().env === 'live') {
      imageBaseUrl = 'cn.pling.com';
    } else {
      imageBaseUrl = 'cn.pling.it';
    }
    return React.createElement(
      "div",
      { className: "product square" },
      React.createElement(
        "div",
        { className: "content" },
        React.createElement(
          "div",
          { className: "product-wrapper mdl-shadow--2dp" },
          React.createElement(
            "a",
            { href: "/p/" + this.props.product.project_id },
            React.createElement(
              "div",
              { className: "product-image-container" },
              React.createElement(
                "figure",
                null,
                React.createElement("img", { className: "very-rounded-corners", src: 'https://' + imageBaseUrl + '/cache/200x171/img/' + this.props.product.image_small })
              )
            ),
            React.createElement(
              "div",
              { className: "product-info" },
              React.createElement(
                "span",
                { className: "product-info-title" },
                this.props.product.title
              ),
              React.createElement(
                "span",
                { className: "product-info-description" },
                this.props.product.description
              )
            )
          )
        )
      )
    );
  }
}
const reducer = Redux.combineReducers({
  products: productsReducer,
  categories: categoriesReducer,
  users: usersReducer,
  supporters: supportersReducer,
  domain: domainReducer,
  env: envReducer,
  device: deviceReducer,
  view: viewReducer,
  filters: filtersReducer
});

/* reducers */

function productsReducer(state = {}, action) {
  if (action.type === 'SET_PRODUCTS') {
    return action.products;
  } else {
    return state;
  }
}

function categoriesReducer(state = {}, action) {
  if (action.type === 'SET_CATEGORIES') {
    return action.categories;
  } else {
    return state;
  }
}

function usersReducer(state = {}, action) {
  if (action.type === 'SET_USERS') {
    return action.users;
  } else {
    return state;
  }
}

function supportersReducer(state = {}, action) {
  if (action.type === 'SET_SUPPORTERS') {
    return action.supporters;
  } else {
    return state;
  }
}

function domainReducer(state = {}, action) {
  if (action.type === 'SET_DOMAIN') {
    return action.domain;
  } else {
    return state;
  }
}

function envReducer(state = {}, action) {
  if (action.type === 'SET_ENV') {
    return action.env;
  } else {
    return state;
  }
}

function deviceReducer(state = {}, action) {
  if (action.type === 'SET_DEVICE') {
    return action.device;
  } else {
    return state;
  }
}

function viewReducer(state = {}, action) {
  if (action.type === 'SET_VIEW') {
    return action.view;
  } else {
    return state;
  }
}

function filtersReducer(state = {}, action) {
  if (action.type === 'SET_FILTERS') {
    return action.filters;
  } else {
    return state;
  }
}

/* /reducers */

/* dispatch */

function setProducts(products) {
  return {
    type: 'SET_PRODUCTS',
    products: products
  };
}

function setCategories(categories) {
  return {
    type: 'SET_CATEGORIES',
    categories: categories
  };
}

function setUsers(users) {
  return {
    type: 'SET_USERS',
    users: users
  };
}

function setSupporters(supporters) {
  return {
    type: 'SET_SUPPORTERS',
    supporters: supporters
  };
}

function setDomain(domain) {
  return {
    type: 'SET_DOMAIN',
    domain: domain
  };
}

function setEnv(env) {
  return {
    type: 'SET_ENV',
    env: env
  };
}

function setDevice(device) {
  return {
    type: 'SET_DEVICE',
    device: device
  };
}

function setView(view) {
  return {
    type: 'SET_VIEW',
    view: view
  };
}

function setFilters(filters) {
  return {
    type: 'SET_FILTERS',
    filters: filters
  };
}

/* /dispatch */
class ExplorePage extends React.Component {
  constructor(props) {
    super(props);
    this.state = {
      device: store.getState().device,
      products: store.getState().products
    };
  }

  componentWillReceiveProps(nextProps) {
    if (nextProps.device) {
      this.setState({ device: nextProps.device });
    }
    if (nextProps.products) {
      this.setState({ products: nextProps.products });
    }
    if (nextProps.filters) {
      this.setState({ filters: filters });
    }
  }

  render() {
    return React.createElement(
      "div",
      { id: "explore-page" },
      React.createElement(
        "div",
        { className: "wrapper" },
        React.createElement(
          "div",
          { className: "section" },
          React.createElement(
            "div",
            { className: "container mdl-grid" },
            React.createElement(
              "div",
              { className: "sidebar-container mdl-cell--3-col mdl-cell--2-col-tablet" },
              React.createElement(ExploreSideBarWrapper, null)
            ),
            React.createElement(
              "div",
              { className: "main-content mdl-cell--9-col  mdl-cell--6-col-tablet" },
              React.createElement(
                "div",
                { className: "top-bar" },
                React.createElement(ExploreTopBarWrapper, null)
              ),
              React.createElement(
                "div",
                { className: "explore-products-container" },
                React.createElement(ProductGroup, {
                  products: this.state.products,
                  device: this.state.device
                })
              )
            )
          )
        )
      )
    );
  }
}

const mapStateToExploreProps = state => {
  const device = state.device;
  const products = state.products;
  return {
    device,
    products
  };
};

const mapDispatchToExploreProps = dispatch => {
  return {
    dispatch
  };
};

const ExplorePageWrapper = ReactRedux.connect(mapStateToExploreProps, mapDispatchToExploreProps)(ExplorePage);

class ExploreSideBar extends React.Component {
  constructor(props) {
    super(props);
    this.state = {};
  }
  render() {
    console.log(this.props);
    return React.createElement(
      "aside",
      { className: "explore-sidebar" },
      React.createElement(
        "ul",
        null,
        React.createElement(
          "li",
          null,
          React.createElement(
            "a",
            { href: "#" },
            "category"
          )
        ),
        React.createElement(
          "li",
          { className: "active" },
          React.createElement(
            "a",
            { href: "#" },
            "category"
          ),
          React.createElement(
            "ul",
            null,
            React.createElement(
              "li",
              null,
              React.createElement(
                "a",
                { href: "#" },
                "subcategory"
              )
            ),
            React.createElement(
              "li",
              null,
              React.createElement(
                "a",
                { href: "#" },
                "subcategory"
              )
            ),
            React.createElement(
              "li",
              null,
              React.createElement(
                "a",
                { href: "#" },
                "subcategory"
              )
            ),
            React.createElement(
              "li",
              null,
              React.createElement(
                "a",
                { href: "#" },
                "subcategory"
              )
            ),
            React.createElement(
              "li",
              null,
              React.createElement(
                "a",
                { href: "#" },
                "subcategory"
              )
            ),
            React.createElement(
              "li",
              null,
              React.createElement(
                "a",
                { href: "#" },
                "subcategory"
              )
            )
          )
        ),
        React.createElement(
          "li",
          null,
          React.createElement(
            "a",
            { href: "#" },
            "category"
          )
        ),
        React.createElement(
          "li",
          null,
          React.createElement(
            "a",
            { href: "#" },
            "category"
          )
        ),
        React.createElement(
          "li",
          null,
          React.createElement(
            "a",
            { href: "#" },
            "category"
          )
        ),
        React.createElement(
          "li",
          null,
          React.createElement(
            "a",
            { href: "#" },
            "category"
          )
        )
      )
    );
  }
}

const mapStateToExploreSideBarProps = state => {
  const categories = state.categories;
  return {
    categories
  };
};

const mapDispatchToExploreSideBarProps = dispatch => {
  return {
    dispatch
  };
};

const ExploreSideBarWrapper = ReactRedux.connect(mapStateToExploreSideBarProps, mapDispatchToExploreSideBarProps)(ExploreSideBar);

class ExploreTopBar extends React.Component {
  constructor(props) {
    super(props);
    this.state = {};
  }

  render() {
    return React.createElement(
      "div",
      { className: "explore-top-bar" },
      React.createElement(
        "a",
        { className: this.props.filters.order === "latest" ? "item active" : "item" },
        "Latest"
      ),
      React.createElement(
        "a",
        { className: this.props.filters.order === "top" ? "item active" : "item" },
        "Top"
      )
    );
  }
}

const mapStateToExploreTopBarProps = state => {
  const filters = state.filters;
  return {
    filters
  };
};

const mapDispatchToExploreTopBarProps = dispatch => {
  return {
    dispatch
  };
};

const ExploreTopBarWrapper = ReactRedux.connect(mapStateToExploreTopBarProps, mapDispatchToExploreTopBarProps)(ExploreTopBar);
class HomePage extends React.Component {
  constructor(props) {
    super(props);
    this.state = {
      device: store.getState().device,
      products: store.getState().products
    };
  }

  componentWillReceiveProps(nextProps) {
    if (nextProps.device) {
      this.setState({ device: nextProps.device });
    }
    if (nextProps.products) {
      this.setState({ products: nextProps.products });
    }
  }

  render() {
    return React.createElement(
      "div",
      { id: "homepage" },
      React.createElement(
        "div",
        { className: "hp-wrapper" },
        React.createElement(Introduction, {
          device: this.state.device
        }),
        React.createElement(
          "div",
          { className: "section" },
          React.createElement(
            "div",
            { className: "container" },
            React.createElement(ProductGroup, {
              products: this.state.products.LatestProducts,
              device: this.state.device,
              numRows: 1,
              title: 'New',
              link: 'https://www.appimagehub.com/browse/ord/latest/'
            })
          )
        ),
        React.createElement(
          "div",
          { className: "section" },
          React.createElement(
            "div",
            { className: "container" },
            React.createElement(ProductGroup, {
              products: this.state.products.TopApps,
              device: this.state.device,
              numRows: 1,
              title: 'Top Apps',
              link: 'https://www.appimagehub.com/browse/ord/top/'
            })
          )
        ),
        React.createElement(
          "div",
          { className: "section" },
          React.createElement(
            "div",
            { className: "container" },
            React.createElement(ProductGroup, {
              products: this.state.products.TopGames,
              device: this.state.device,
              numRows: 1,
              title: 'Top Games',
              link: 'https://www.appimagehub.com/browse/cat/6/ord/top/'
            })
          )
        )
      )
    );
  }
}

const mapStateToHomePageProps = state => {
  const device = state.device;
  const products = state.products;
  return {
    device,
    products
  };
};

const mapDispatchToHomePageProps = dispatch => {
  return {
    dispatch
  };
};

const HomePageWrapper = ReactRedux.connect(mapStateToHomePageProps, mapDispatchToHomePageProps)(HomePage);

class Introduction extends React.Component {
  render() {
    return React.createElement(
      "div",
      { id: "introduction", className: "section" },
      React.createElement(
        "div",
        { className: "container" },
        React.createElement(
          "article",
          null,
          React.createElement(
            "h2",
            { className: "mdl-color-text--primary" },
            "Welcome to AppImageHub"
          ),
          React.createElement(
            "p",
            null,
            "AppImages are self-contained apps which can simply be downloaded & run on any Linux distribution. For easy integration, download AppImageLauncher:"
          ),
          React.createElement(
            "div",
            { className: "actions" },
            React.createElement(
              "a",
              { href: "https://www.appimagehub.com/p/1228228", className: "mdl-button mdl-js-button mdl-button--raised mdl-js-ripple-effect mdl-button--colored mdl-color--primary" },
              React.createElement("img", { src: "/theme/react/assets/img/icon-download_white.png" }),
              " AppImageLauncher"
            ),
            React.createElement(
              "a",
              { href: "https://www.appimagehub.com/browse", className: "mdl-button mdl-js-button mdl-button--raised mdl-js-ripple-effect mdl-button--colored mdl-color--primary" },
              "Browse all apps"
            )
          )
        )
      )
    );
  }
}
const { Provider, connect } = ReactRedux;
const store = Redux.createStore(reducer);

class App extends React.Component {
  constructor(props) {
    super(props);
    this.state = {
      loading: true,
      version: 1
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
    if (view) store.dispatch(setView(view));
    // filters
    if (filters) store.dispatch(setFilters(filters));
    // products
    if (products) store.dispatch(setProducts(products));
    // categories
    if (categories) store.dispatch(setCategories(categories));
    // finish loading
    this.setState({ loading: false });
  }

  componentWillUnmount() {
    // device
    window.removeEventListener("resize", this.updateDimensions);
  }

  updateDimensions() {
    const device = appHelpers.getDeviceWidth(window.innerWidth);
    store.dispatch(setDevice(device));
  }

  render() {
    let displayView = React.createElement(HomePageWrapper, null);
    if (store.getState().view === 'explore') {
      displayView = React.createElement(ExplorePageWrapper, null);
    }
    return React.createElement(
      "div",
      { id: "app-root" },
      displayView
    );
  }
}

class AppWrapper extends React.Component {
  render() {
    return React.createElement(
      Provider,
      { store: store },
      React.createElement(App, null)
    );
  }
}

ReactDOM.render(React.createElement(AppWrapper, null), document.getElementById('explore-content'));
