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

  return {
    getTimeAgo,
    getDeviceWidth,
    getNumberOfProducts
  };
}();
const reducer = Redux.combineReducers({
  products: productsReducer,
  users: usersReducer,
  supporters: supportersReducer
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
class HomePageTemplateOne extends React.Component {
  constructor(props) {
    super(props);
    this.state = {};
    this.updateDimensions = this.updateDimensions.bind(this);
  }

  componentWillMount() {
    // this.updateDimensions();
  }

  componentDidMount() {
    window.addEventListener("resize", this.updateDimensions);
  }

  componentWillUnmount() {
    window.removeEventListener("resize", this.updateDimensions);
  }

  updateDimensions() {
    const device = appHelpers.getDeviceWidth(window.innerWidth);
    this.setState({ device: device });
  }

  render() {
    return React.createElement(
      "div",
      { id: "homepage-version-one" },
      React.createElement(Introduction, null),
      React.createElement(LatestProductsWrapper, null),
      React.createElement(TopProductsWrapper, null),
      React.createElement(FullImageProductsWrapper, null),
      React.createElement(PaddedImageProductsWrapper, null),
      React.createElement(RoundedCornersProductsWrapper, null),
      React.createElement(RounderCornersProductsWrapper, null)
    );
  }
}

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
            "App Images Hub, right here"
          ),
          React.createElement(
            "p",
            null,
            "Welcome to appimagehub, the home of hundreds of apps which can be easily installed on any Linux distribution. Browse the apps online, from your app center or the command line."
          ),
          React.createElement(
            "div",
            { className: "actions" },
            React.createElement(
              "button",
              { className: "mdl-button mdl-js-button mdl-button--raised mdl-js-ripple-effect mdl-button--colored mdl-color--primary" },
              "browse"
            ),
            React.createElement(
              "button",
              { className: "mdl-button mdl-js-button mdl-button--raised mdl-js-ripple-effect mdl-button--colored mdl-color--primary" },
              "join"
            )
          )
        )
      )
    );
  }
}

class LatestProducts extends React.Component {
  constructor(props) {
    super(props);
    this.state = {};
  }

  componentWillReceiveProps(nextProps) {
    if (nextProps.products && !this.state.products) {
      this.setState({ products: nextProps.products.ThemesPlasma });
    }
  }

  render() {
    let latestProducts;
    if (this.state.products) {
      const limit = appHelpers.getNumberOfProducts(this.props.device);
      latestProducts = this.state.products.slice(0, limit).map((product, index) => React.createElement(
        "div",
        { key: index, className: "product square" },
        React.createElement(
          "div",
          { className: "content" },
          React.createElement(
            "div",
            { className: "product-wrapper mdl-shadow--2dp" },
            React.createElement(
              "a",
              { href: "/p/" + product.project_id },
              React.createElement(
                "div",
                { className: "product-image-container" },
                React.createElement(
                  "figure",
                  null,
                  React.createElement("img", { src: 'https://cn.pling.it/cache/200x171/img/' + product.image_small })
                )
              ),
              React.createElement(
                "div",
                { className: "product-info mdl-color--primary" },
                React.createElement(
                  "span",
                  { className: "product-info-title" },
                  product.title
                ),
                React.createElement(
                  "span",
                  { className: "product-info-description" },
                  product.description
                )
              )
            )
          )
        )
      ));
    }

    return React.createElement(
      "div",
      { id: "latest-products", className: "hp-section products-showcase" },
      React.createElement(
        "div",
        { className: "container" },
        React.createElement(
          "div",
          { className: "section-header" },
          React.createElement(
            "h3",
            { className: "mdl-color-text--primary" },
            "Round Images Layout"
          ),
          React.createElement(
            "div",
            { className: "actions" },
            React.createElement(
              "button",
              { className: "mdl-button mdl-js-button mdl-button--colored mdl-button--raised mdl-js-ripple-effect mdl-color--primary" },
              "show more"
            )
          )
        ),
        React.createElement(
          "div",
          { className: "products-container row" },
          latestProducts
        )
      )
    );
  }
}

const mapStateToLatestProductsProps = state => {
  const products = state.products;
  return {
    products
  };
};

const mapDispatchToLatestProductsProps = dispatch => {
  return {
    dispatch
  };
};

const LatestProductsWrapper = ReactRedux.connect(mapStateToLatestProductsProps, mapDispatchToLatestProductsProps)(LatestProducts);

class TopProducts extends React.Component {
  constructor(props) {
    super(props);
    this.state = {};
  }

