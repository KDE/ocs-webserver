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
  }

  render() {
    return React.createElement(
      "div",
      { id: "homepage-version-one" },
      React.createElement(SpotlightProductWrapper, null),
      React.createElement(LatestProductsWrapper, null),
      React.createElement(TopProductsWrapper, null),
      React.createElement(TopSupportersWrapper, null),
      React.createElement(IntroDiv, null)
    );
  }
}

class SpotlightProduct extends React.Component {
  constructor(props) {
    super(props);
    this.state = {};
  }

  componentWillReceiveProps(nextProps) {
    if (nextProps.products && !this.state.product) {
      this.setState({ product: nextProps.products.ThemeGTK[0] });
    }
  }

  render() {

    let spotlightProduct;
    if (this.state.product) {
      spotlightProduct = React.createElement(
        "div",
        { className: "ui grid segment", id: "spotlight-product" },
        React.createElement(
          "div",
          { className: "column four wide computer" },
          React.createElement("img", { className: "product-image", src: "https://cn.pling.it/cache/200x171/img/" + this.state.product.image_small })
        ),
        React.createElement(
          "div",
          { className: "column twelve wide computer" },
          React.createElement(
            "h2",
            null,
            this.state.product.title
          ),
          React.createElement("div", { className: "spotlight-product-sub-info" }),
          React.createElement(
            "div",
            { className: "spotlight-product-description" },
            this.state.product.description
          )
        )
      );
    }

    return React.createElement(
      "div",
      { id: "spotlight-product-container", className: "hp-section" },
      React.createElement(
        "div",
        { className: "ui container" },
        React.createElement(
          "div",
          { className: "row" },
          React.createElement(
            "h2",
            null,
            "in the spotlight"
          ),
          spotlightProduct
        )
      )
    );
  }
}

const mapStateToSpotlightProductProps = state => {
  const products = state.products;
  return {
    products
  };
};

const mapDispatchToSpotlightProductProps = dispatch => {
  return {
    dispatch
  };
};

const SpotlightProductWrapper = ReactRedux.connect(mapStateToSpotlightProductProps, mapDispatchToSpotlightProductProps)(SpotlightProduct);

class IntroDiv extends React.Component {
  constructor(props) {
    super(props);
    this.state = {};
  }

