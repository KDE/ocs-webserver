window.appHelpers = function () {

  function getTimeAgo(datetime) {
    const a = timeago().format(datetime);
    return a;
  }

  return {
    getTimeAgo
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
  }

  render() {
    return React.createElement(
      "div",
      { id: "homepage-version-one" },
      React.createElement(Introduction, null),
      React.createElement(LatestProductsWrapper, null),
      React.createElement(TopProductsWrapper, null)
    );
  }
}

class Introduction extends React.Component {
  render() {
    return React.createElement(
      "div",
      { id: "Introduction", className: "hp-section" },
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
      latestProducts = this.state.products.map((product, index) => React.createElement(
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
                  React.createElement("img", { className: "mdl-shadow--2dp", src: 'https://cn.pling.it/cache/200x171/img/' + product.image_small })
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
            "h2",
            { className: "mdl-color-text--primary" },
            "latest products"
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
      topProducts = this.state.products.map((product, index) => React.createElement(
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
                  React.createElement("img", { className: "mdl-shadow--2dp", src: 'https://cn.pling.it/cache/200x171/img/' + product.image_small })
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
            "h2",
            { className: "mdl-color-text--primary" },
            "hottest products"
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

class CommunitySection extends React.Component {
  constructor(props) {
    super(props);
    this.state = {};
  }

  render() {
    return React.createElement(
      "div",
      { id: "community-section", className: "hp-section" },
      React.createElement(
        "div",
        { className: "container" },
        React.createElement(
          "div",
          { className: "mdl-content mdl-grid" },
          React.createElement(
            "div",
            { id: "latest-rss-news-container", className: "community-section-div mdl-cell mdl-cell--6-col mdl-cell--4-col-tablet mdl-cell--4-col-phone" },
            React.createElement(LatestRssNewsPosts, null)
          ),
          React.createElement(
            "div",
            { id: "latest-blog-posts-container", className: "community-section-div mdl-cell mdl-cell--6-col mdl-cell--4-col-tablet mdl-cell--4-col-phone" },
            React.createElement(LatestBlogPosts, null)
          )
        )
      )
    );
  }
}

class LatestRssNewsPosts extends React.Component {
  constructor(props) {
    super(props);
    this.state = {};
    this.getLatestRssNewsPosts = this.getLatestRssNewsPosts.bind(this);
  }

  componentDidMount() {
    this.getLatestRssNewsPosts();
  }

  getLatestRssNewsPosts() {
    const json_url = "https://blog.opendesktop.org/?json=1&callback=?";
    const self = this;
    $.getJSON(json_url, function (res) {
      self.setState({ posts: res.posts });
    });
  }

  render() {
    let rssNewsPostsDisplay;
    if (this.state.posts) {
      rssNewsPostsDisplay = this.state.posts.slice(0, 3).map((np, index) => React.createElement(
        "div",
        { className: "mdl-list__item rss-news-post", key: index },
        React.createElement(
          "div",
          { className: "mdl-list__item-text-body" },
          React.createElement(
            "h3",
            null,
            React.createElement(
              "a",
              { href: np.url },
              np.title
            )
          ),
          React.createElement("p", { dangerouslySetInnerHTML: { __html: np.excerpt } })
        )
      ));
    }
    return React.createElement(
      "div",
      { id: "latest-rss-news", className: "mdl-shadow--2dp" },
      React.createElement(
        "h2",
        { className: "mdl-color--primary mdl-color-text--white" },
        "latest rss news"
      ),
      React.createElement(
        "div",
        { className: "mdl-list" },
        rssNewsPostsDisplay
      )
    );
  }
}

class LatestBlogPosts extends React.Component {
  constructor(props) {
    super(props);
    this.state = {};
    this.getLatestBlogPosts = this.getLatestBlogPosts.bind(this);
  }

  componentDidMount() {
    this.getLatestBlogPosts();
  }

  getLatestBlogPosts() {
    const urlforum = 'https://forum.opendesktop.org/';
    const json_url = urlforum + 'latest.json';
    const self = this;
    $.ajax(json_url).then(function (result) {
      self.setState({ posts: result.topic_list.topics });
    });
  }

  render() {
    let blogPostsDisplay;
    if (this.state.posts) {
      blogPostsDisplay = this.state.posts.slice(0, 3).map((bp, index) => React.createElement(
        "div",
        { className: "mdl-list__item rss-news-post", key: index },
        React.createElement(
          "div",
          { className: "mdl-list__item-text-body" },
          React.createElement(
            "h3",
            null,
            React.createElement(
              "a",
              { href: bp.url },
              bp.title
            )
          ),
          React.createElement("p", { dangerouslySetInnerHTML: { __html: bp.excerpt } })
        )
      ));
    }
    return React.createElement(
      "div",
      { id: "latest-blog-posts", className: "mdl-shadow--2dp" },
      React.createElement(
        "h2",
        { className: "mdl-color--primary mdl-color-text--white" },
        "Latest Blog Posts"
      ),
      React.createElement(
        "div",
        { className: "mdl-list" },
        blogPostsDisplay
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
