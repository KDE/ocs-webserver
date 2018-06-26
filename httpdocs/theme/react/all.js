window.appHelpers = function () {

  function getTimeAgo(datetime) {
    const a = timeago().format(datetime);
    return a;
  }

  function getDeviceWidth(width) {
    let device;
    if (width > 1250) {
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

  function getNumberOfProducts(device) {
    let num;
    if (device === "full") {
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
    return num;
  }

  function splitByLastDot(text) {
    var index = text.lastIndexOf('.');
    return text.slice(index + 1);
  }

  return {
    getTimeAgo,
    getDeviceWidth,
    getNumberOfProducts,
    splitByLastDot
  };
}();
const reducer = Redux.combineReducers({
  products: productsReducer,
  users: usersReducer,
  supporters: supportersReducer,
  domain: domainReducer
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
          title: 'Apps',
          link: 'https://www.appimagehub.com/browse/ord/top/'
        }),
        React.createElement(ProductGroup, {
          products: this.state.products.LatestProducts,
          device: this.state.device,
          numRows: 1,
          title: 'Games',
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
            "AppImages are self-contained apps which can simply be downloaded & run on any Linux distribution. For easy usage, download AppImageLauncher:"
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
  }

  componentDidMount() {
    store.dispatch(setProducts(products));
    console.log(window.location.hostname);
    store.dispatch(setDomain(window.location.hostname));
    console.log(store.getState());
    this.setState({ loading: false });
  }

  render() {
    let templateDisplay;
    if (this.state.version === 1) {
      templateDisplay = React.createElement(HomePageWrapper, null);
    } else if (this.state.version === 2) {
      templateDisplay = React.createElement(HomePageTemplateTwo, null);
    }
    return React.createElement(
      "div",
      { id: "app-root" },
      templateDisplay
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