  render() {
    return React.createElement(
      "div",
      { id: "intro", className: "hp-section" },
      React.createElement(
        "div",
        { className: "ui container" },
        React.createElement(
          "div",
          { className: "ui grid" },
          React.createElement(
            "div",
            { className: "row" },
            React.createElement(
              "div",
              { className: "column eight wide computer" },
              React.createElement(
                "a",
                { href: "https://www.opendesktop.org/p/1175480/" },
                React.createElement("img", { id: "download-app", src: "/images/system/download-app.png" })
              )
            ),
            React.createElement(
              "div",
              { className: "column eight wide computer" },
              React.createElement(
                "a",
                { id: "become-supporter", href: "/supprt" },
                React.createElement(
                  "h1",
                  null,
                  "become a supporter"
                )
              )
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
      latestProducts = this.state.products.map((product, index) => React.createElement(
        "div",
        { key: index, className: "three wide column computer grid-image-container" },
        React.createElement(
          "a",
          { href: "/p/" + product.project_id },
          React.createElement("img", { className: "product-image", src: "https://cn.pling.it/cache/200x171/img/" + product.image_small })
        )
      ));
    }

    return React.createElement(
      "div",
      { id: "latest-products", className: "hp-section" },
      React.createElement(
        "div",
        { className: "ui container" },
        React.createElement(
          "div",
          { className: "ui grid" },
          React.createElement(
            "div",
            { className: "row" },
            React.createElement(
              "div",
              { className: "column sixtenn wide computer" },
              React.createElement(
                "h2",
                null,
                "latest products"
              )
            )
          ),
          React.createElement(
            "div",
            { className: "row" },
            latestProducts
          )
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
        products = nextProps.products.ThemesPlasma;
      }
      this.setState({ products: products });
    }
  }

  render() {
    let topProducts;
    if (this.state.products) {
      topProducts = this.state.products.map((product, index) => React.createElement(
        "div",
        { key: index, className: "three wide column computer grid-image-container" },
        React.createElement(
          "a",
          { href: "/p/" + product.project_id },
          React.createElement("img", { className: "product-image", src: "https://cn.pling.it/cache/280x171/img/" + product.image_small })
        )
      ));
    }
    return React.createElement(
      "div",
      { id: "hottest-products", className: "hp-section" },
      React.createElement(
        "div",
        { className: "ui container" },
        React.createElement(
          "div",
          { className: "ui grid" },
          React.createElement(
            "div",
            { className: "row" },
            React.createElement(
              "div",
              { className: "column sixtenn wide computer" },
              React.createElement(
                "h2",
                null,
                "hottest products"
              )
            )
          ),
          React.createElement(
            "div",
            { className: "row" },
            topProducts
          )
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

class TopSupporters extends React.Component {
  constructor(props) {
    super(props);
    this.state = {};
  }

  componentWillReceiveProps(nextProps) {
    console.log(nextProps);
    if (nextProps.supporters && !this.state.supporters) {
      this.setState({ supporters: nextProps.supporters });
    }
  }

  render() {
    let topSupporters;
    if (this.state.supporters) {
      topSupporters = this.state.supporters.map((supporter, index) => React.createElement(TopSupportersItem, {
        key: index,
        supporter: supporter
      }));
    }

    return React.createElement(
      "div",
      { id: "top-supporters", className: "hp-section" },
      React.createElement(
        "div",
        { className: "ui container" },
        React.createElement(
          "div",
          { className: "ui grid" },
          React.createElement(
            "div",
            { className: "row" },
            React.createElement(
              "div",
              { className: "column sixteen wide computer" },
              React.createElement(
                "h2",
                null,
                "top supporters "
              )
            )
          ),
          React.createElement(
            "div",
            { className: "row" },
            topSupporters
          )
        )
      )
    );
  }
}

const mapStateToTopSupportersProps = state => {
  const supporters = state.users; // temp
  return {
    supporters
  };
};

const mapDispatchToTopSupportersProps = dispatch => {
  return {
    dispatch
  };
};

const TopSupportersWrapper = ReactRedux.connect(mapStateToTopSupportersProps, mapDispatchToTopSupportersProps)(TopSupporters);

class TopSupportersItem extends React.Component {
  constructor(props) {
    super(props);
    this.state = {};
  }

  render() {
    return React.createElement(
      "div",
      { className: "four wide column computer grid-image-container" },
      React.createElement(
        "a",
        { href: "/member/" + this.props.supporter.member_id },
        React.createElement(
          "div",
          { className: "ui grid supporter-info-wrapper" },
          React.createElement(
            "div",
            { className: "eight wide column computer" },
            React.createElement("img", { src: "https://cn.pling.it/cache/280x171/img/" + this.props.supporter.avatar, onError: e => {
                e.target.src = "/images_sys/cc-icons-png/by.large.png";
              } })
          ),
          React.createElement(
            "div",
            { className: "eight wide column computer" },
            React.createElement(
              "div",
              { className: "supporter-name" },
              React.createElement(
                "h3",
                null,
                this.props.supporter.username
              )
            )
          )
        )
      )
    );
  }
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

class FeaturedSlideshow extends React.Component {
  constructor(props) {
    super(props);
    this.state = {};
  }

  componentDidMount() {
    $('.shape').shape();
  }

  onFlipButtonClick() {
    $('.shape').shape('flip down');
  }

  componentWillReceiveProps(nextProps) {
    console.log(nextProps);
  }

  render() {
    return React.createElement(
      "div",
      { id: "featured-sideshow", className: "hp-section" },
      React.createElement(
        "a",
        { onClick: this.onFlipButtonClick },
        "flip shape"
      ),
      React.createElement(
        "div",
        { className: "ui contaier" },
        React.createElement(
          "div",
          { className: "ui cube shape" },
          React.createElement(
            "div",
            { className: "sides" },
            React.createElement(
              "div",
              { className: "side" },
              React.createElement(
                "div",
                { className: "content" },
                React.createElement(
                  "div",
                  { className: "center" },
                  "1"
                )
              )
            ),
            React.createElement(
              "div",
              { className: "side" },
              React.createElement(
                "div",
                { className: "content" },
                React.createElement(
                  "div",
                  { className: "center" },
                  "2"
                )
              )
            ),
            React.createElement(
              "div",
              { className: "side" },
              React.createElement(
                "div",
                { className: "content" },
                React.createElement(
                  "div",
                  { className: "center" },
                  "3"
                )
              )
            ),
            React.createElement(
              "div",
              { className: "side active" },
              React.createElement(
                "div",
                { className: "content" },
                React.createElement(
                  "div",
                  { className: "center" },
                  "4"
                )
              )
            ),
            React.createElement(
              "div",
              { className: "side" },
              React.createElement(
                "div",
                { className: "content" },
                React.createElement(
                  "div",
                  { className: "center" },
                  "5"
                )
              )
            ),
            React.createElement(
              "div",
              { className: "side" },
              React.createElement(
                "div",
                { className: "content" },
                React.createElement(
                  "div",
                  { className: "center" },
                  "6"
                )
              )
            )
          )
        )
      )
    );
  }
}

const mapStateToFeaturedSlideshowProps = state => {
  const products = state.products;
  return {
    products
  };
};

const mapDispatchToFeaturedSlideshowProps = dispatch => {
  return {
    dispatch
  };
};

const FeaturedSlideshowWrapper = ReactRedux.connect(mapStateToFeaturedSlideshowProps, mapDispatchToFeaturedSlideshowProps)(FeaturedSlideshow);
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
