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
    if (width > 1720) {
      device = "very-huge";
    } else if (width < 1720 && width > 1500) {
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

  function getFileSize(size) {
    if (isNaN(size)) size = 0;

    if (size < 1024) return size + ' Bytes';

    size /= 1024;

    if (size < 1024) return size.toFixed(2) + ' Kb';

    size /= 1024;

    if (size < 1024) return size.toFixed(2) + ' Mb';

    size /= 1024;

    if (size < 1024) return size.toFixed(2) + ' Gb';

    size /= 1024;

    return size.toFixed(2) + ' Tb';
  }

  function generateFilterUrl(location, currentCat) {
    let link = {};
    console.log(currentCat);
    if (currentCat && currentCat !== 0) {
      link.base = "/browse/cat/" + currentCat + "/ord/";
    } else {
      link.base = "/browse/ord/";
    }
    if (location.search) link.search = location.search;
    return link;
  }

  function generateFileDownloadHash(file, env) {
    let salt;
    if (env === "test") {
      salt = "vBHnf7bbdhz120bhNsd530LsA2mkMvh6sDsCm4jKlm23D186Fj";
    } else {
      salt = "Kcn6cv7&dmvkS40Hna§4ffcvl=021nfMs2sdlPs123MChf4s0K";
    }

    const timestamp = Date.now() + 3600;
    const hash = md5(salt, file.collection_id + timestamp);
    return hash;
    /*
    $salt = PPLOAD_DOWNLOAD_SECRET;
    $collectionID = $productInfo->ppload_collection_id;
    $timestamp = time() + 3600; // one hour valid
    $hash = md5($salt . $collectionID . $timestamp);
    */
  }

  return {
    getEnv,
    getDeviceWidth,
    splitByLastDot,
    getTimeAgo,
    getFileSize,
    generateFilterUrl,
    generateFileDownloadHash
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
    if (device === "very-huge") {
      num = 7;
    } else if (device === "huge") {
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

  function calculateProductRatings(ratings) {
    let pRating;
    let totalUp = 0,
        totalDown = 0;
    ratings.forEach(function (r, index) {
      if (r.rating_active === "1") {
        if (r.user_like === "1") {
          totalUp += 1;
        } else if (r.user_dislike === "1") {
          totalDown += 1;
        }
      }
    });
    pRating = 100 / ratings.length * (totalUp - totalDown);
    return pRating;
  }

  function getActiveRatingsNumber(ratings) {
    let activeRatingsNumber = 0;
    ratings.forEach(function (r, index) {
      if (r.rating_active === "1") {
        activeRatingsNumber += 1;
      }
    });
    return activeRatingsNumber;
  }

  function getFilesSummary(files) {
    let summery = {
      downloads: 0,
      archived: 0,
      fileSize: 0,
      total: 0
    };
    files.forEach(function (file, index) {
      summery.total += 1;
      summery.fileSize += parseInt(file.size);
      summery.downloads += parseInt(file.downloaded_count);
    });

    return summery;
  }

  function checkIfLikedByUser(user, likes) {
    let likedByUser = false;
    likes.forEach(function (like, index) {
      if (user.member_id === like.member_id) {
        likedByUser = true;
      }
    });
    return likedByUser;
  }

  function getLoggedUserRatingOnProduct(user, ratings) {
    let userRating = -1;
    ratings.forEach(function (r, index) {
      if (r.member_id === user.member_id) {
        if (r.user_like === "1") {
          userRating = 1;
        } else {
          userRating = 0;
        }
      }
    });
    return userRating;
  }

  return {
    getNumberOfProducts,
    generatePaginationObject,
    calculateProductRatings,
    getActiveRatingsNumber,
    getFilesSummary,
    checkIfLikedByUser,
    getLoggedUserRatingOnProduct
  };
}();
class ProductGroupScrollWrapper extends React.Component {
  constructor(props) {
    super(props);
    this.state = {
      products: [],
      offset: 0
    };
    this.onProductGroupScroll = this.onProductGroupScroll.bind(this);
    this.loadMoreProducts = this.loadMoreProducts.bind(this);
  }

  componentWillMount() {
    window.addEventListener("scroll", this.onProductGroupScroll);
  }

  componentDidMount() {
    this.loadMoreProducts();
  }

  onProductGroupScroll() {
    const end = $("footer").offset().top;
    const viewEnd = $(window).scrollTop() + $(window).height();
    const distance = end - viewEnd;
    if (distance < 0 && this.state.loadingMoreProducts !== true) {
      this.setState({ loadingMoreProducts: true }, function () {
        this.loadMoreProducts();
      });
    }
  }

  loadMoreProducts() {
    const itemsPerScroll = 50;
    const moreProducts = store.getState().products.slice(this.state.offset, this.state.offset + itemsPerScroll);
    const products = this.state.products.concat(moreProducts);
    const offset = this.state.offset + itemsPerScroll;
    this.setState({
      products: products,
      offset: offset,
      loadingMoreProducts: false
    });
  }

  render() {
    let loadingMoreProductsDisplay;
    if (this.state.loadingMoreProducts) {
      loadingMoreProductsDisplay = React.createElement(
        "div",
        { className: "product-group-scroll-loading-container" },
        React.createElement(
          "div",
          { className: "icon-wrapper" },
          React.createElement("span", { className: "glyphicon glyphicon-refresh spinning" })
        )
      );
    }
    return React.createElement(
      "div",
      { className: "product-group-scroll-wrapper" },
      React.createElement(ProductGroup, {
        products: this.state.products,
        device: this.props.device
      }),
      loadingMoreProductsDisplay
    );
  }
}

class ProductGroup extends React.Component {
  render() {
    let products;
    if (this.props.products) {
      let productsArray = this.props.products;
      if (this.props.numRows) {
        const limit = productHelpers.getNumberOfProducts(this.props.device, this.props.numRows);
        console.log(productsArray);
        console.log(limit);
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
                React.createElement("img", { className: "very-rounded-corners", src: 'https://' + imageBaseUrl + '/cache/200x171/img/' + this.props.product.image_small }),
                React.createElement(
                  "span",
                  { className: "product-info-title" },
                  this.props.product.title
                )
              )
            ),
            React.createElement(
              "div",
              { className: "product-info" },
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
  product: productReducer,
  lightboxGallery: lightboxGalleryReducer,
  pagination: paginationReducer,
  topProducts: topProductsReducer,
  categories: categoriesReducer,
  comments: commentsReducer,
  users: usersReducer,
  user: userReducer,
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

function productReducer(state = {}, action) {
  if (action.type === 'SET_PRODUCT') {
    return action.product;
  } else if (action.type === 'SET_PRODUCT_FILES') {
    const s = Object.assign({}, state, {
      r_files: action.files
    });
    return s;
  } else if (action.type === 'SET_PRODUCT_UPDATES') {
    const s = Object.assign({}, state, {
      r_updates: action.updates
    });
    return s;
  } else if (action.type === 'SET_PRODUCT_RATINGS') {
    const s = Object.assign({}, state, {
      r_ratings: action.ratings
    });
    return s;
  } else if (action.type === 'SET_PRODUCT_LIKES') {
    const s = Object.assign({}, state, {
      r_likes: action.likes
    });
    return s;
  } else if (action.type === 'SET_PRODUCT_PLINGS') {
    const s = Object.assign({}, state, {
      r_plings: action.plings
    });
    return s;
  } else if (action.type === 'SET_PRODUCT_USER_RATINGS') {
    const s = Object.assign({}, state, {
      r_userRatings: action.userRatings
    });
    return s;
  } else if (action.type === 'SET_PRODUCT_GALLERY') {
    const s = Object.assign({}, state, {
      r_gallery: action.gallery
    });
    return s;
  } else if (action.type === 'SET_PRODUCT_COMMENTS') {
    const s = Object.assign({}, state, {
      r_comments: action.comments
    });
    return s;
  } else if (action.type === 'SET_PRODUCT_ORIGINS') {
    const s = Object.assign({}, state, {
      r_origins: action.origins
    });
    return s;
  } else if (action.type === 'SET_PRODUCT_RELATED') {
    const s = Object.assign({}, state, {
      r_related: action.related
    });
    return s;
  } else if (action.type === 'SET_PRODUCT_MORE_PRODUCTS') {
    const s = Object.assign({}, state, {
      r_more_products: action.products
    });
    return s;
  } else if (action.type === 'SET_PRODUCT_MORE_PRODUCTS_OTHER_USERS') {
    const s = Object.assign({}, state, {
      r_more_products_other_users: action.products
    });
    return s;
  } else if (action.type === 'SET_PRODUCT_TAGS') {
    const s = Object.assign({}, state, {
      r_tags_user: action.userTags,
      r_tags_system: action.systemTags
    });
    return s;
  } else {
    return state;
  }
}

function lightboxGalleryReducer(state = {}, action) {
  if (action.type === 'SHOW_LIGHTBOX') {
    const s = Object.assign({}, state, {
      show: true,
      currentItem: action.item
    });
    return s;
  } else if (action.type === 'HIDE_LIGHTBOX') {
    const s = Object.assign({}, state, {
      show: false
    });
    return s;
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

function userReducer(state = {}, action) {
  if (action.type === 'SET_USER') {
    return action.user;
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

function setProduct(product) {
  return {
    type: 'SET_PRODUCT',
    product: product
  };
}

function setProductFiles(files) {
  return {
    type: 'SET_PRODUCT_FILES',
    files: files
  };
}

function setProductUpdates(updates) {
  return {
    type: 'SET_PRODUCT_UPDATES',
    updates: updates
  };
}

function setProductRatings(ratings) {
  return {
    type: 'SET_PRODUCT_RATINGS',
    ratings: ratings
  };
}

function setProductLikes(likes) {
  return {
    type: 'SET_PRODUCT_LIKES',
    likes: likes
  };
}

function setProductPlings(plings) {
  return {
    type: 'SET_PRODUCT_PLINGS',
    plings: plings
  };
}

function setProductUserRatings(userRatings) {
  return {
    type: 'SET_PRODUCT_USER_RATINGS',
    userRatings: userRatings
  };
}

function setProductGallery(gallery) {
  return {
    type: 'SET_PRODUCT_GALLERY',
    gallery: gallery
  };
}

function setProductComments(comments) {
  return {
    type: 'SET_PRODUCT_COMMENTS',
    comments: comments
  };
}

function setProductOrigins(origins) {
  return {
    type: 'SET_PRODUCT_ORIGINS',
    origins: origins
  };
}

function setProductRelated(related) {
  return {
    type: 'SET_PRODUCT_RELATED',
    related: related
  };
}

function setProductMoreProducts(products) {
  return {
    type: 'SET_PRODUCT_MORE_PRODUCTS',
    products: products
  };
}

function setProductMoreProductsOtherUsers(products) {
  return {
    type: 'SET_PRODUCT_MORE_PRODUCTS_OTHER_USERS',
    products: products
  };
}

function setProductTags(userTags, systemTags) {
  return {
    type: 'SET_PRODUCT_TAGS',
    userTags: userTags,
    systemTags: systemTags
  };
}

function showLightboxGallery(num) {
  return {
    type: 'SHOW_LIGHTBOX',
    item: num
  };
}

function hideLightboxGallery() {
  return {
    type: 'HIDE_LIGHTBOX'
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

function setUser(user) {
  return {
    type: 'SET_USER',
    user: user
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

      if (title.length > 0) {
        titleDisplay = React.createElement(
          "div",
          { className: "explore-page-category-title" },
          React.createElement(
            "h2",
            null,
            title
          ),
          React.createElement(
            "small",
            null,
            store.getState().pagination.totalcount,
            " results"
          )
        );
      }
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
              React.createElement(ProductGroupScrollWrapper, {
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
    const categories = this.props.categories;
    let currentId;
    if (categories.current) {
      currentId = categories.current.id;
    }
    if (categories.currentSub) {
      currentId = categories.currentSub.id;
    }
    if (categories.currentSecondSub) {
      currentId = categories.currentSecondSub.id;
    }

    const link = appHelpers.generateFilterUrl(window.location, currentId);
    let linkSearch = "";
    if (link.search) {
      linkSearch = link.search;
    }

    console.log(link.base);

    return React.createElement(
      "div",
      { className: "explore-top-bar" },
      React.createElement(
        "a",
        { href: link.base + "latest" + linkSearch, className: this.props.filters.order === "latest" ? "item active" : "item" },
        "Latest"
      ),
      React.createElement(
        "a",
        { href: link.base + "top" + linkSearch, className: this.props.filters.order === "top" ? "item active" : "item" },
        "Top"
      )
    );
  }
}

const mapStateToExploreTopBarProps = state => {
  const filters = state.filters;
  const categories = state.categories;
  return {
    filters,
    categories
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
    const itemsPerPage = 1000;
    const numPages = Math.ceil(this.props.pagination.totalcount / itemsPerPage);
    const pagination = productHelpers.generatePaginationObject(numPages, window.location.pathname, this.props.currentCategoy, this.props.filters.order, this.props.pagination.page);
    this.setState({ pagination: pagination });
  }

  render() {
    let paginationDisplay;
    if (this.state.pagination && this.props.pagination.totalcount > 1000) {
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
class ProductView extends React.Component {
  constructor(props) {
    super(props);
    this.state = {
      tab: 'product',
      showDownloadSection: false
    };
    this.toggleTab = this.toggleTab.bind(this);
    this.toggleDownloadSection = this.toggleDownloadSection.bind(this);
  }

  componentDidMount() {
    let downloadTableHeight = $('#product-download-section').find('#files-tab').height();
    downloadTableHeight += 80;
    this.setState({ downloadTableHeight: downloadTableHeight });
  }

  componentWillReceiveProps(nextProps) {
    if (nextProps.product !== this.props.product) {
      this.forceUpdate();
    }
    if (nextProps.lightboxGallery !== this.props.lightboxGallery) {
      this.forceUpdate();
    }
  }

  toggleTab(tab) {
    this.setState({ tab: tab });
  }

  toggleDownloadSection() {
    let showDownloadSection = this.state.showDownloadSection === true ? false : true;
    this.setState({ showDownloadSection: showDownloadSection });
  }

  render() {
    let productGalleryLightboxDisplay;
    if (this.props.lightboxGallery.show === true) {
      productGalleryLightboxDisplay = React.createElement(ProductGalleryLightbox, {
        product: this.props.product
      });
    }

    let downloadSectionDisplayHeight;
    if (this.state.showDownloadSection === true) {
      downloadSectionDisplayHeight = this.state.downloadTableHeight;
    }

    return React.createElement(
      'div',
      { id: 'product-page' },
      React.createElement(
        'div',
        { id: 'product-download-section', style: { "height": downloadSectionDisplayHeight } },
        React.createElement(ProductViewFilesTab, {
          product: this.props.product,
          files: this.props.product.r_files
        })
      ),
      React.createElement(ProductViewHeader, {
        product: this.props.product,
        user: this.props.user,
        onDownloadBtnClick: this.toggleDownloadSection
      }),
      React.createElement(ProductViewGallery, {
        product: this.props.product
      }),
      React.createElement(ProductNavBar, {
        onTabToggle: this.toggleTab,
        tab: this.state.tab,
        product: this.props.product
      }),
      React.createElement(ProductViewContent, {
        product: this.props.product,
        user: this.props.user,
        tab: this.state.tab
      }),
      productGalleryLightboxDisplay
    );
  }
}

const mapStateToProductPageProps = state => {
  const product = state.product;
  const user = state.user;
  const lightboxGallery = state.lightboxGallery;
  return {
    product,
    user,
    lightboxGallery
  };
};

const mapDispatchToProductPageProps = dispatch => {
  return {
    dispatch
  };
};

const ProductViewWrapper = ReactRedux.connect(mapStateToProductPageProps, mapDispatchToProductPageProps)(ProductView);

class ProductViewHeader extends React.Component {
  constructor(props) {
    super(props);
    this.state = {};
  }

  render() {

    let imageBaseUrl;
    if (store.getState().env === 'live') {
      imageBaseUrl = 'cn.pling.com';
    } else {
      imageBaseUrl = 'cn.pling.it';
    }

    let productTagsDisplay;
    if (this.props.product.r_tags_user) {
      const tagsArray = this.props.product.r_tags_user.split(',');
      const tags = tagsArray.map((tag, index) => React.createElement(
        'span',
        { className: 'mdl-chip', key: index },
        React.createElement(
          'span',
          { className: 'mdl-chip__text' },
          React.createElement('span', { className: 'glyphicon glyphicon-tag' }),
          React.createElement(
            'a',
            { href: "search/projectSearchText/" + tag + "/f/tags" },
            tag
          )
        )
      ));
      productTagsDisplay = React.createElement(
        'div',
        { className: 'product-tags' },
        tags
      );
    }
    console.log(this.props);
    return React.createElement(
      'div',
      { className: 'wrapper', id: 'product-view-header' },
      React.createElement(
        'div',
        { className: 'container' },
        React.createElement(
          'div',
          { className: 'section mdl-grid' },
          React.createElement(
            'div',
            { className: 'image-container' },
            React.createElement('img', { src: 'https://' + imageBaseUrl + '/cache/140x140/img/' + this.props.product.image_small })
          ),
          React.createElement(
            'div',
            { className: 'details-container' },
            React.createElement(
              'h1',
              null,
              this.props.product.title
            ),
            React.createElement(
              'div',
              { className: 'info-row' },
              React.createElement(
                'a',
                { className: 'user', href: "/member/" + this.props.product.member_id },
                React.createElement(
                  'span',
                  { className: 'avatar' },
                  React.createElement('img', { src: this.props.product.profile_image_url })
                ),
                React.createElement(
                  'span',
                  { className: 'username' },
                  this.props.product.username
                )
              ),
              React.createElement(
                'a',
                { href: "/browse/cat/" + this.props.product.project_category_id + "/order/latest?new=1" },
                React.createElement(
                  'span',
                  null,
                  this.props.product.cat_title
                )
              ),
              productTagsDisplay
            ),
            React.createElement(
              'a',
              { onClick: this.props.onDownloadBtnClick, href: '#', className: 'mdl-button mdl-js-button mdl-button--colored mdl-button--raised mdl-js-ripple-effect mdl-color--primary' },
              'Download'
            ),
            React.createElement(
              'div',
              { id: 'product-view-header-right-side' },
              React.createElement(ProductViewHeaderLikes, {
                product: this.props.product,
                user: this.props.user
              }),
              React.createElement(ProductViewHeaderRatings, {
                product: this.props.product,
                user: this.props.user
              })
            )
          )
        )
      )
    );
  }
}

class ProductViewHeaderLikes extends React.Component {
  constructor(props) {
    super(props);
    this.state = {};
    this.onUserLike = this.onUserLike.bind(this);
  }

  componentDidMount() {
    const user = store.getState().user;
    const likedByUser = productHelpers.checkIfLikedByUser(user, this.props.product.r_likes);
    this.setState({ likesTotal: this.props.product.r_likes.length, likedByUser: likedByUser });
  }

  onUserLike() {
    if (this.props.user) {
      const url = "/p/" + this.props.product.project_id + "/followproject/";
      const self = this;
      $.ajax({ url: url, cache: false }).done(function (response) {
        // error
        if (response.status === "error") {
          self.setState({ msg: response.msg });
        } else {
          // delete
          if (response.action === "delete") {
            const likesTotal = self.state.likesTotal - 1;
            self.setState({ likesTotal: likesTotal, likedByUser: false });
          }
          // insert
          else {
              const likesTotal = self.state.likesTotal + 1;
              self.setState({ likesTotal: likesTotal, likedByUser: true });
            }
        }
      });
    } else {
      this.setState({ msg: 'please login to like' });
    }
  }

  render() {
    let cssContainerClass, cssHeartClass;
    if (this.state.likedByUser === true) {
      cssContainerClass = "liked-by-user";
      cssHeartClass = "plingheart fa heartproject fa-heart";
    } else {
      cssHeartClass = "plingheart fa fa-heart-o heartgrey";
    }

    return React.createElement(
      'div',
      { className: cssContainerClass, id: 'likes-container' },
      React.createElement(
        'div',
        { className: 'likes' },
        React.createElement('i', { className: cssHeartClass }),
        React.createElement(
          'span',
          { onClick: this.onUserLike },
          this.state.likesTotal
        )
      ),
      React.createElement(
        'div',
        { className: 'likes-label-container' },
        this.state.msg
      )
    );
  }
}

class ProductViewHeaderRatings extends React.Component {
  constructor(props) {
    super(props);
    this.state = {
      userIsOwner: '',
      action: '',
      laplace_score: this.props.product.laplace_score
    };
    this.onRatingFormResponse = this.onRatingFormResponse.bind(this);
  }

  componentDidMount() {
    let userIsOwner = false;
    if (this.props.user && this.props.user.member_id === this.props.product.member_id) {
      userIsOwner = true;
    }
    let userRating = -1;
    if (userIsOwner === false) {
      userRating = productHelpers.getLoggedUserRatingOnProduct(this.props.user, this.props.product.r_ratings);
    }
    this.setState({ userIsOwner: userIsOwner, userRating: userRating });
  }

  onRatingBtnClick(action) {
    this.setState({ showModal: false }, function () {
      this.setState({ action: action, showModal: true }, function () {
        $('#ratings-form-modal').modal('show');
      });
    });
  }

  onRatingFormResponse(response, val) {
    console.log('need to calculate laplace_score now');
    $('#ratings-form-modal').modal('hide');
  }

  render() {

    let ratingsFormModalDisplay;
    if (this.state.showModal === true) {
      ratingsFormModalDisplay = React.createElement(RatingsFormModal, {
        user: this.props.user,
        userIsOwner: this.state.userIsOwner,
        userRating: this.state.userRating,
        action: this.state.action,
        product: this.props.product,
        onRatingFormResponse: this.onRatingFormResponse
      });
    }

    let ratingsBarCss;
    if (this.props.product.laplace_score < 50) {
      ratingsBarCss = 'red';
    }

    return React.createElement(
      'div',
      { className: 'ratings-bar-container' },
      React.createElement(
        'div',
        { className: 'ratings-bar-left', onClick: () => this.onRatingBtnClick('minus') },
        React.createElement(
          'i',
          { className: 'material-icons' },
          'remove'
        )
      ),
      React.createElement(
        'div',
        { className: 'ratings-bar-holder' },
        React.createElement('div', { className: ratingsBarCss + " ratings-bar", style: { "width": this.state.laplace_score + "%" } }),
        React.createElement('div', { className: 'ratings-bar-empty', style: { "width": 100 - this.state.laplace_score + "%" } })
      ),
      React.createElement(
        'div',
        { className: 'ratings-bar-right', onClick: () => this.onRatingBtnClick('plus') },
        React.createElement(
          'i',
          { className: 'material-icons' },
          'add'
        )
      ),
      ratingsFormModalDisplay
    );
  }
}

class RatingsFormModal extends React.Component {
  constructor(props) {
    super(props);
    this.state = {
      action: this.props.action
    };
    this.submitRatingForm = this.submitRatingForm.bind(this);
  }

  componentDidMount() {
    let actionIcon;
    if (this.props.action === 'plus') {
      actionIcon = '+';
    } else if (this.props.action === 'minus') {
      actionIcon = '-';
    }
    this.setState({ action: this.props.action, actionIcon: actionIcon, text: actionIcon }, function () {
      this.forceUpdate();
    });
  }

  submitRatingForm() {
    this.setState({ loading: true }, function () {
      const self = this;
      let v;
      if (this.state.action === 'plus') {
        v = '1';
      } else {
        v = '2';
      }

      jQuery.ajax({
        data: {
          p: this.props.product.project_id,
          m: this.props.user.member_id,
          v: v,
          pm: this.props.product.member_id,
          otxt: this.state.text,
          userrate: this.props.userRating,
          msg: this.state.text
        },
        url: '/productcomment/addreplyreview/',
        method: 'post',
        error: function () {
          const msg = "Service is temporarily unavailable. Our engineers are working quickly to resolve this issue. <br/>Find out why you may have encountered this error.";
          this.setState({ msg: msg });
        },
        success: function (response) {
          self.props.onRatingFormResponse(response, v);
        }
      });
    });
  }

  render() {
    let textAreaDisplay, modalBtnDisplay;
    if (!this.props.user) {
      textAreaDisplay = React.createElement(
        'p',
        null,
        'Please login to comment'
      );
      modalBtnDisplay = React.createElement(
        'button',
        { type: 'button', className: 'btn btn-secondary', 'data-dismiss': 'modal' },
        'Close'
      );
    } else {
      if (this.props.userIsOwner) {
        textAreaDisplay = React.createElement(
          'p',
          null,
          'Project owner not allowed'
        );
        modalBtnDisplay = React.createElement(
          'button',
          { type: 'button', className: 'btn btn-secondary', 'data-dismiss': 'modal' },
          'Close'
        );
      } else if (this.state.text) {
        textAreaDisplay = React.createElement('textarea', { defaultValue: this.state.text, className: 'form-control' });
        if (this.state.loading !== true) {

          if (this.state.msg) {
            modalBtnDisplay = React.createElement(
              'p',
              null,
              this.state.msg
            );
          } else {
            modalBtnDisplay = React.createElement(
              'button',
              { onClick: this.submitRatingForm, type: 'button', className: 'btn btn-primary' },
              'Rate Now'
            );
          }
        } else {
          modalBtnDisplay = React.createElement('span', { className: 'glyphicon glyphicon-refresh spinning' });
        }
      }
    }

    return React.createElement(
      'div',
      { className: 'modal', id: 'ratings-form-modal', tabIndex: '-1', role: 'dialog' },
      React.createElement(
        'div',
        { className: 'modal-dialog', role: 'document' },
        React.createElement(
          'div',
          { className: 'modal-content' },
          React.createElement(
            'div',
            { className: 'modal-header' },
            React.createElement(
              'div',
              { className: this.props.action + " action-icon-container" },
              this.state.actionIcon
            ),
            React.createElement(
              'h5',
              { className: 'modal-title' },
              'Add Comment (min. 1 char):'
            ),
            React.createElement(
              'button',
              { type: 'button', id: 'review-modal-close', className: 'close', 'data-dismiss': 'modal', 'aria-label': 'Close' },
              React.createElement(
                'span',
                { 'aria-hidden': 'true' },
                '\xD7'
              )
            )
          ),
          React.createElement(
            'div',
            { className: 'modal-body' },
            textAreaDisplay
          ),
          React.createElement(
            'div',
            { className: 'modal-footer' },
            modalBtnDisplay
          )
        )
      )
    );
  }
}

class ProductViewGallery extends React.Component {
  constructor(props) {
    super(props);
    this.state = {
      loading: true,
      currentItem: 1,
      galleryWrapperMarginLeft: 0
    };
    this.updateDimensions = this.updateDimensions.bind(this);
    this.onLeftArrowClick = this.onLeftArrowClick.bind(this);
    this.onRightArrowClick = this.onRightArrowClick.bind(this);
    this.animateGallerySlider = this.animateGallerySlider.bind(this);
  }

  componentDidMount() {
    window.addEventListener("resize", this.updateDimensions);
    this.updateDimensions();
  }

  componentWillUnmount() {
    window.removeEventListener("resize", this.updateDimensions);
  }

  updateDimensions() {
    const productGallery = document.getElementById('product-gallery');
    const itemsWidth = 300;
    const itemsTotal = this.props.product.r_gallery.length + 1;
    this.setState({
      itemsWidth: itemsWidth,
      itemsTotal: itemsTotal,
      loading: false
    });
  }

  onLeftArrowClick() {
    let nextItem;
    if (this.state.currentItem <= 1) {
      nextItem = this.state.itemsTotal;
    } else {
      nextItem = this.state.currentItem - 1;
    }
    const marginLeft = this.state.itemsWidth * (nextItem - 1);
    this.animateGallerySlider(nextItem, marginLeft);
  }

  onRightArrowClick() {
    let nextItem;
    if (this.state.currentItem === this.state.itemsTotal) {
      nextItem = 1;
    } else {
      nextItem = this.state.currentItem + 1;
    }
    const marginLeft = this.state.itemsWidth * (nextItem - 1);
    this.animateGallerySlider(nextItem, marginLeft);
  }

  animateGallerySlider(nextItem, marginLeft) {
    this.setState({ currentItem: nextItem, galleryWrapperMarginLeft: "-" + marginLeft + "px" });
  }

  onGalleryItemClick(num) {
    store.dispatch(showLightboxGallery(num));
  }

  render() {

    let galleryDisplay;

    if (this.props.product.embed_code && this.props.product.embed_code.length > 0) {

      let imageBaseUrl;
      if (store.getState().env === 'live') {
        imageBaseUrl = 'http://cn.pling.com';
      } else {
        imageBaseUrl = 'http://cn.pling.it';
      }

      if (this.props.product.r_gallery.length > 0) {

        const itemsWidth = this.state.itemsWidth;
        const currentItem = this.state.currentItem;
        const self = this;
        const moreItems = this.props.product.r_gallery.map((gi, index) => React.createElement(
          'div',
          { key: index, onClick: () => this.onGalleryItemClick(index + 2), className: currentItem === index + 2 ? "active-gallery-item gallery-item" : "gallery-item" },
          React.createElement('img', { className: 'media-item', src: imageBaseUrl + "/img/" + gi })
        ));

        galleryDisplay = React.createElement(
          'div',
          { id: 'product-gallery' },
          React.createElement(
            'a',
            { className: 'gallery-arrow arrow-left', onClick: this.onLeftArrowClick },
            React.createElement(
              'i',
              { className: 'material-icons' },
              'chevron_left'
            )
          ),
          React.createElement(
            'div',
            { className: 'section' },
            React.createElement(
              'div',
              { style: { "width": this.state.itemsWidth * this.state.itemsTotal + "px", "marginLeft": this.state.galleryWrapperMarginLeft }, className: 'gallery-items-wrapper' },
              React.createElement('div', { onClick: () => this.onGalleryItemClick(1), dangerouslySetInnerHTML: { __html: this.props.product.embed_code }, className: this.state.currentItem === 1 ? "active-gallery-item gallery-item" : "gallery-item" }),
              moreItems
            )
          ),
          React.createElement(
            'a',
            { className: 'gallery-arrow arrow-right', onClick: this.onRightArrowClick },
            React.createElement(
              'i',
              { className: 'material-icons' },
              'chevron_right'
            )
          )
        );
      }
    }

    return React.createElement(
      'div',
      { className: 'section', id: 'product-view-gallery-container' },
      React.createElement(
        'div',
        { className: 'container' },
        React.createElement(
          'div',
          { className: 'section' },
          galleryDisplay
        )
      )
    );
  }
}

class ProductGalleryLightbox extends React.Component {
  constructor(props) {
    super(props);
    let currentItem;
    if (store.getState().lightboxGallery) {
      currentItem = store.getState().lightboxGallery.currentItem;
    } else {
      currentItem = 1;
    }
    this.state = {
      currentItem: currentItem,
      loading: true
    };
    this.updateDimensions = this.updateDimensions.bind(this);
    this.toggleNextGalleryItem = this.toggleNextGalleryItem.bind(this);
    this.togglePrevGalleryItem = this.togglePrevGalleryItem.bind(this);
    this.animateGallerySlider = this.animateGallerySlider.bind(this);
    this.onThumbnailClick = this.onThumbnailClick.bind(this);
  }

  componentDidMount() {
    window.addEventListener("resize", this.updateDimensions);
    this.updateDimensions();
  }

  componentWillUnmount() {
    window.removeEventListener("resize", this.updateDimensions);
  }

  updateDimensions() {
    const thumbnailsSectionWidth = document.getElementById('thumbnails-section').offsetWidth;
    const itemsWidth = 300;
    const itemsTotal = this.props.product.r_gallery.length + 1;
    let thumbnailsMarginLeft = 0;
    if (this.state.currentItem * itemsWidth > thumbnailsSectionWidth) {
      thumbnailsMarginLeft = thumbnailsSectionWidth - this.state.currentItem * itemsWidth;
    }
    this.setState({
      itemsWidth: itemsWidth,
      itemsTotal: itemsTotal,
      thumbnailsSectionWidth: thumbnailsSectionWidth,
      thumbnailsMarginLeft: thumbnailsMarginLeft,
      loading: false
    });
  }

  togglePrevGalleryItem() {
    let nextItem;
    if (this.state.currentItem <= 1) {
      nextItem = this.state.itemsTotal;
    } else {
      nextItem = this.state.currentItem - 1;
    }

    this.animateGallerySlider(nextItem);
  }

  toggleNextGalleryItem() {
    let nextItem;
    if (this.state.currentItem === this.state.itemsTotal) {
      nextItem = 1;
    } else {
      nextItem = this.state.currentItem + 1;
    }
    this.animateGallerySlider(nextItem);
  }

  animateGallerySlider(currentItem) {
    this.setState({ currentItem: currentItem }, function () {
      this.updateDimensions();
    });
  }

  onThumbnailClick(num) {
    this.animateGallerySlider(num);
  }

  hideLightbox() {
    store.dispatch(hideLightboxGallery());
  }

  render() {

    let imageBaseUrl;
    if (store.getState().env === 'live') {
      imageBaseUrl = 'http://cn.pling.com';
    } else {
      imageBaseUrl = 'http://cn.pling.it';
    }

    const currentItem = this.state.currentItem;
    const self = this;
    const thumbnails = this.props.product.r_gallery.map((gi, index) => React.createElement(
      'div',
      { key: index, onClick: () => self.onThumbnailClick(index + 2), className: self.state.currentItem === index + 2 ? "active thumbnail-item" : "thumbnail-item" },
      React.createElement('img', { className: 'media-item', src: imageBaseUrl + "/img/" + gi })
    ));

    let mainItemDisplay;
    if (currentItem === 1) {
      mainItemDisplay = React.createElement('div', { dangerouslySetInnerHTML: { __html: this.props.product.embed_code } });
    } else {
      const mainItem = this.props.product.r_gallery[currentItem - 2];
      mainItemDisplay = React.createElement('img', { className: 'media-item', src: imageBaseUrl + "/img/" + mainItem });
    }

    return React.createElement(
      'div',
      { id: 'product-gallery-lightbox' },
      React.createElement(
        'a',
        { id: 'close-lightbox', onClick: this.hideLightbox },
        React.createElement(
          'i',
          { className: 'material-icons' },
          'cancel'
        )
      ),
      React.createElement(
        'div',
        { id: 'lightbox-gallery-main-view' },
        React.createElement(
          'a',
          { className: 'gallery-arrow', onClick: this.togglePrevGalleryItem, id: 'arrow-left' },
          React.createElement(
            'i',
            { className: 'material-icons' },
            'chevron_left'
          )
        ),
        React.createElement(
          'div',
          { className: 'current-gallery-item' },
          mainItemDisplay
        ),
        React.createElement(
          'a',
          { className: 'gallery-arrow', onClick: this.toggleNextGalleryItem, id: 'arrow-right' },
          React.createElement(
            'i',
            { className: 'material-icons' },
            'chevron_right'
          )
        )
      ),
      React.createElement(
        'div',
        { id: 'lightbox-gallery-thumbnails' },
        React.createElement(
          'div',
          { className: 'section', id: 'thumbnails-section' },
          React.createElement(
            'div',
            { id: 'gallery-items-wrapper', style: { "width": this.state.itemsTotal * this.state.itemsWidth + "px", "marginLeft": this.state.thumbnailsMarginLeft + "px" } },
            React.createElement('div', { onClick: () => this.onThumbnailClick(1), dangerouslySetInnerHTML: { __html: this.props.product.embed_code }, className: this.state.currentItem === 1 ? "active thumbnail-item" : "thumbnail-item" }),
            thumbnails
          )
        )
      )
    );
  }
}

class ProductNavBar extends React.Component {
  render() {
    let productNavBarDisplay;
    let filesMenuItem, ratingsMenuItem, favsMenuItem, plingsMenuItem;
    if (this.props.product.r_files.length > 0) {
      filesMenuItem = React.createElement(
        'a',
        { className: this.props.tab === "files" ? "item active" : "item", onClick: () => this.props.onTabToggle('files') },
        'Files (',
        this.props.product.r_files.length,
        ')'
      );
    }
    if (this.props.product.r_ratings.length > 0) {
      const activeRatingsNumber = productHelpers.getActiveRatingsNumber(this.props.product.r_ratings);
      ratingsMenuItem = React.createElement(
        'a',
        { className: this.props.tab === "ratings" ? "item active" : "item", onClick: () => this.props.onTabToggle('ratings') },
        'Ratings & Reviews (',
        activeRatingsNumber,
        ')'
      );
    }
    if (this.props.product.r_likes.length > 0) {
      favsMenuItem = React.createElement(
        'a',
        { className: this.props.tab === "favs" ? "item active" : "item", onClick: () => this.props.onTabToggle('favs') },
        'Favs (',
        this.props.product.r_likes.length,
        ')'
      );
    }
    if (this.props.product.r_plings.length > 0) {
      plingsMenuItem = React.createElement(
        'a',
        { className: this.props.tab === "plings" ? "item active" : "item", onClick: () => this.props.onTabToggle('plings') },
        'Plings (',
        this.props.product.r_plings.length,
        ')'
      );
    }
    return React.createElement(
      'div',
      { className: 'wrapper' },
      React.createElement(
        'div',
        { className: 'container' },
        React.createElement(
          'div',
          { className: 'explore-top-bar' },
          React.createElement(
            'a',
            { className: this.props.tab === "product" ? "item active" : "item", onClick: () => this.props.onTabToggle('product') },
            'Product'
          ),
          filesMenuItem,
          ratingsMenuItem,
          favsMenuItem,
          plingsMenuItem
        )
      )
    );
  }
}

class ProductViewContent extends React.Component {
  render() {
    let currentTabDisplay;
    if (this.props.tab === 'product') {
      currentTabDisplay = React.createElement(
        'div',
        { className: 'product-tab', id: 'product-tab' },
        React.createElement('p', { dangerouslySetInnerHTML: { __html: this.props.product.description } }),
        React.createElement(ProductCommentsContainer, {
          product: this.props.product,
          user: this.props.user
        })
      );
    } else if (this.props.tab === 'files') {
      currentTabDisplay = React.createElement(ProductViewFilesTab, {
        product: this.props.product,
        files: this.props.product.r_files
      });
    } else if (this.props.tab === 'ratings') {
      currentTabDisplay = React.createElement(ProductViewRatingsTab, {
        ratings: this.props.product.r_ratings
      });
    } else if (this.props.tab === 'favs') {
      currentTabDisplay = React.createElement(ProductViewFavTab, {
        likes: this.props.product.r_likes
      });
    } else if (this.props.tab === 'plings') {
      currentTabDisplay = React.createElement(ProductViewPlingsTab, {
        plings: this.props.product.r_plings
      });
    }
    return React.createElement(
      'div',
      { className: 'wrapper' },
      React.createElement(
        'div',
        { className: 'container' },
        React.createElement(
          'div',
          { className: 'section', id: 'product-view-content-container' },
          currentTabDisplay
        )
      )
    );
  }
}

class ProductCommentsContainer extends React.Component {
  constructor(props) {
    super(props);
    this.state = {};
  }

  render() {
    let commentsDisplay;
    const cArray = categoryHelpers.convertCatChildrenObjectToArray(this.props.product.r_comments);
    if (cArray.length > 0) {
      const product = this.props.product;
      const comments = cArray.map((c, index) => {
        if (c.level === 1) {
          return React.createElement(CommentItem, { product: product, comment: c.comment, key: index, level: 1 });
        }
      });
      commentsDisplay = React.createElement(
        'div',
        { className: 'comment-list' },
        comments
      );
    }

    return React.createElement(
      'div',
      { className: 'product-view-section', id: 'product-comments-container' },
      React.createElement(
        'div',
        { className: 'section-header' },
        React.createElement(
          'h3',
          null,
          'Comments'
        ),
        React.createElement(
          'span',
          { className: 'comments-counter' },
          cArray.length,
          ' comments'
        )
      ),
      React.createElement(CommentForm, {
        user: this.props.user,
        product: this.props.product
      }),
      commentsDisplay
    );
  }
}

class CommentForm extends React.Component {
  constructor(props) {
    super(props);
    this.state = {
      text: '',
      errorMsg: '',
      errorTitle: ''
    };
    this.updateCommentText = this.updateCommentText.bind(this);
    this.submitComment = this.submitComment.bind(this);
  }

  updateCommentText(e) {
    this.setState({ text: e.target.value });
  }

  submitComment() {
    const msg = this.state.text;
    console.log(this.state.text);
    const self = this;
    jQuery.ajax({
      data: {
        p: this.props.product.project_id,
        m: this.props.user.member_id,
        msg: this.state.text
      },
      url: '/productcomment/addreply/',
      type: 'post',
      dataType: 'json',
      error: function (jqXHR, textStatus, errorThrown) {
        const results = JSON && JSON.parse(jqXHR.responseText) || $.parseJSON(jqXHR.responseText);
        self.setState({
          errorMsg: results.message,
          errorTitle: results.title,
          login_url: results.login_url,
          status: 'error'
        });
      },
      success: function (results) {
        self.setState({
          text: ''
        }, function () {
          jQuery.ajax({ data: {}, url: '/productcomment?p=' + self.props.product.project_id }, function (response) {
            console.log(response);
          });
        });
      }
    });
  }

  render() {

    let commentFormDisplay;
    if (this.props.user) {

      let submitBtnDisplay;
      if (this.state.text.length === 0) {
        submitBtnDisplay = React.createElement(
          'button',
          { disabled: 'disabled', type: 'button', className: 'mdl-button mdl-js-button mdl-button--raised mdl-js-ripple-effect mdl-button--colored mdl-color--primary' },
          'send'
        );
      } else {
        submitBtnDisplay = React.createElement(
          'button',
          { onClick: this.submitComment, type: 'button', className: 'mdl-button mdl-js-button mdl-button--raised mdl-js-ripple-effect mdl-button--colored mdl-color--primary' },
          React.createElement('span', { className: 'glyphicon glyphicon-send' }),
          'send'
        );
      }

      let errorDisplay;
      if (this.state.status === 'error') {
        errorDisplay = React.createElement(
          'div',
          { className: 'comment-form-error-display-container' },
          React.createElement('div', { dangerouslySetInnerHTML: { __html: this.state.errorTitle } }),
          React.createElement('div', { dangerouslySetInnerHTML: { __html: this.state.errorMsg } })
        );
      }

      commentFormDisplay = React.createElement(
        'div',
        { className: 'comment-form-container' },
        React.createElement(
          'span',
          null,
          'Add Comment'
        ),
        React.createElement('textarea', { className: 'form-control', onChange: this.updateCommentText, defaultValue: this.state.text }),
        errorDisplay,
        submitBtnDisplay
      );
    } else {
      commentFormDisplay = React.createElement(
        'p',
        null,
        'Please ',
        React.createElement(
          'a',
          { href: '/login?redirect=ohWn43n4SbmJZWlKUZNl2i1_s5gggiCE' },
          'login'
        ),
        ' or ',
        React.createElement(
          'a',
          { href: '/register' },
          'register'
        ),
        ' to add a comment'
      );
    }

    return React.createElement(
      'div',
      { id: 'product-page-comment-form-container' },
      commentFormDisplay
    );
  }
}

class CommentItem extends React.Component {
  constructor(props) {
    super(props);
    this.state = {};
    this.filterByCommentLevel = this.filterByCommentLevel.bind(this);
  }

  filterByCommentLevel(val) {
    if (val.level > this.props.level && this.props.comment.comment_id === val.comment.comment_parent_id) {
      return val;
    }
  }

  render() {
    let commentRepliesContainer;
    const filteredComments = categoryHelpers.convertCatChildrenObjectToArray(this.props.product.r_comments).filter(this.filterByCommentLevel);
    if (filteredComments.length > 0) {
      const product = this.props.product;
      const comments = filteredComments.map((c, index) => React.createElement(CommentItem, { product: product, comment: c.comment, key: index, level: c.level }));
      commentRepliesContainer = React.createElement(
        'div',
        { className: 'comment-item-replies-container' },
        comments
      );
    }

    let displayIsSupporter;
    if (this.props.comment.issupporter === "1") {
      displayIsSupporter = React.createElement(
        'span',
        { className: 'is-supporter-display' },
        'S'
      );
    }

    let displayIsCreater;
    if (this.props.comment.member_id === this.props.product.member_id) {
      displayIsCreater = React.createElement(
        'span',
        { className: 'is-creater-display' },
        'C'
      );
    }

    return React.createElement(
      'div',
      { className: 'comment-item' },
      React.createElement(
        'div',
        { className: 'comment-user-avatar' },
        React.createElement('img', { src: this.props.comment.profile_image_url }),
        displayIsSupporter,
        displayIsCreater
      ),
      React.createElement(
        'div',
        { className: 'comment-item-content' },
        React.createElement(
          'div',
          { className: 'comment-item-header' },
          React.createElement(
            'a',
            { className: 'comment-username', href: "/member/" + this.props.comment.member_id },
            this.props.comment.username
          ),
          React.createElement(
            'span',
            { className: 'comment-created-at' },
            appHelpers.getTimeAgo(this.props.comment.comment_created_at)
          )
        ),
        React.createElement(
          'div',
          { className: 'comment-item-text' },
          this.props.comment.comment_text
        )
      ),
      commentRepliesContainer
    );
  }
}

class ProductViewFilesTab extends React.Component {
  render() {
    let filesDisplay;
    const files = this.props.files.map((f, index) => React.createElement(ProductViewFilesTabItem, {
      product: this.props.product,
      key: index,
      file: f
    }));
    const summeryRow = productHelpers.getFilesSummary(this.props.files);
    filesDisplay = React.createElement(
      'tbody',
      null,
      files,
      React.createElement(
        'tr',
        null,
        React.createElement(
          'td',
          null,
          summeryRow.total,
          ' files (0 archived)'
        ),
        React.createElement('td', null),
        React.createElement('td', null),
        React.createElement('td', null),
        React.createElement('td', null),
        React.createElement(
          'td',
          null,
          summeryRow.downloads
        ),
        React.createElement('td', null),
        React.createElement(
          'td',
          null,
          appHelpers.getFileSize(summeryRow.fileSize)
        ),
        React.createElement('td', null),
        React.createElement('td', null)
      )
    );
    return React.createElement(
      'div',
      { id: 'files-tab', className: 'product-tab' },
      React.createElement(
        'table',
        { className: 'mdl-data-table mdl-js-data-table mdl-shadow--2dp' },
        React.createElement(
          'thead',
          null,
          React.createElement(
            'tr',
            null,
            React.createElement(
              'th',
              { className: 'mdl-data-table__cell--non-numericm' },
              'File'
            ),
            React.createElement(
              'th',
              { className: 'mdl-data-table__cell--non-numericm' },
              'Version'
            ),
            React.createElement(
              'th',
              { className: 'mdl-data-table__cell--non-numericm' },
              'Description'
            ),
            React.createElement(
              'th',
              { className: 'mdl-data-table__cell--non-numericm' },
              'Packagetype'
            ),
            React.createElement(
              'th',
              { className: 'mdl-data-table__cell--non-numericm' },
              'Architecture'
            ),
            React.createElement(
              'th',
              { className: 'mdl-data-table__cell--non-numericm' },
              'Downloads'
            ),
            React.createElement(
              'th',
              { className: 'mdl-data-table__cell--non-numericm' },
              'Date'
            ),
            React.createElement(
              'th',
              { className: 'mdl-data-table__cell--non-numericm' },
              'Filesize'
            ),
            React.createElement(
              'th',
              { className: 'mdl-data-table__cell--non-numericm' },
              'DL'
            ),
            React.createElement(
              'th',
              { className: 'mdl-data-table__cell--non-numericm' },
              'OCS-Install'
            )
          )
        ),
        filesDisplay
      )
    );
  }
}

class ProductViewFilesTabItem extends React.Component {
  constructor(props) {
    super(props);
    this.state = { downloadLink: "" };
  }

  componentDidMount() {
    let baseUrl, downloadLinkUrlAttr;
    if (store.getState().env === 'live') {
      baseUrl = 'cn.pling.com';
      downloadLinkUrlAttr = "cc.pling.com/api/";
    } else {
      baseUrl = 'cn.pling.it';
      downloadLinkUrlAttr = "cc.pling.it/api/";
    }

    const f = this.props.file;
    const fileDownloadHash = appHelpers.generateFileDownloadHash(f, store.getState().env);
    console.log(fileDownloadHash);
    // var downloadUrl = "https://<?= $_SERVER["SERVER_NAME"]?>/p/<?= $this->product->project_id ?>/startdownload?file_id=" + this.id + "&file_name=" + this.name + "&file_type=" + this.type + "&file_size=" + this.size + "&url=" + encodeURIComponent(pploadApiUri + 'files/downloadfile/id/' + this.id + '/s/' + hash + '/t/' + timetamp + '/u/' + userid + '/' + this.name);
    // var downloadLink = '<a href="' + downloadUrl + '" id="data-link' + this.id + '">' + this.name + '</a>';

    let downloadLink = "https://" + baseUrl + "/p/" + this.props.product.project_id + "/startdownload?file_id=" + f.id + "&file_name=" + f.title + "&file_type=" + f.type + "&file_size=" + f.size + "&url=" + downloadLinkUrlAttr + "files/downloadfile/id/" + f.id + "/s/" + fileDownloadHash + "/t/" + f.created_timestamp + "/u/" + this.props.product.member_id + "/" + f.title;

    /*https://david.pling.cc/p/747/startdownload?file_id=1519124607&amp;
    file_name=1519124607-download-app-old.png&amp;
    file_type=image/png&amp;
    file_size=21383&amp;
    url=https%3A%2F%2Fcc.ppload.com%2Fapi%2Ffiles%2Fdownloadfile%2Fid
    %2F1519124607%2Fs
    %2Fd66c71127c9aae29e58e03ddd85de57a%2Ft
    %2F1532003618%2Fu
    %2F%2F1519124607-download-app-old.png
    */
    this.setState({ downloadLink: downloadLink });
  }

  render() {
    const f = this.props.file;
    return React.createElement(
      'tr',
      null,
      React.createElement(
        'td',
        { className: 'mdl-data-table__cell--non-numericm' },
        React.createElement(
          'a',
          { href: this.state.downloadLink },
          f.title
        )
      ),
      React.createElement(
        'td',
        null,
        f.version
      ),
      React.createElement(
        'td',
        { className: 'mdl-data-table__cell--non-numericm' },
        f.description
      ),
      React.createElement(
        'td',
        { className: 'mdl-data-table__cell--non-numericm' },
        f.packagename
      ),
      React.createElement(
        'td',
        { className: 'mdl-data-table__cell--non-numericm' },
        f.archname
      ),
      React.createElement(
        'td',
        null,
        f.downloaded_count
      ),
      React.createElement(
        'td',
        { className: 'mdl-data-table__cell--non-numericm' },
        appHelpers.getTimeAgo(f.created_timestamp)
      ),
      React.createElement(
        'td',
        { className: 'mdl-data-table__cell--non-numericm' },
        appHelpers.getFileSize(f.size)
      ),
      React.createElement(
        'td',
        null,
        React.createElement(
          'a',
          { href: this.state.downloadLink },
          React.createElement(
            'i',
            { className: 'material-icons' },
            'cloud_download'
          )
        )
      ),
      React.createElement(
        'td',
        null,
        f.ocs_compatible
      )
    );
  }
}

class ProductViewRatingsTab extends React.Component {
  constructor(props) {
    super(props);
    this.state = {
      filter: 'active'
    };
    this.filterLikes = this.filterLikes.bind(this);
    this.filterDislikes = this.filterDislikes.bind(this);
    this.filterActive = this.filterActive.bind(this);
    this.setFilter = this.setFilter.bind(this);
  }

  filterLikes(rating) {
    if (rating.user_like === "1") {
      return rating;
    }
  }

  filterDislikes(rating) {
    if (rating.user_dislike === "1") {
      return rating;
    }
  }

  filterActive(rating) {
    if (rating.rating_active === "1") {
      return rating;
    }
  }

  setFilter(filter) {
    this.setState({ filter: filter });
  }

  render() {

    const ratingsLikes = this.props.ratings.filter(this.filterLikes);
    const ratingsDislikes = this.props.ratings.filter(this.filterDislikes);
    const ratingsActive = this.props.ratings.filter(this.filterActive);

    let ratingsDisplay;
    if (this.props.ratings.length > 0) {

      let ratings;
      if (this.state.filter === "all") {
        ratings = this.props.ratings;
      } else if (this.state.filter === "active") {
        ratings = ratingsActive;
      } else if (this.state.filter === "dislikes") {
        ratings = ratingsDislikes;
      } else if (this.state.filter === "likes") {
        ratings = ratingsLikes;
      }

      const ratingsItems = ratings.map((r, index) => React.createElement(RatingItem, {
        key: index,
        rating: r
      }));

      ratingsDisplay = React.createElement(
        'div',
        { className: 'product-ratings-list comment-list' },
        ratingsItems
      );
    }
    const subMenuItemClassName = " mdl-button mdl-js-button mdl-button--raised mdl-js-ripple-effect";
    const subMenuActiveItemClassName = "active mdl-button--colored mdl-color--primary item";
    return React.createElement(
      'div',
      { id: 'ratings-tab', className: 'product-tab' },
      React.createElement(
        'div',
        { className: 'ratings-filters-menu' },
        React.createElement(
          'span',
          { className: 'btn-container', onClick: () => this.setFilter("dislikes") },
          React.createElement(
            'a',
            { className: this.state.filter === "dislikes" ? subMenuActiveItemClassName + subMenuItemClassName : subMenuItemClassName, onClick: this.showDislikes },
            'show dislikes (',
            ratingsDislikes.length,
            ')'
          )
        ),
        React.createElement(
          'span',
          { className: 'btn-container', onClick: () => this.setFilter("likes") },
          React.createElement(
            'a',
            { onClick: this.setDislikesFilter, className: this.state.filter === "likes" ? subMenuActiveItemClassName + subMenuItemClassName : subMenuItemClassName, onClick: this.showLikes },
            'show likes (',
            ratingsLikes.length,
            ')'
          )
        ),
        React.createElement(
          'span',
          { className: 'btn-container', onClick: () => this.setFilter("active") },
          React.createElement(
            'a',
            { onClick: this.setDislikesFilter, className: this.state.filter === "active" ? subMenuActiveItemClassName + subMenuItemClassName : subMenuItemClassName, onClick: this.showActive },
            'show active reviews (',
            ratingsActive.length,
            ')'
          )
        ),
        React.createElement(
          'span',
          { className: 'btn-container', onClick: () => this.setFilter("all") },
          React.createElement(
            'a',
            { onClick: this.setDislikesFilter, className: this.state.filter === "all" ? subMenuActiveItemClassName + subMenuItemClassName : subMenuItemClassName, onClick: this.showAll },
            'show all (',
            this.props.ratings.length,
            ')'
          )
        )
      ),
      ratingsDisplay
    );
  }
}

class RatingItem extends React.Component {
  constructor(props) {
    super(props);
    this.state = {};
  }

  render() {
    return React.createElement(
      'div',
      { className: 'product-rating-item comment-item' },
      React.createElement(
        'div',
        { className: 'rating-user-avatar comment-user-avatar' },
        React.createElement('img', { src: this.props.rating.profile_image_url })
      ),
      React.createElement(
        'div',
        { className: 'rating-item-content comment-item-content' },
        React.createElement(
          'div',
          { className: 'rating-item-header comment-item-header' },
          React.createElement(
            'a',
            { href: "/member/" + this.props.rating.member_id },
            this.props.rating.username
          ),
          React.createElement(
            'span',
            { className: 'comment-created-at' },
            appHelpers.getTimeAgo(this.props.rating.created_at)
          )
        ),
        React.createElement(
          'div',
          { className: 'rating-item-text comment-item-text' },
          this.props.rating.comment_text
        )
      )
    );
  }
}

class ProductViewFavTab extends React.Component {
  constructor(props) {
    super(props);
    this.state = {};
  }
  render() {
    let favsDisplay;
    if (this.props.likes) {
      const favs = this.props.likes.map((like, index) => React.createElement(UserCardItem, {
        key: index,
        like: like
      }));
      favsDisplay = React.createElement(
        'div',
        { className: 'favs-list cards' },
        favs
      );
    }
    return React.createElement(
      'div',
      { className: 'product-tab', id: 'fav-tab' },
      favsDisplay
    );
  }
}

class ProductViewPlingsTab extends React.Component {
  constructor(props) {
    super(props);
    this.state = {};
  }
  render() {
    let plingsDisplay;
    if (this.props.plings) {
      const plings = this.props.plings.map((pling, index) => React.createElement(UserCardItem, {
        key: index,
        pling: pling
      }));
      plingsDisplay = React.createElement(
        'div',
        { className: 'plings-list cards' },
        plings
      );
    }
    return React.createElement(
      'div',
      { className: 'product-tab', id: 'plings-tab' },
      plingsDisplay
    );
  }
}

class UserCardItem extends React.Component {
  constructor(props) {
    super(props);
    this.state = {};
  }
  render() {
    let item;
    if (this.props.like) {
      item = this.props.like;
    } else if (this.props.pling) {
      item = this.props.pling;
    }

    let cardTypeDisplay;
    if (this.props.like) {
      cardTypeDisplay = React.createElement('i', { className: 'fa fa-heart myfav', 'aria-hidden': 'true' });
    } else if (this.props.pling) {
      cardTypeDisplay = React.createElement('img', { src: '/images/system/pling-btn-active.png' });
    }

    return React.createElement(
      'div',
      { className: 'user-card-item' },
      React.createElement(
        'div',
        { className: 'card-content' },
        React.createElement(
          'div',
          { className: 'user-avatar' },
          React.createElement('img', { src: item.profile_image_url })
        ),
        React.createElement(
          'span',
          { className: 'username' },
          React.createElement(
            'a',
            { href: "/member/" + item.member_id },
            item.username
          )
        ),
        React.createElement(
          'span',
          { className: 'card-type-holder' },
          cardTypeDisplay
        ),
        React.createElement(
          'span',
          { className: 'created-at' },
          appHelpers.getTimeAgo(item.created_at)
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

    // product (single)
    if (window.product) {
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
      store.dispatch(setProductTags(tagsuserJson, tagssystemJson));
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

    // user
    console.log(window.user);
    if (window.user) {
      console.log(user);
      store.dispatch(setUser(user));
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
    console.log(this.state);
    let displayView = React.createElement(HomePageWrapper, null);
    if (store.getState().view === 'explore') {
      displayView = React.createElement(ExplorePageWrapper, null);
    } else if (store.getState().view === 'product') {
      displayView = React.createElement(ProductViewWrapper, null);
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
