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
      console.log(productsArray);
      products = productsArray.map((product, index) => React.createElement(ProductGroupItem, {
        key: index,
        product: product
      }));
    }
    return React.createElement(
      "div",
      { className: "hp-section products-showcase" },
      React.createElement(
        "div",
        { className: "container" },
        React.createElement(
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
        ),
        React.createElement(
          "div",
          { className: "products-container row" },
          products
        )
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
  users: usersReducer,
  supporters: supportersReducer,
  domain: domainReducer,
  env: envReducer,
  device: deviceReducer
});

function productsReducer(state = {}, action) {
  if (action.type === 'SET_PRODUCTS') {
    return action.products;
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

function setProducts(products) {
  return {
    type: 'SET_PRODUCTS',
    products: products
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
class HomePageTemplateTwo extends React.Component {
  render() {
    return React.createElement(
      "div",
      { id: "hompage-version-two" },
      React.createElement(FeaturedSlideshowWrapper, null),
      React.createElement(
        "div",
        { id: "top-products", className: "hp-section" },
        "top 4 products with pic and info"
      ),
      React.createElement(
        "div",
        { id: "other-products", className: "hp-section" },
        "another top 6 products with pic and info"
      ),
      React.createElement(
        "div",
        { id: "latest-products", className: "hp-section" },
        "3 columns with 3 products each"
      )
    );
  }
}
class HomePage extends React.Component {
  constructor(props) {
    super(props);
    this.state = {
      device: store.getState().device,
      products: store.getState().products
    };
  }

  componentWillReceiveProps(nextProps) {
    console.log(nextProps);
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
        React.createElement(ProductGroup, {
          products: this.state.products.LatestProducts,
          device: this.state.device,
          numRows: 1,
          title: 'New',
          link: 'https://www.appimagehub.com/browse/ord/latest/'
        }),
        React.createElement(ProductGroup, {
          products: this.state.products.LatestProducts,
          device: this.state.device,
          numRows: 1,
          title: 'Top Apps',
          link: 'https://www.appimagehub.com/browse/ord/top/'
        }),
        React.createElement(ProductGroup, {
          products: this.state.products.LatestProducts,
          device: this.state.device,
          numRows: 1,
          title: 'Top Games',
          link: 'https://www.appimagehub.com/browse/cat/6/ord/top/'
        })
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
      { id: "introduction", className: "hp-section" },
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
    // products
    store.dispatch(setProducts(products));
    // domain
    store.dispatch(setDomain(window.location.hostname));
    // env
    const env = appHelpers.getEnv(window.location.hostname);
    store.dispatch(setEnv(env));
    // device
    window.addEventListener("resize", this.updateDimensions);
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
    return React.createElement(
      "div",
      { id: "app-root" },
      React.createElement(HomePageWrapper, null)
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