  componentWillReceiveProps(nextProps) {
    if (nextProps.products && !this.state.products) {
      let products;
      if (nextProps.products.TopProducts.elements.length > 0) {
        products = nextProps.products.TopProducts.elements;
      } else {
        console.log(nextProps.products);
        products = nextProps.products.Apps;
      }
      this.setState({ products: products });
    }
  }

  render() {
    let topProducts;
    if (this.state.products) {
      const limit = appHelpers.getNumberOfProducts(this.props.device);
      topProducts = this.state.products.slice(0, limit).map((product, index) => React.createElement(
        "div",
        { key: index, className: "product square" },
        React.createElement(
          "div",
          { className: "content" },
          React.createElement(
            "div",
            { className: "product-wrapper mdl-shadow--2dp" },
            React.createElement(
              "a",
              { href: "/p/" + product.project_id },
              React.createElement(
                "div",
                { className: "product-image-container" },
                React.createElement(
                  "figure",
                  null,
                  React.createElement("img", { className: "squared", src: 'https://cn.pling.it/cache/200x171/img/' + product.image_small })
                )
              ),
              React.createElement(
                "div",
                { className: "product-info mdl-color--primary" },
                React.createElement(
                  "span",
                  { className: "product-info-title" },
                  product.title
                ),
                React.createElement(
                  "span",
                  { className: "product-info-description" },
                  product.description
                )
              )
            )
          )
        )
      ));
    }
    return React.createElement(
      "div",
      { id: "hottest-products", className: "hp-section products-showcase" },
      React.createElement(
        "div",
        { className: "container" },
        React.createElement(
          "div",
          { className: "section-header" },
          React.createElement(
            "h3",
            { className: "mdl-color-text--primary" },
            "Square Images Layout"
          ),
          React.createElement(
            "div",
            { className: "actions" },
            React.createElement(
              "button",
              { className: "mdl-button mdl-js-button mdl-button--colored mdl-button--raised mdl-js-ripple-effect mdl-color--primary" },
              "show more"
            )
          )
        ),
        React.createElement(
          "div",
          { className: "products-container row" },
          topProducts
        )
      )
    );
  }
}

const mapStateToTopProductsProps = state => {
  const products = state.products;
  return {
    products
  };
};

const mapDispatchToTopProductsProps = dispatch => {
  return {
    dispatch
  };
};

const TopProductsWrapper = ReactRedux.connect(mapStateToTopProductsProps, mapDispatchToTopProductsProps)(TopProducts);

class FullImageProducts extends React.Component {
  constructor(props) {
    super(props);
    this.state = {};
  }

  componentWillReceiveProps(nextProps) {
    console.log(nextProps);
    if (nextProps.products && !this.state.products) {
      let products;
      if (nextProps.products.TopProducts.elements.length > 0) {
        products = nextProps.products.TopProducts.elements;
      } else {
        products = nextProps.products.Apps;
      }
      this.setState({ products: products });
    }
  }

  render() {
    let topProducts;
    if (this.state.products) {
      const limit = appHelpers.getNumberOfProducts(this.props.device);
      topProducts = this.state.products.slice(0, limit).map((product, index) => React.createElement(
        "div",
        { key: index, className: "product square" },
        React.createElement(
          "div",
          { className: "content" },
          React.createElement(
            "div",
            { className: "product-wrapper mdl-shadow--2dp" },
            React.createElement(
              "a",
              { href: "/p/" + product.project_id },
              React.createElement(
                "div",
                { className: "product-image-container" },
                React.createElement(
                  "figure",
                  { className: "no-padding" },
                  React.createElement("img", { className: "full", src: 'https://cn.pling.it/cache/200x171/img/' + product.image_small })
                )
              ),
              React.createElement(
                "div",
                { className: "product-info music-image mdl-color--primary" },
                React.createElement(
                  "i",
                  { className: "material-icons left-icon" },
                  "play_circle_filled"
                ),
                React.createElement(
                  "span",
                  { className: "product-info-title" },
                  product.title
                ),
                React.createElement(
                  "i",
                  { className: "material-icons right-icon" },
                  "add_shopping_cart"
                )
              )
            )
          )
        )
      ));
    }
    return React.createElement(
      "div",
      { id: "hottest-products", className: "hp-section products-showcase" },
      React.createElement(
        "div",
        { className: "container" },
        React.createElement(
          "div",
          { className: "section-header" },
          React.createElement(
            "h3",
            { className: "mdl-color-text--primary" },
            "Full Images Layout"
          ),
          React.createElement(
            "div",
            { className: "actions" },
            React.createElement(
              "button",
              { className: "mdl-button mdl-js-button mdl-button--colored mdl-button--raised mdl-js-ripple-effect mdl-color--primary" },
              "show more"
            )
          )
        ),
        React.createElement(
          "div",
          { className: "products-container row" },
          topProducts
        )
      )
    );
  }
}

