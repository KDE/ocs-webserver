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

  function generateFilterUrl(location, currentCat) {
    let link = {};
    if (currentCat !== 0) {
      link.base = "/browse/cat/" + currentCat + "/ord/";
    } else {
      link.base = "/browse/ord/";
    }
    if (location.search) link.search = location.search;
    return link;
  }

  return {
    getEnv,
    getDeviceWidth,
    splitByLastDot,
    getTimeAgo,
    generateFilterUrl
  };
}();
window.categoryHelpers = function () {

  function findCurrentCategories(categories, catId) {
    let currentCategories = {};
    categories.forEach(function (mc, index) {
      if (parseInt(mc.id) === catId) {
        currentCategories.category = mc;
      } else {
        const cArray = categoryHelpers.convertCatChildrenObjectToArray(mc.children);
        cArray.forEach(function (sc, index) {
          if (parseInt(sc.id) === catId) {
            currentCategories.category = mc;
            currentCategories.subcategory = sc;
          } else {
            const scArray = categoryHelpers.convertCatChildrenObjectToArray(sc.children);
            scArray.forEach(function (ssc, index) {
              if (parseInt(ssc.id) === catId) {
                currentCategories.category = mc;
                currentCategories.subcategory = sc;
                currentCategories.secondSubCategory = ssc;
              }
            });
          }
        });
      }
    });
    return currentCategories;
  }

  function convertCatChildrenObjectToArray(children) {
    let cArray = [];
    for (var i in children) {
      cArray.push(children[i]);
    }
    return cArray;
  }

  return {
    findCurrentCategories,
    convertCatChildrenObjectToArray
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

  function generatePaginationObject(numPages, pathname, currentCategoy, order, page) {
    let pagination = [];

    let baseHref = "/browse";
    if (pathname.indexOf('cat') > -1) {
      baseHref += "/cat/" + currentCategoy;
    }

    if (page > 1) {
      const prev = {
        number: 'previous',
        link: baseHref + "/page/" + parseInt(page - 1) + "/ord/" + order
      };
      pagination.push(prev);
    }

    for (var i = 0; i < numPages; i++) {
      const p = {
        number: parseInt(i + 1),
        link: baseHref + "/page/" + parseInt(i + 1) + "/ord/" + order
      };
      pagination.push(p);
    }

    if (page < numPages) {
      const next = {
        number: 'next',
        link: baseHref + "/page/" + parseInt(page + 1) + "/ord/" + order
      };
      pagination.push(next);
    }

    return pagination;
  }

  return {
    getNumberOfProducts,
    generatePaginationObject
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
  pagination: paginationReducer,
  topProducts: topProductsReducer,
  categories: categoriesReducer,
  comments: commentsReducer,
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

function paginationReducer(state = {}, action) {
  if (action.type === 'SET_PAGINATION') {
    return action.pagination;
  } else {
    return state;
  }
}

function topProductsReducer(state = {}, action) {
  if (action.type === 'SET_TOP_PRODUCTS') {
    return action.products;
  } else {
    return state;
  }
}

function categoriesReducer(state = {}, action) {
  if (action.type === 'SET_CATEGORIES') {
    const s = Object.assign({}, state, {
      items: categories
    });
    return s;
  } else if (action.type === 'SET_CURRENT_CAT') {
    const s = Object.assign({}, state, {
      current: action.cat
    });
    return s;
  } else if (action.type === 'SET_CURRENT_SUBCAT') {
    const s = Object.assign({}, state, {
      currentSub: action.cat
    });
    return s;
  } else if (action.type === 'SET_CURRENT_SECONDSUBCAT') {
    const s = Object.assign({}, state, {
      currentSecondSub: action.cat
    });
    return s;
  } else {
    return state;
  }
}

function commentsReducer(state = {}, action) {
  if (action.type === 'SET_COMMENTS') {
    return action.comments;
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

function setPagination(pagination) {
  return {
    type: 'SET_PAGINATION',
    pagination: pagination
  };
}

function setTopProducts(topProducts) {
  return {
    type: 'SET_TOP_PRODUCTS',
    products: topProducts
  };
}

function setCategories(categories) {
  return {
    type: 'SET_CATEGORIES',
    categories: categories
  };
}

function setCurrentCategory(cat) {
  return {
    type: 'SET_CURRENT_CAT',
    cat: cat
  };
}

function setCurrentSubCategory(cat) {
  return {
    type: 'SET_CURRENT_SUBCAT',
    cat: cat
  };
}

function setCurrentSecondSubCategory(cat) {
  return {
    type: 'SET_CURRENT_SECONDSUBCAT',
    cat: cat
  };
}

function setComments(comments) {
  return {
    type: 'SET_COMMENTS',
    comments: comments
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
      products: store.getState().products,
      minHeight: 'auto'
    };

    this.updateContainerHeight = this.updateContainerHeight.bind(this);
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

  updateContainerHeight(sideBarHeight) {
    this.setState({ minHeight: sideBarHeight + 100 });
  }

  render() {

    let titleDisplay;
    if (this.props.categories) {
      let title = "";

      if (this.props.categories.currentSecondSub) {
        title = this.props.categories.currentSecondSub.title;
      } else {
        if (this.props.categories.currentSub) {
          title = this.props.categories.currentSub.title;
        } else {
          if (this.props.categories.current) {
            title = this.props.categories.current.title;
          }
        }
      }

      console.log(title);

      titleDisplay = React.createElement(
        "div",
        { className: "explore-page-category-title" },
        React.createElement(
          "h2",
          null,
          title
        )
      );
    }

    return React.createElement(
      "div",
      { id: "explore-page" },
      React.createElement(
        "div",
        { className: "wrapper" },
        React.createElement(
          "div",
          { className: "main-content-container", style: { "minHeight": this.state.minHeight } },
          React.createElement(
            "div",
            { className: "left-sidebar-container" },
            React.createElement(ExploreLeftSideBarWrapper, {
              updateContainerHeight: this.updateContainerHeight
            })
          ),
          React.createElement(
            "div",
            { className: "main-content" },
            titleDisplay,
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
              }),
              React.createElement(PaginationWrapper, null)
            )
          )
        ),
        React.createElement(
          "div",
          { className: "right-sidebar-container" },
          React.createElement(ExploreRightSideBarWrapper, null)
        )
      )
    );
  }
}

const mapStateToExploreProps = state => {
  const device = state.device;
  const products = state.products;
  const categories = state.categories;
  return {
    device,
    products,
    categories
  };
};

const mapDispatchToExploreProps = dispatch => {
  return {
    dispatch
  };
};

const ExplorePageWrapper = ReactRedux.connect(mapStateToExploreProps, mapDispatchToExploreProps)(ExplorePage);

class ExploreTopBar extends React.Component {
  constructor(props) {
    super(props);
    this.state = {};
  }

  render() {
    const link = appHelpers.generateFilterUrl(window.location, store.getState().categories.current);
    return React.createElement(
      "div",
      { className: "explore-top-bar" },
      React.createElement(
        "a",
        { href: link.base + "latest" + link.search, className: this.props.filters.order === "latest" ? "item active" : "item" },
        "Latest"
      ),
      React.createElement(
        "a",
        { href: link.base + "top" + link.search, className: this.props.filters.order === "top" ? "item active" : "item" },
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

class ExploreLeftSideBar extends React.Component {
  constructor(props) {
    super(props);
    this.state = {};
  }

  componentDidMount() {
    const sideBarHeight = $('#left-sidebar').height();
    this.props.updateContainerHeight(sideBarHeight);
  }

  render() {
    let categoryTree;
    if (this.props.categories) {
      categoryTree = this.props.categories.items.map((cat, index) => React.createElement(ExploreSideBarItem, {
        key: index,
        category: cat
      }));
    }

    return React.createElement(
      "aside",
      { className: "explore-left-sidebar", id: "left-sidebar" },
      React.createElement(
        "ul",
        null,
        React.createElement(
          "li",
          { className: "category-item" },
          React.createElement(
            "a",
            { className: this.props.categories.current === 0 ? "active" : "", href: "/browse/ord/" + filters.order },
            React.createElement(
              "span",
              { className: "title" },
              "All"
            )
          )
        ),
        categoryTree
      )
    );
  }
}

const mapStateToExploreLeftSideBarProps = state => {
  const categories = state.categories;
  const filters = state.filters;
  return {
    categories
  };
};

const mapDispatchToExploreLeftSideBarProps = dispatch => {
  return {
    dispatch
  };
};

const ExploreLeftSideBarWrapper = ReactRedux.connect(mapStateToExploreLeftSideBarProps, mapDispatchToExploreLeftSideBarProps)(ExploreLeftSideBar);

class ExploreSideBarItem extends React.Component {
  render() {
    const order = store.getState().filters.order;
    const categories = store.getState().categories;

    let currentId, currentSubId, currentSecondSubId;
    if (categories.current) {
      currentId = categories.current.id;
    }
    if (categories.currentSub) {
      currentSubId = categories.currentSub.id;
    }
    if (categories.currentSecondSub) {
      currentSecondSubId = categories.currentSecondSub.id;
    }

    let active;
    if (currentId === this.props.category.id || currentSubId === this.props.category.id || currentSecondSubId === this.props.category.id) {
      active = true;
    }

    let subcatMenu;
    if (this.props.category.has_children === true && active) {
      const cArray = categoryHelpers.convertCatChildrenObjectToArray(this.props.category.children);
      const subcategories = cArray.map((cat, index) => React.createElement(ExploreSideBarItem, {
        key: index,
        category: cat
      }));
      subcatMenu = React.createElement(
        "ul",
        null,
        subcategories
      );
    }

    return React.createElement(
      "li",
      { className: "category-item" },
      React.createElement(
        "a",
        { className: active === true ? "active" : "", href: "/browse/cat/" + this.props.category.id + "/ord/" + order + window.location.search },
        React.createElement(
          "span",
          { className: "title" },
          this.props.category.title
        ),
        React.createElement(
          "span",
          { className: "product-counter" },
          this.props.category.product_count
        )
      ),
      subcatMenu
    );
  }
}

class Pagination extends React.Component {
  constructor(props) {
    super(props);
    this.state = {};
  }

  componentDidMount() {
    const itemsPerPage = 10;
    const numPages = Math.ceil(this.props.pagination.totalcount / itemsPerPage);
    const pagination = productHelpers.generatePaginationObject(numPages, window.location.pathname, this.props.currentCategoy, this.props.filters.order, this.props.pagination.page);
    this.setState({ pagination: pagination });
  }

  render() {
    let paginationDisplay;
    if (this.state.pagination) {
      const pagination = this.state.pagination.map((pi, index) => {

        let numberDisplay;
        if (pi.number === 'previous') {
          numberDisplay = React.createElement(
            "span",
            { className: "num-wrap" },
            React.createElement(
              "i",
              { className: "material-icons" },
              "arrow_back_ios"
            ),
            React.createElement(
              "span",
              null,
              pi.number
            )
          );
        } else if (pi.number === 'next') {
          numberDisplay = React.createElement(
            "span",
            { className: "num-wrap" },
            React.createElement(
              "span",
              null,
              pi.number
            ),
            React.createElement(
              "i",
              { className: "material-icons" },
              "arrow_forward_ios"
            )
          );
        } else {
          numberDisplay = pi.number;
        }

        let cssClass;
        if (pi.number === this.props.pagination.page) {
          cssClass = "active";
        }

        return React.createElement(
          "li",
          { key: index },
          React.createElement(
            "a",
            { href: pi.link, className: cssClass },
            numberDisplay
          )
        );
      });
      paginationDisplay = React.createElement(
        "ul",
        null,
        pagination
      );
    }
    return React.createElement(
      "div",
      { id: "pagination-container" },
      React.createElement(
        "div",
        { className: "wrapper" },
        paginationDisplay
      )
    );
  }
}

const mapStateToPaginationProps = state => {
  const pagination = state.pagination;
  const filters = state.filters;
  const currentCategoy = state.categories.current;
  return {
    pagination,
    filters,
    currentCategoy
  };
};

const mapDispatchToPaginationProps = dispatch => {
  return {
    dispatch
  };
};

const PaginationWrapper = ReactRedux.connect(mapStateToPaginationProps, mapDispatchToPaginationProps)(Pagination);

class ExploreRightSideBar extends React.Component {
  constructor(props) {
    super(props);
    this.state = {};
  }

  render() {
    return React.createElement(
      "aside",
      { className: "explore-right-sidebar" },
      React.createElement(
        "div",
        { className: "ers-section" },
        React.createElement(
          "a",
          { href: "https://www.opendesktop.org/p/1175480/", target: "_blank" },
          React.createElement("img", { id: "download-app", src: "/images/system/download-app.png" })
        )
      ),
      React.createElement(
        "div",
        { className: "ers-section" },
        React.createElement(
          "a",
          { href: "/support", id: "become-a-supporter", className: "mdl-button mdl-js-button mdl-button--colored mdl-button--raised mdl-js-ripple-effect mdl-color--primary" },
          "Become a supporter"
        )
      ),
      React.createElement(
        "div",
        { className: "ers-section" },
        React.createElement(ExploreSupportersContainerWrapper, null)
      ),
      React.createElement(
        "div",
        { className: "ers-section" },
        React.createElement(RssNewsContainer, null)
      ),
      React.createElement(
        "div",
        { className: "ers-section" },
        React.createElement(BlogFeedContainer, null)
      ),
      React.createElement(
        "div",
        { className: "ers-section" },
        React.createElement(ExploreCommentsContainerWrapper, null)
      ),
      React.createElement(
        "div",
        { className: "ers-section" },
        React.createElement(ExploreTopProductsWrapper, null)
      )
    );
  }
}

const mapStateToExploreRightSideBarProps = state => {
  const categories = state.categories;
  const filters = state.filters;
  return {
    categories
  };
};

const mapDispatchToExploreRightSideBarProps = dispatch => {
  return {
    dispatch
  };
};

const ExploreRightSideBarWrapper = ReactRedux.connect(mapStateToExploreRightSideBarProps, mapDispatchToExploreRightSideBarProps)(ExploreRightSideBar);

class ExploreSupportersContainer extends React.Component {
  constructor(props) {
    super(props);
    this.state = {};
  }

  render() {
    let supportersContainer;
    if (this.props.supporters) {
      const cArray = categoryHelpers.convertCatChildrenObjectToArray(this.props.supporters);
      const supporters = cArray.map((sp, index) => React.createElement(
        "div",
        { className: "supporter-item", key: index },
        React.createElement(
          "a",
          { href: "/member/" + sp.member_id, className: "item" },
          React.createElement("img", { src: sp.profile_image_url })
        )
      ));
      supportersContainer = React.createElement(
        "div",
        { className: "supporter-list-wrapper" },
        supporters
      );
    }

    return React.createElement(
      "div",
      { id: "supporters-container", className: "sidebar-feed-container" },
      React.createElement(
        "h3",
        null,
        this.props.supporters.length,
        " people support those who create freedom"
      ),
      supportersContainer
    );
  }
}

const mapStateToExploreSupportersContainerProps = state => {
  const supporters = state.supporters;
  return {
    supporters
  };
};

const mapDispatchToExploreSupportersContainerProps = dispatch => {
  return {
    dispatch
  };
};

const ExploreSupportersContainerWrapper = ReactRedux.connect(mapStateToExploreSupportersContainerProps, mapDispatchToExploreSupportersContainerProps)(ExploreSupportersContainer);

class RssNewsContainer extends React.Component {
  constructor(props) {
    super(props);
    this.state = {};
  }

  componentDidMount() {
    const self = this;
    $.getJSON("https://blog.opendesktop.org/?json=1&callback=?", function (res) {
      self.setState({ items: res.posts });
    });
  }

  render() {
    let feedItemsContainer;
    if (this.state.items) {

      const feedItems = this.state.items.slice(0, 3).map((fi, index) => React.createElement(
        "li",
        { key: index },
        React.createElement(
          "a",
          { className: "title", href: fi.url },
          React.createElement(
            "span",
            null,
            fi.title
          )
        ),
        React.createElement(
          "span",
          { className: "info-row" },
          React.createElement(
            "span",
            { className: "date" },
            appHelpers.getTimeAgo(fi.date)
          ),
          React.createElement(
            "span",
            { className: "comment-counter" },
            fi.comment_count,
            " comments"
          )
        )
      ));

      feedItemsContainer = React.createElement(
        "ul",
        null,
        feedItems
      );
    }
    return React.createElement(
      "div",
      { id: "rss-new-container", className: "sidebar-feed-container" },
      React.createElement(
        "h3",
        null,
        "News"
      ),
      feedItemsContainer
    );
  }
}

class BlogFeedContainer extends React.Component {
  constructor(props) {
    super(props);
    this.state = {};
  }

  componentDidMount() {
    const self = this;
    $.ajax("https://forum.opendesktop.org/latest.json").then(function (result) {
      let topics = result.topic_list.topics;
      topics.sort(function (a, b) {
        return new Date(b.last_posted_at) - new Date(a.last_posted_at);
      });
      topics = topics.slice(0, 3);
      self.setState({ items: topics });
    });
  }

  render() {
    let feedItemsContainer;
    if (this.state.items) {

      const feedItems = this.state.items.map((fi, index) => React.createElement(
        "li",
        { key: index },
        React.createElement(
          "a",
          { className: "title", href: "https://forum.opendesktop.org//t/" + fi.id },
          React.createElement(
            "span",
            null,
            fi.title
          )
        ),
        React.createElement(
          "span",
          { className: "info-row" },
          React.createElement(
            "span",
            { className: "date" },
            appHelpers.getTimeAgo(fi.created_at)
          ),
          React.createElement(
            "span",
            { className: "comment-counter" },
            fi.reply_count,
            " replies"
          )
        )
      ));

      feedItemsContainer = React.createElement(
        "ul",
        null,
        feedItems
      );
    }
    return React.createElement(
      "div",
      { id: "blog-feed-container", className: "sidebar-feed-container" },
      React.createElement(
        "h3",
        null,
        "Forum"
      ),
      feedItemsContainer
    );
  }
}

class ExploreCommentsContainer extends React.Component {
  constructor(props) {
    super(props);
    this.state = {};
  }

  render() {
    let commentsContainer;
    if (this.props.comments) {
      const comments = this.props.comments.map((cm, index) => React.createElement(
        "li",
        { key: index },
        React.createElement(
          "div",
          { className: "cm-content" },
          React.createElement(
            "span",
            { className: "cm-userinfo" },
            React.createElement("img", { src: cm.profile_image_url }),
            React.createElement(
              "span",
              { className: "username" },
              React.createElement(
                "a",
                { href: "/p/" + cm.comment_target_id },
                cm.username
              )
            )
          ),
          React.createElement(
            "a",
            { className: "title", href: "/member/" + cm.member_id },
            React.createElement(
              "span",
              null,
              cm.title
            )
          ),
          React.createElement(
            "span",
            { className: "content" },
            cm.comment_text
          ),
          React.createElement(
            "span",
            { className: "info-row" },
            React.createElement(
              "span",
              { className: "date" },
              appHelpers.getTimeAgo(cm.comment_created_at)
            )
          )
        )
      ));
      commentsContainer = React.createElement(
        "ul",
        null,
        comments
      );
    }
    return React.createElement(
      "div",
      { id: "blog-feed-container", className: "sidebar-feed-container" },
      React.createElement(
        "h3",
        null,
        "Forum"
      ),
      commentsContainer
    );
  }
}

const mapStateToExploreCommentsContainerProps = state => {
  const comments = state.comments;
  return {
    comments
  };
};

const mapDispatchToExploreCommentsContainerProps = dispatch => {
  return {
    dispatch
  };
};

const ExploreCommentsContainerWrapper = ReactRedux.connect(mapStateToExploreCommentsContainerProps, mapDispatchToExploreCommentsContainerProps)(ExploreCommentsContainer);

class ExploreTopProducts extends React.Component {
  constructor(props) {
    super(props);
    this.state = {};
  }

  render() {
    let topProductsContainer;
    if (this.props.topProducts) {

      let imageBaseUrl;
      if (store.getState().env === 'live') {
        imageBaseUrl = 'cn.pling.com';
      } else {
        imageBaseUrl = 'cn.pling.it';
      }

      const topProducts = this.props.topProducts.map((tp, index) => React.createElement(
        "li",
        { key: index },
        React.createElement("img", { src: "https://" + imageBaseUrl + "/cache/40x40/img/" + tp.image_small }),
        React.createElement(
          "a",
          { href: "/p/" + tp.project_id },
          tp.title
        ),
        React.createElement(
          "span",
          { className: "cat-name" },
          tp.cat_title
        )
      ));

      topProductsContainer = React.createElement(
        "ol",
        null,
        topProducts
      );
    }
    return React.createElement(
      "div",
      { id: "top-products-container", className: "sidebar-feed-container" },
      React.createElement(
        "h3",
        null,
        "3 Months Ranking"
      ),
      React.createElement(
        "small",
        null,
        "(based on downloads)"
      ),
      topProductsContainer
    );
  }
}

const mapStateToExploreTopProductsProps = state => {
  const topProducts = state.topProducts;
  return {
    topProducts
  };
};

const mapDispatchToExploreTopProductsProps = dispatch => {
  return {
    dispatch
  };
};

const ExploreTopProductsWrapper = ReactRedux.connect(mapStateToExploreTopProductsProps, mapDispatchToExploreTopProductsProps)(ExploreTopProducts);
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
      console.log(nextProps.products);
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
              link: '/browse/ord/latest/'
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
              link: '/browse/ord/top/'
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
              link: '/browse/cat/6/ord/top/'
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
              { href: "/p/1228228", className: "mdl-button mdl-js-button mdl-button--raised mdl-js-ripple-effect mdl-button--colored mdl-color--primary" },
              React.createElement("img", { src: "/theme/react/assets/img/icon-download_white.png" }),
              " AppImageLauncher"
            ),
            React.createElement(
              "a",
              { href: "/browse", className: "mdl-button mdl-js-button mdl-button--raised mdl-js-ripple-effect mdl-button--colored mdl-color--primary" },
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
    if (window.view) store.dispatch(setView(view));

    // products
    if (window.products) {
      store.dispatch(setProducts(products));
    }

    // pagination
    if (window.pagination) {
      store.dispatch(setPagination(pagination));
    }

    // filters
    if (window.filters) {
      store.dispatch(setFilters(filters));
    }

    // top products
    if (window.topProducts) {
      store.dispatch(setTopProducts(topProducts));
    }

    // categories
    if (window.categories) {
      // set categories
      store.dispatch(setCategories(categories));
      if (window.catId) {
        // current categories
        const currentCategories = categoryHelpers.findCurrentCategories(categories, catId);
        console.log(currentCategories);
        store.dispatch(setCurrentCategory(currentCategories.category));
        store.dispatch(setCurrentSubCategory(currentCategories.subcategory));
        store.dispatch(setCurrentSecondSubCategory(currentCategories.secondSubCategory));
      }
    }

    // supporters
    if (window.supporters) {
      store.dispatch(setSupporters(supporters));
    }

    // comments
    if (window.comments) {
      store.dispatch(setComments(comments));
    }

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
