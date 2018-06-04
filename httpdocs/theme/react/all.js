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
class Template extends React.Component {
  constructor(props) {
    super(props);
    this.state = {};
  }

  render() {
    return React.createElement(
      "div",
      { id: "template" },
      React.createElement(IntroDiv, null),
      React.createElement(LatestProductsWrapper, null),
      React.createElement(TopProductsWrapper, null),
      React.createElement(TopSupportersWrapper, null)
    );
  }
}

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
              React.createElement("img", { src: "/images/system/download-app.png" })
            ),
            React.createElement(
              "div",
              { className: "column eight wide computer" },
              React.createElement(
                "p",
                null,
                "become a supporter"
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
      this.setState({ products: nextProps.products.ThemeGTK });
    }
  }

  render() {
    let latestProducts;
    if (this.state.products) {
      latestProducts = this.state.products.map((product, index) => React.createElement(
        "div",
        { key: index, className: "two wide column computer" },
        React.createElement("img", { src: "https://cn.pling.it/cache/200x171/img/" + product.image_small })
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
              { className: "column eight wide computer" },
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
      this.setState({ products: nextProps.products.ThemesPlasma });
    }
  }

  render() {
    let topProducts;
    if (this.state.products) {
      topProducts = this.state.products.map((product, index) => React.createElement(
        "div",
        { key: index, className: "four wide column computer" },
        React.createElement("img", { src: "https://cn.pling.it/cache/280x171/img/" + product.image_small })
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
              { className: "column eight wide computer" },
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
    if (nextProps.supporters && !this.state.supporters) {
      this.setState({ supporters: nextProps.supporters });
    }
  }

  render() {
    let topSupporters;
    if (this.state.supporters) {
      topSupporters = this.state.supporters.map((supporter, index) => React.createElement(
        "div",
        { key: index, className: "four wide column computer" },
        React.createElement("img", { src: "https://cn.pling.it/cache/280x171/img/" + supporter.avatar })
      ));
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
              { className: "column eight wide computer" },
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
const { Provider, connect } = ReactRedux;
const store = Redux.createStore(reducer);

class App extends React.Component {
  constructor(props) {
    super(props);
    this.state = {
      loading: true
    };
  }

  componentDidMount() {
    store.dispatch(setProducts(products));
    store.dispatch(setSupporters(supporters));
    store.dispatch(setUsers(users));
    this.setState({ loading: false });
  }

  render() {

    return React.createElement(
      "div",
      { id: "app-root" },
      React.createElement(Template, null)
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