const mapStateToFullImageProductsProps = state => {
  const products = state.products;
  return {
    products
  };
};

const mapDispatchToFullImageProductsProps = dispatch => {
  return {
    dispatch
  };
};

const FullImageProductsWrapper = ReactRedux.connect(mapStateToFullImageProductsProps, mapDispatchToFullImageProductsProps)(FullImageProducts);

class PaddedImageProducts extends React.Component {
  constructor(props) {
    super(props);
    this.state = {};
  }

  componentWillReceiveProps(nextProps) {
    console.log(nextProps);
    if (nextProps.products && !this.state.products) {
      let products;
      if (nextProps.products.TopProducts.elements.length > 0) {
        products = nextProps.products.TopProducts.elements;
      } else {
        products = nextProps.products.Apps;
      }
      this.setState({ products: products });
    }
  }

  render() {

    let topProducts;
    if (this.state.products) {
      const limit = appHelpers.getNumberOfProducts(this.props.device);
      topProducts = this.state.products.slice(0, limit).map((product, index) => React.createElement(
        "div",
        { key: index, className: "product square" },
        React.createElement(
          "div",
          { className: "content" },
          React.createElement(
            "div",
            { className: "product-wrapper mdl-shadow--2dp" },
            React.createElement(
              "a",
              { href: "/p/" + product.project_id },
              React.createElement(
                "div",
                { className: "product-image-container" },
                React.createElement(
                  "figure",
                  { className: "no-padding" },
                  React.createElement("img", { className: "full padded", src: 'https://cn.pling.it/cache/200x171/img/' + product.image_small })
                )
              ),
              React.createElement(
                "div",
                { className: "product-info mdl-color--primary" },
                React.createElement(
                  "span",
                  { className: "product-info-title" },
                  product.title
                ),
                React.createElement(
                  "span",
                  { className: "product-info-description" },
                  product.description
                )
              )
            )
          )
        )
      ));
    }
    return React.createElement(
      "div",
      { id: "hottest-products", className: "hp-section products-showcase" },
      React.createElement(
        "div",
        { className: "container" },
        React.createElement(
          "div",
          { className: "section-header" },
          React.createElement(
            "h3",
            { className: "mdl-color-text--primary" },
            "Padded Images Layout"
          ),
          React.createElement(
            "div",
            { className: "actions" },
            React.createElement(
              "button",
              { className: "mdl-button mdl-js-button mdl-button--colored mdl-button--raised mdl-js-ripple-effect mdl-color--primary" },
              "show more"
            )
          )
        ),
        React.createElement(
          "div",
          { className: "products-container row" },
          topProducts
        )
      )
    );
  }
}

const mapStateToPaddedImageProductsProps = state => {
  const products = state.products;
  return {
    products
  };
};

const mapDispatchToPaddedImageProductsProps = dispatch => {
  return {
    dispatch
  };
};

const PaddedImageProductsWrapper = ReactRedux.connect(mapStateToPaddedImageProductsProps, mapDispatchToPaddedImageProductsProps)(PaddedImageProducts);

class RoundedCornersProducts extends React.Component {
  constructor(props) {
    super(props);
    this.state = {};
  }

  componentWillReceiveProps(nextProps) {
    console.log(nextProps);
    if (nextProps.products && !this.state.products) {
      let products;
      if (nextProps.products.TopProducts.elements.length > 0) {
        products = nextProps.products.TopProducts.elements;
      } else {
        products = nextProps.products.Apps;
      }
      this.setState({ products: products });
    }
  }

  render() {

    let topProducts;
    if (this.state.products) {
      const limit = appHelpers.getNumberOfProducts(this.props.device);
      topProducts = this.state.products.slice(0, limit).map((product, index) => React.createElement(
        "div",
        { key: index, className: "product square" },
        React.createElement(
          "div",
          { className: "content" },
          React.createElement(
            "div",
            { className: "product-wrapper mdl-shadow--2dp" },
            React.createElement(
              "a",
              { href: "/p/" + product.project_id },
              React.createElement(
                "div",
                { className: "product-image-container" },
                React.createElement(
                  "figure",
                  { className: "no-padding" },
                  React.createElement("img", { className: "rounded-corners", src: 'https://cn.pling.it/cache/200x171/img/' + product.image_small })
                )
              ),
              React.createElement(
                "div",
                { className: "product-info mdl-color--primary" },
                React.createElement(
                  "span",
                  { className: "product-info-title" },
                  product.title
                ),
                React.createElement(
                  "span",
                  { className: "product-info-description" },
                  product.description
                )
              )
            )
          )
        )
      ));
    }
    return React.createElement(
      "div",
      { id: "hottest-products", className: "hp-section products-showcase" },
      React.createElement(
        "div",
        { className: "container" },
        React.createElement(
          "div",
          { className: "section-header" },
          React.createElement(
            "h3",
            { className: "mdl-color-text--primary" },
            "Rounded Corner Images Layout"
          ),
          React.createElement(
            "div",
            { className: "actions" },
            React.createElement(
              "button",
              { className: "mdl-button mdl-js-button mdl-button--colored mdl-button--raised mdl-js-ripple-effect mdl-color--primary" },
              "show more"
            )
          )
        ),
        React.createElement(
          "div",
          { className: "products-container row" },
          topProducts
        )
      )
    );
  }
}

const mapStateToRoundedCornersProductsProps = state => {
  const products = state.products;
  return {
    products
  };
};

const mapDispatchToRoundedCornersProductsProps = dispatch => {
  return {
    dispatch
  };
};

const RoundedCornersProductsWrapper = ReactRedux.connect(mapStateToRoundedCornersProductsProps, mapDispatchToRoundedCornersProductsProps)(RoundedCornersProducts);

class RounderCornersProducts extends React.Component {
  constructor(props) {
    super(props);
    this.state = {};
  }

  componentWillReceiveProps(nextProps) {
    console.log(nextProps);
    if (nextProps.products && !this.state.products) {
      let products;
      if (nextProps.products.TopProducts.elements.length > 0) {
        products = nextProps.products.TopProducts.elements;
      } else {
        products = nextProps.products.Apps;
      }
      this.setState({ products: products });
    }
  }

  render() {

    let topProducts;
    if (this.state.products) {
      const limit = appHelpers.getNumberOfProducts(this.props.device);
      topProducts = this.state.products.slice(0, limit).map((product, index) => React.createElement(
        "div",
        { key: index, className: "product square" },
        React.createElement(
          "div",
          { className: "content" },
          React.createElement(
            "div",
            { className: "product-wrapper mdl-shadow--2dp" },
            React.createElement(
              "a",
              { href: "/p/" + product.project_id },
              React.createElement(
                "div",
                { className: "product-image-container" },
                React.createElement(
                  "figure",
                  { className: "no-padding" },
                  React.createElement("img", { className: "very-rounded-corners", src: 'https://cn.pling.it/cache/200x171/img/' + product.image_small })
                )
              ),
              React.createElement(
                "div",
                { className: "product-info mdl-color--primary" },
                React.createElement(
                  "span",
                  { className: "product-info-title" },
                  product.title
                ),
                React.createElement(
                  "span",
                  { className: "product-info-description" },
                  product.description
                )
              )
            )
          )
        )
      ));
    }
    return React.createElement(
      "div",
      { id: "hottest-products", className: "hp-section products-showcase" },
      React.createElement(
        "div",
        { className: "container" },
        React.createElement(
          "div",
          { className: "section-header" },
          React.createElement(
            "h3",
            { className: "mdl-color-text--primary" },
            "Rounder Corner Images Layout"
          ),
          React.createElement(
            "div",
            { className: "actions" },
            React.createElement(
              "button",
              { className: "mdl-button mdl-js-button mdl-button--colored mdl-button--raised mdl-js-ripple-effect mdl-color--primary" },
              "show more"
            )
          )
        ),
        React.createElement(
          "div",
          { className: "products-container row" },
          topProducts
        )
      )
    );
  }
}

const mapStateToRounderCornersProductsProps = state => {
  const products = state.products;
  return {
    products
  };
};

const mapDispatchToRounderCornersProductsProps = dispatch => {
  return {
    dispatch
  };
};

const RounderCornersProductsWrapper = ReactRedux.connect(mapStateToRounderCornersProductsProps, mapDispatchToRounderCornersProductsProps)(RounderCornersProducts);
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
    store.dispatch(setSupporters(supporters));
    store.dispatch(setUsers(users));
    this.setState({ loading: false });
  }

  render() {
    let templateDisplay;
    if (this.state.version === 1) {
      templateDisplay = React.createElement(HomePageTemplateOne, null);
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
