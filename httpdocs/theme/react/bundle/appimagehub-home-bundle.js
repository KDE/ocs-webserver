"use strict";

window.appHelpers = function () {
  function getEnv(domain) {
    var env;

    if (this.splitByLastDot(domain) === 'com' || this.splitByLastDot(domain) === 'org') {
      env = 'live';
    } else {
      env = 'test';
    }

    return env;
  }

  function getDeviceWidth(width) {
    var device;

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
    var a = timeago().format(datetime);
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
    var link = {};

    if (currentCat && currentCat !== 0) {
      link.base = "/browse/cat/" + currentCat + "/ord/";
    } else {
      link.base = "/browse/ord/";
    }

    if (location.search) link.search = location.search;
    return link;
  }

  function generateFileDownloadHash(file, env) {
    var salt;

    if (env === "test") {
      salt = "vBHnf7bbdhz120bhNsd530LsA2mkMvh6sDsCm4jKlm23D186Fj";
    } else {
      salt = "Kcn6cv7&dmvkS40Hna§4ffcvl=021nfMs2sdlPs123MChf4s0K";
    }

    var timestamp = Math.floor(new Date().getTime() / 1000 + 3600);
    var hash = md5(salt + file.collection_id + timestamp);
    return hash;
  }

  return {
    getEnv: getEnv,
    getDeviceWidth: getDeviceWidth,
    splitByLastDot: splitByLastDot,
    getTimeAgo: getTimeAgo,
    getFileSize: getFileSize,
    generateFilterUrl: generateFilterUrl,
    generateFileDownloadHash: generateFileDownloadHash
  };
}();
"use strict";

window.categoryHelpers = function () {
  function findCurrentCategories(categories, catId) {
    var currentCategories = {};
    categories.forEach(function (mc, index) {
      if (parseInt(mc.id) === catId) {
        currentCategories.category = mc;
      } else {
        var cArray = categoryHelpers.convertCatChildrenObjectToArray(mc.children);
        cArray.forEach(function (sc, index) {
          if (parseInt(sc.id) === catId) {
            currentCategories.category = mc;
            currentCategories.subcategory = sc;
          } else {
            var scArray = categoryHelpers.convertCatChildrenObjectToArray(sc.children);
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
    var cArray = [];

    for (var i in children) {
      cArray.push(children[i]);
    }

    return cArray;
  }

  return {
    findCurrentCategories: findCurrentCategories,
    convertCatChildrenObjectToArray: convertCatChildrenObjectToArray
  };
}();
"use strict";

window.productHelpers = function () {
  function getNumberOfProducts(device, numRows) {
    var num;

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
    var pagination = [];
    var baseHref = "/browse";

    if (pathname.indexOf('cat') > -1) {
      baseHref += "/cat/" + currentCategoy;
    }

    if (page > 1) {
      var prev = {
        number: 'previous',
        link: baseHref + "/page/" + parseInt(page - 1) + "/ord/" + order
      };
      pagination.push(prev);
    }

    for (var i = 0; i < numPages; i++) {
      var p = {
        number: parseInt(i + 1),
        link: baseHref + "/page/" + parseInt(i + 1) + "/ord/" + order
      };
      pagination.push(p);
    }

    if (page < numPages) {
      var next = {
        number: 'next',
        link: baseHref + "/page/" + parseInt(page + 1) + "/ord/" + order
      };
      pagination.push(next);
    }

    return pagination;
  }

  function calculateProductRatings(ratings) {
    var pRating;
    var totalUp = 0,
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
    var activeRatingsNumber = 0;
    ratings.forEach(function (r, index) {
      if (r.rating_active === "1") {
        activeRatingsNumber += 1;
      }
    });
    return activeRatingsNumber;
  }

  function getFilesSummary(files) {
    var summery = {
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
    var likedByUser = false;
    likes.forEach(function (like, index) {
      if (user.member_id === like.member_id) {
        likedByUser = true;
      }
    });
    return likedByUser;
  }

  function getLoggedUserRatingOnProduct(user, ratings) {
    var userRating = -1;
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

  function calculateProductLaplaceScore(ratings) {
    var laplace_score = 0;
    var upvotes = 0;
    var downvotes = 0;
    ratings.forEach(function (rating, index) {
      if (rating.rating_active === "1") {
        if (rating.user_like === "1") {
          upvotes += 1;
        } else if (rating.user_like === "0") {
          downvotes += 1;
        }
      }
    });
    laplace_score = Math.round((upvotes + 6) / (upvotes + downvotes + 12), 2) * 100;
    return laplace_score;
  }

  return {
    getNumberOfProducts: getNumberOfProducts,
    generatePaginationObject: generatePaginationObject,
    calculateProductRatings: calculateProductRatings,
    getActiveRatingsNumber: getActiveRatingsNumber,
    getFilesSummary: getFilesSummary,
    checkIfLikedByUser: checkIfLikedByUser,
    getLoggedUserRatingOnProduct: getLoggedUserRatingOnProduct,
    calculateProductLaplaceScore: calculateProductLaplaceScore
  };
}();
"use strict";

function _typeof(obj) { if (typeof Symbol === "function" && typeof Symbol.iterator === "symbol") { _typeof = function _typeof(obj) { return typeof obj; }; } else { _typeof = function _typeof(obj) { return obj && typeof Symbol === "function" && obj.constructor === Symbol && obj !== Symbol.prototype ? "symbol" : typeof obj; }; } return _typeof(obj); }

function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }

function _defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } }

function _createClass(Constructor, protoProps, staticProps) { if (protoProps) _defineProperties(Constructor.prototype, protoProps); if (staticProps) _defineProperties(Constructor, staticProps); return Constructor; }

function _possibleConstructorReturn(self, call) { if (call && (_typeof(call) === "object" || typeof call === "function")) { return call; } return _assertThisInitialized(self); }

function _getPrototypeOf(o) { _getPrototypeOf = Object.setPrototypeOf ? Object.getPrototypeOf : function _getPrototypeOf(o) { return o.__proto__ || Object.getPrototypeOf(o); }; return _getPrototypeOf(o); }

function _assertThisInitialized(self) { if (self === void 0) { throw new ReferenceError("this hasn't been initialised - super() hasn't been called"); } return self; }

function _inherits(subClass, superClass) { if (typeof superClass !== "function" && superClass !== null) { throw new TypeError("Super expression must either be null or a function"); } subClass.prototype = Object.create(superClass && superClass.prototype, { constructor: { value: subClass, writable: true, configurable: true } }); if (superClass) _setPrototypeOf(subClass, superClass); }

function _setPrototypeOf(o, p) { _setPrototypeOf = Object.setPrototypeOf || function _setPrototypeOf(o, p) { o.__proto__ = p; return o; }; return _setPrototypeOf(o, p); }

var ProductGroupScrollWrapper =
/*#__PURE__*/
function (_React$Component) {
  _inherits(ProductGroupScrollWrapper, _React$Component);

  function ProductGroupScrollWrapper(props) {
    var _this;

    _classCallCheck(this, ProductGroupScrollWrapper);

    _this = _possibleConstructorReturn(this, _getPrototypeOf(ProductGroupScrollWrapper).call(this, props));
    _this.state = {
      products: [],
      offset: 0
    };
    _this.onProductGroupScroll = _this.onProductGroupScroll.bind(_assertThisInitialized(_this));
    _this.loadMoreProducts = _this.loadMoreProducts.bind(_assertThisInitialized(_this));
    return _this;
  }

  _createClass(ProductGroupScrollWrapper, [{
    key: "componentWillMount",
    value: function componentWillMount() {
      window.addEventListener("scroll", this.onProductGroupScroll);
    }
  }, {
    key: "componentDidMount",
    value: function componentDidMount() {
      this.loadMoreProducts();
    }
  }, {
    key: "onProductGroupScroll",
    value: function onProductGroupScroll() {
      var end = $("footer").offset().top;
      var viewEnd = $(window).scrollTop() + $(window).height();
      var distance = end - viewEnd;

      if (distance < 0 && this.state.loadingMoreProducts !== true) {
        this.setState({
          loadingMoreProducts: true
        }, function () {
          this.loadMoreProducts();
        });
      }
    }
  }, {
    key: "loadMoreProducts",
    value: function loadMoreProducts() {
      var itemsPerScroll = 50;
      var moreProducts = store.getState().products.slice(this.state.offset, this.state.offset + itemsPerScroll);
      var products = this.state.products.concat(moreProducts);
      var offset = this.state.offset + itemsPerScroll;
      this.setState({
        products: products,
        offset: offset,
        loadingMoreProducts: false
      });
    }
  }, {
    key: "render",
    value: function render() {
      var loadingMoreProductsDisplay;

      if (this.state.loadingMoreProducts) {
        loadingMoreProductsDisplay = React.createElement("div", {
          className: "product-group-scroll-loading-container"
        }, React.createElement("div", {
          className: "icon-wrapper"
        }, React.createElement("span", {
          className: "glyphicon glyphicon-refresh spinning"
        })));
      }

      return React.createElement("div", {
        className: "product-group-scroll-wrapper"
      }, React.createElement(ProductGroup, {
        products: this.state.products,
        device: this.props.device
      }), loadingMoreProductsDisplay);
    }
  }]);

  return ProductGroupScrollWrapper;
}(React.Component);

var ProductGroup =
/*#__PURE__*/
function (_React$Component2) {
  _inherits(ProductGroup, _React$Component2);

  function ProductGroup() {
    _classCallCheck(this, ProductGroup);

    return _possibleConstructorReturn(this, _getPrototypeOf(ProductGroup).apply(this, arguments));
  }

  _createClass(ProductGroup, [{
    key: "render",
    value: function render() {
      var products;

      if (this.props.products) {
        var productsArray = this.props.products;

        if (this.props.numRows) {
          var limit = productHelpers.getNumberOfProducts(this.props.device, this.props.numRows);
          productsArray = productsArray.slice(0, limit);
        }

        products = productsArray.map(function (product, index) {
          return React.createElement(ProductGroupItem, {
            key: index,
            product: product
          });
        });
      }

      var sectionHeader;

      if (this.props.title) {
        sectionHeader = React.createElement("div", {
          className: "section-header"
        }, React.createElement("h3", {
          className: "mdl-color-text--primary"
        }, this.props.title), React.createElement("div", {
          className: "actions"
        }, React.createElement("a", {
          href: this.props.link,
          className: "mdl-button mdl-js-button mdl-button--colored mdl-button--raised mdl-js-ripple-effect mdl-color--primary"
        }, "see more")));
      }

      return React.createElement("div", {
        className: "products-showcase"
      }, sectionHeader, React.createElement("div", {
        className: "products-container row"
      }, products));
    }
  }]);

  return ProductGroup;
}(React.Component);

var ProductGroupItem =
/*#__PURE__*/
function (_React$Component3) {
  _inherits(ProductGroupItem, _React$Component3);

  function ProductGroupItem() {
    _classCallCheck(this, ProductGroupItem);

    return _possibleConstructorReturn(this, _getPrototypeOf(ProductGroupItem).apply(this, arguments));
  }

  _createClass(ProductGroupItem, [{
    key: "render",
    value: function render() {
      var imageBaseUrl;

      if (store.getState().env === 'live') {
        imageBaseUrl = 'cn.opendesktop.org';
      } else {
        imageBaseUrl = 'cn.pling.it';
      }

      return React.createElement("div", {
        className: "product square"
      }, React.createElement("div", {
        className: "content"
      }, React.createElement("div", {
        className: "product-wrapper mdl-shadow--2dp"
      }, React.createElement("a", {
        href: "/p/" + this.props.product.project_id
      }, React.createElement("div", {
        className: "product-image-container"
      }, React.createElement("figure", null, React.createElement("img", {
        className: "very-rounded-corners",
        src: 'https://' + imageBaseUrl + '/cache/200x171/img/' + this.props.product.image_small
      }), React.createElement("span", {
        className: "product-info-title"
      }, this.props.product.title))), React.createElement("div", {
        className: "product-info"
      }, React.createElement("span", {
        className: "product-info-description"
      }, this.props.product.description))))));
    }
  }]);

  return ProductGroupItem;
}(React.Component);
"use strict";

var reducer = Redux.combineReducers({
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

function productsReducer() {
  var state = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : {};
  var action = arguments.length > 1 ? arguments[1] : undefined;

  if (action.type === 'SET_PRODUCTS') {
    return action.products;
  } else {
    return state;
  }
}

function productReducer() {
  var state = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : {};
  var action = arguments.length > 1 ? arguments[1] : undefined;

  if (action.type === 'SET_PRODUCT') {
    return action.product;
  } else if (action.type === 'SET_PRODUCT_FILES') {
    var s = Object.assign({}, state, {
      r_files: action.files
    });
    return s;
  } else if (action.type === 'SET_PRODUCT_UPDATES') {
    var _s = Object.assign({}, state, {
      r_updates: action.updates
    });

    return _s;
  } else if (action.type === 'SET_PRODUCT_RATINGS') {
    var _s2 = Object.assign({}, state, {
      r_ratings: action.ratings
    });

    return _s2;
  } else if (action.type === 'SET_PRODUCT_LIKES') {
    var _s3 = Object.assign({}, state, {
      r_likes: action.likes
    });

    return _s3;
  } else if (action.type === 'SET_PRODUCT_PLINGS') {
    var _s4 = Object.assign({}, state, {
      r_plings: action.plings
    });

    return _s4;
  } else if (action.type === 'SET_PRODUCT_USER_RATINGS') {
    var _s5 = Object.assign({}, state, {
      r_userRatings: action.userRatings
    });

    return _s5;
  } else if (action.type === 'SET_PRODUCT_GALLERY') {
    var _s6 = Object.assign({}, state, {
      r_gallery: action.gallery
    });

    return _s6;
  } else if (action.type === 'SET_PRODUCT_COMMENTS') {
    var _s7 = Object.assign({}, state, {
      r_comments: action.comments
    });

    return _s7;
  } else if (action.type === 'SET_PRODUCT_ORIGINS') {
    var _s8 = Object.assign({}, state, {
      r_origins: action.origins
    });

    return _s8;
  } else if (action.type === 'SET_PRODUCT_RELATED') {
    var _s9 = Object.assign({}, state, {
      r_related: action.related
    });

    return _s9;
  } else if (action.type === 'SET_PRODUCT_MORE_PRODUCTS') {
    var _s10 = Object.assign({}, state, {
      r_more_products: action.products
    });

    return _s10;
  } else if (action.type === 'SET_PRODUCT_MORE_PRODUCTS_OTHER_USERS') {
    var _s11 = Object.assign({}, state, {
      r_more_products_other_users: action.products
    });

    return _s11;
  } else if (action.type === 'SET_PRODUCT_TAGS') {
    var _s12 = Object.assign({}, state, {
      r_tags_user: action.userTags,
      r_tags_system: action.systemTags
    });

    return _s12;
  } else {
    return state;
  }
}

function lightboxGalleryReducer() {
  var state = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : {};
  var action = arguments.length > 1 ? arguments[1] : undefined;

  if (action.type === 'SHOW_LIGHTBOX') {
    var s = Object.assign({}, state, {
      show: true,
      currentItem: action.item
    });
    return s;
  } else if (action.type === 'HIDE_LIGHTBOX') {
    var _s13 = Object.assign({}, state, {
      show: false
    });

    return _s13;
  } else {
    return state;
  }
}

function paginationReducer() {
  var state = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : {};
  var action = arguments.length > 1 ? arguments[1] : undefined;

  if (action.type === 'SET_PAGINATION') {
    return action.pagination;
  } else {
    return state;
  }
}

function topProductsReducer() {
  var state = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : {};
  var action = arguments.length > 1 ? arguments[1] : undefined;

  if (action.type === 'SET_TOP_PRODUCTS') {
    return action.products;
  } else {
    return state;
  }
}

function categoriesReducer() {
  var state = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : {};
  var action = arguments.length > 1 ? arguments[1] : undefined;

  if (action.type === 'SET_CATEGORIES') {
    var s = Object.assign({}, state, {
      items: categories
    });
    return s;
  } else if (action.type === 'SET_CURRENT_CAT') {
    var _s14 = Object.assign({}, state, {
      current: action.cat
    });

    return _s14;
  } else if (action.type === 'SET_CURRENT_SUBCAT') {
    var _s15 = Object.assign({}, state, {
      currentSub: action.cat
    });

    return _s15;
  } else if (action.type === 'SET_CURRENT_SECONDSUBCAT') {
    var _s16 = Object.assign({}, state, {
      currentSecondSub: action.cat
    });

    return _s16;
  } else {
    return state;
  }
}

function commentsReducer() {
  var state = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : {};
  var action = arguments.length > 1 ? arguments[1] : undefined;

  if (action.type === 'SET_COMMENTS') {
    return action.comments;
  } else {
    return state;
  }
}

function usersReducer() {
  var state = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : {};
  var action = arguments.length > 1 ? arguments[1] : undefined;

  if (action.type === 'SET_USERS') {
    return action.users;
  } else {
    return state;
  }
}

function userReducer() {
  var state = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : {};
  var action = arguments.length > 1 ? arguments[1] : undefined;

  if (action.type === 'SET_USER') {
    return action.user;
  } else {
    return state;
  }
}

function supportersReducer() {
  var state = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : {};
  var action = arguments.length > 1 ? arguments[1] : undefined;

  if (action.type === 'SET_SUPPORTERS') {
    return action.supporters;
  } else {
    return state;
  }
}

function domainReducer() {
  var state = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : {};
  var action = arguments.length > 1 ? arguments[1] : undefined;

  if (action.type === 'SET_DOMAIN') {
    return action.domain;
  } else {
    return state;
  }
}

function envReducer() {
  var state = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : {};
  var action = arguments.length > 1 ? arguments[1] : undefined;

  if (action.type === 'SET_ENV') {
    return action.env;
  } else {
    return state;
  }
}

function deviceReducer() {
  var state = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : {};
  var action = arguments.length > 1 ? arguments[1] : undefined;

  if (action.type === 'SET_DEVICE') {
    return action.device;
  } else {
    return state;
  }
}

function viewReducer() {
  var state = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : {};
  var action = arguments.length > 1 ? arguments[1] : undefined;

  if (action.type === 'SET_VIEW') {
    return action.view;
  } else {
    return state;
  }
}

function filtersReducer() {
  var state = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : {};
  var action = arguments.length > 1 ? arguments[1] : undefined;

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
"use strict";

function _typeof(obj) { if (typeof Symbol === "function" && typeof Symbol.iterator === "symbol") { _typeof = function _typeof(obj) { return typeof obj; }; } else { _typeof = function _typeof(obj) { return obj && typeof Symbol === "function" && obj.constructor === Symbol && obj !== Symbol.prototype ? "symbol" : typeof obj; }; } return _typeof(obj); }

function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }

function _defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } }

function _createClass(Constructor, protoProps, staticProps) { if (protoProps) _defineProperties(Constructor.prototype, protoProps); if (staticProps) _defineProperties(Constructor, staticProps); return Constructor; }

function _possibleConstructorReturn(self, call) { if (call && (_typeof(call) === "object" || typeof call === "function")) { return call; } return _assertThisInitialized(self); }

function _getPrototypeOf(o) { _getPrototypeOf = Object.setPrototypeOf ? Object.getPrototypeOf : function _getPrototypeOf(o) { return o.__proto__ || Object.getPrototypeOf(o); }; return _getPrototypeOf(o); }

function _assertThisInitialized(self) { if (self === void 0) { throw new ReferenceError("this hasn't been initialised - super() hasn't been called"); } return self; }

function _inherits(subClass, superClass) { if (typeof superClass !== "function" && superClass !== null) { throw new TypeError("Super expression must either be null or a function"); } subClass.prototype = Object.create(superClass && superClass.prototype, { constructor: { value: subClass, writable: true, configurable: true } }); if (superClass) _setPrototypeOf(subClass, superClass); }

function _setPrototypeOf(o, p) { _setPrototypeOf = Object.setPrototypeOf || function _setPrototypeOf(o, p) { o.__proto__ = p; return o; }; return _setPrototypeOf(o, p); }

var ExplorePage =
/*#__PURE__*/
function (_React$Component) {
  _inherits(ExplorePage, _React$Component);

  function ExplorePage(props) {
    var _this;

    _classCallCheck(this, ExplorePage);

    _this = _possibleConstructorReturn(this, _getPrototypeOf(ExplorePage).call(this, props));
    _this.state = {
      device: store.getState().device,
      minHeight: 'auto'
    };
    _this.updateContainerHeight = _this.updateContainerHeight.bind(_assertThisInitialized(_this));
    return _this;
  }

  _createClass(ExplorePage, [{
    key: "componentWillReceiveProps",
    value: function componentWillReceiveProps(nextProps) {
      if (nextProps.device) {
        this.setState({
          device: nextProps.device
        });
      }

      if (nextProps.products) {
        this.setState({
          products: nextProps.products
        });
      }

      if (nextProps.filters) {
        this.setState({
          filters: filters
        });
      }
    }
  }, {
    key: "updateContainerHeight",
    value: function updateContainerHeight(sideBarHeight) {
      this.setState({
        minHeight: sideBarHeight + 100
      });
    }
  }, {
    key: "render",
    value: function render() {
      var titleDisplay;

      if (this.props.categories) {
        var title = "";

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
          titleDisplay = React.createElement("div", {
            className: "explore-page-category-title"
          }, React.createElement("h2", null, title), React.createElement("small", null, store.getState().pagination.totalcount, " results"));
        }
      }

      return React.createElement("div", {
        id: "explore-page"
      }, React.createElement("div", {
        className: "wrapper"
      }, React.createElement("div", {
        className: "main-content-container",
        style: {
          "minHeight": this.state.minHeight
        }
      }, React.createElement("div", {
        className: "left-sidebar-container"
      }, React.createElement(ExploreLeftSideBarWrapper, {
        updateContainerHeight: this.updateContainerHeight
      })), React.createElement("div", {
        className: "main-content"
      }, titleDisplay, React.createElement("div", {
        className: "top-bar"
      }, React.createElement(ExploreTopBarWrapper, null)), React.createElement("div", {
        className: "explore-products-container"
      }, React.createElement(ProductGroupScrollWrapper, {
        device: this.state.device
      }), React.createElement(PaginationWrapper, null)))), React.createElement("div", {
        className: "right-sidebar-container"
      }, React.createElement(ExploreRightSideBarWrapper, null))));
    }
  }]);

  return ExplorePage;
}(React.Component);

var mapStateToExploreProps = function mapStateToExploreProps(state) {
  var device = state.device;
  var products = state.products;
  var categories = state.categories;
  return {
    device: device,
    products: products,
    categories: categories
  };
};

var mapDispatchToExploreProps = function mapDispatchToExploreProps(dispatch) {
  return {
    dispatch: dispatch
  };
};

var ExplorePageWrapper = ReactRedux.connect(mapStateToExploreProps, mapDispatchToExploreProps)(ExplorePage);

var ExploreTopBar =
/*#__PURE__*/
function (_React$Component2) {
  _inherits(ExploreTopBar, _React$Component2);

  function ExploreTopBar(props) {
    var _this2;

    _classCallCheck(this, ExploreTopBar);

    _this2 = _possibleConstructorReturn(this, _getPrototypeOf(ExploreTopBar).call(this, props));
    _this2.state = {};
    return _this2;
  }

  _createClass(ExploreTopBar, [{
    key: "render",
    value: function render() {
      var categories = this.props.categories;
      var currentId;

      if (categories.current) {
        currentId = categories.current.id;
      }

      if (categories.currentSub) {
        currentId = categories.currentSub.id;
      }

      if (categories.currentSecondSub) {
        currentId = categories.currentSecondSub.id;
      }

      var link = appHelpers.generateFilterUrl(window.location, currentId);
      var linkSearch = "";

      if (link.search) {
        linkSearch = link.search;
      }

      return React.createElement("div", {
        className: "explore-top-bar"
      }, React.createElement("a", {
        href: link.base + "latest" + linkSearch,
        className: this.props.filters.order === "latest" ? "item active" : "item"
      }, "Latest"), React.createElement("a", {
        href: link.base + "top" + linkSearch,
        className: this.props.filters.order === "top" ? "item active" : "item"
      }, "Top"));
    }
  }]);

  return ExploreTopBar;
}(React.Component);

var mapStateToExploreTopBarProps = function mapStateToExploreTopBarProps(state) {
  var filters = state.filters;
  var categories = state.categories;
  return {
    filters: filters,
    categories: categories
  };
};

var mapDispatchToExploreTopBarProps = function mapDispatchToExploreTopBarProps(dispatch) {
  return {
    dispatch: dispatch
  };
};

var ExploreTopBarWrapper = ReactRedux.connect(mapStateToExploreTopBarProps, mapDispatchToExploreTopBarProps)(ExploreTopBar);

var ExploreLeftSideBar =
/*#__PURE__*/
function (_React$Component3) {
  _inherits(ExploreLeftSideBar, _React$Component3);

  function ExploreLeftSideBar(props) {
    var _this3;

    _classCallCheck(this, ExploreLeftSideBar);

    _this3 = _possibleConstructorReturn(this, _getPrototypeOf(ExploreLeftSideBar).call(this, props));
    _this3.state = {};
    return _this3;
  }

  _createClass(ExploreLeftSideBar, [{
    key: "componentDidMount",
    value: function componentDidMount() {
      var sideBarHeight = $('#left-sidebar').height();
      this.props.updateContainerHeight(sideBarHeight);
    }
  }, {
    key: "render",
    value: function render() {
      var categoryTree;

      if (this.props.categories) {
        categoryTree = this.props.categories.items.map(function (cat, index) {
          return React.createElement(ExploreSideBarItem, {
            key: index,
            category: cat
          });
        });
      }

      return React.createElement("aside", {
        className: "explore-left-sidebar",
        id: "left-sidebar"
      }, React.createElement("ul", null, React.createElement("li", {
        className: "category-item"
      }, React.createElement("a", {
        className: this.props.categories.current === 0 ? "active" : "",
        href: "/browse/ord/" + filters.order
      }, React.createElement("span", {
        className: "title"
      }, "All"))), categoryTree));
    }
  }]);

  return ExploreLeftSideBar;
}(React.Component);

var mapStateToExploreLeftSideBarProps = function mapStateToExploreLeftSideBarProps(state) {
  var categories = state.categories;
  var filters = state.filters;
  return {
    categories: categories
  };
};

var mapDispatchToExploreLeftSideBarProps = function mapDispatchToExploreLeftSideBarProps(dispatch) {
  return {
    dispatch: dispatch
  };
};

var ExploreLeftSideBarWrapper = ReactRedux.connect(mapStateToExploreLeftSideBarProps, mapDispatchToExploreLeftSideBarProps)(ExploreLeftSideBar);

var ExploreSideBarItem =
/*#__PURE__*/
function (_React$Component4) {
  _inherits(ExploreSideBarItem, _React$Component4);

  function ExploreSideBarItem() {
    _classCallCheck(this, ExploreSideBarItem);

    return _possibleConstructorReturn(this, _getPrototypeOf(ExploreSideBarItem).apply(this, arguments));
  }

  _createClass(ExploreSideBarItem, [{
    key: "render",
    value: function render() {
      var order = store.getState().filters.order;
      var categories = store.getState().categories;
      var currentId, currentSubId, currentSecondSubId;

      if (categories.current) {
        currentId = categories.current.id;
      }

      if (categories.currentSub) {
        currentSubId = categories.currentSub.id;
      }

      if (categories.currentSecondSub) {
        currentSecondSubId = categories.currentSecondSub.id;
      }

      var active;

      if (currentId === this.props.category.id || currentSubId === this.props.category.id || currentSecondSubId === this.props.category.id) {
        active = true;
      }

      var subcatMenu;

      if (this.props.category.has_children === true && active) {
        var cArray = categoryHelpers.convertCatChildrenObjectToArray(this.props.category.children);
        var subcategories = cArray.map(function (cat, index) {
          return React.createElement(ExploreSideBarItem, {
            key: index,
            category: cat
          });
        });
        subcatMenu = React.createElement("ul", null, subcategories);
      }

      return React.createElement("li", {
        className: "category-item"
      }, React.createElement("a", {
        className: active === true ? "active" : "",
        href: "/browse/cat/" + this.props.category.id + "/ord/" + order + window.location.search
      }, React.createElement("span", {
        className: "title"
      }, this.props.category.title), React.createElement("span", {
        className: "product-counter"
      }, this.props.category.product_count)), subcatMenu);
    }
  }]);

  return ExploreSideBarItem;
}(React.Component);

var Pagination =
/*#__PURE__*/
function (_React$Component5) {
  _inherits(Pagination, _React$Component5);

  function Pagination(props) {
    var _this4;

    _classCallCheck(this, Pagination);

    _this4 = _possibleConstructorReturn(this, _getPrototypeOf(Pagination).call(this, props));
    _this4.state = {};
    return _this4;
  }

  _createClass(Pagination, [{
    key: "componentDidMount",
    value: function componentDidMount() {
      var itemsPerPage = 50;
      var numPages = Math.ceil(this.props.pagination.totalcount / itemsPerPage);
      var pagination = productHelpers.generatePaginationObject(numPages, window.location.pathname, this.props.currentCategoy, this.props.filters.order, this.props.pagination.page);
      this.setState({
        pagination: pagination
      }, function () {});
    }
  }, {
    key: "render",
    value: function render() {
      var _this5 = this;

      var paginationDisplay;

      if (this.state.pagination && this.props.pagination.totalcount > 50) {
        var pagination = this.state.pagination.map(function (pi, index) {
          var numberDisplay;

          if (pi.number === 'previous') {
            numberDisplay = React.createElement("span", {
              className: "num-wrap"
            }, React.createElement("i", {
              className: "material-icons"
            }, "arrow_back_ios"), React.createElement("span", null, pi.number));
          } else if (pi.number === 'next') {
            numberDisplay = React.createElement("span", {
              className: "num-wrap"
            }, React.createElement("span", null, pi.number), React.createElement("i", {
              className: "material-icons"
            }, "arrow_forward_ios"));
          } else {
            numberDisplay = pi.number;
          }

          var cssClass;

          if (pi.number === _this5.props.pagination.page) {
            cssClass = "active";
          }

          return React.createElement("li", {
            key: index
          }, React.createElement("a", {
            href: pi.link,
            className: cssClass
          }, numberDisplay));
        });
        paginationDisplay = React.createElement("ul", null, pagination);
      }

      return React.createElement("div", {
        id: "pagination-container"
      }, React.createElement("div", {
        className: "wrapper"
      }, paginationDisplay));
    }
  }]);

  return Pagination;
}(React.Component);

var mapStateToPaginationProps = function mapStateToPaginationProps(state) {
  var pagination = state.pagination;
  var filters = state.filters;
  var currentCategoy = state.categories.current;
  return {
    pagination: pagination,
    filters: filters,
    currentCategoy: currentCategoy
  };
};

var mapDispatchToPaginationProps = function mapDispatchToPaginationProps(dispatch) {
  return {
    dispatch: dispatch
  };
};

var PaginationWrapper = ReactRedux.connect(mapStateToPaginationProps, mapDispatchToPaginationProps)(Pagination);

var ExploreRightSideBar =
/*#__PURE__*/
function (_React$Component6) {
  _inherits(ExploreRightSideBar, _React$Component6);

  function ExploreRightSideBar(props) {
    var _this6;

    _classCallCheck(this, ExploreRightSideBar);

    _this6 = _possibleConstructorReturn(this, _getPrototypeOf(ExploreRightSideBar).call(this, props));
    _this6.state = {};
    return _this6;
  }

  _createClass(ExploreRightSideBar, [{
    key: "render",
    value: function render() {
      return React.createElement("aside", {
        className: "explore-right-sidebar"
      }, React.createElement("div", {
        className: "ers-section"
      }, React.createElement("a", {
        href: "https://www.opendesktop.org/p/1175480/",
        target: "_blank"
      }, React.createElement("img", {
        id: "download-app",
        src: "/images/system/download-app.png"
      }))), React.createElement("div", {
        className: "ers-section"
      }, React.createElement("a", {
        href: "/support",
        id: "become-a-supporter",
        className: "mdl-button mdl-js-button mdl-button--colored mdl-button--raised mdl-js-ripple-effect mdl-color--primary"
      }, "Become a supporter")), React.createElement("div", {
        className: "ers-section"
      }, React.createElement(ExploreSupportersContainerWrapper, null)), React.createElement("div", {
        className: "ers-section"
      }, React.createElement(RssNewsContainer, null)), React.createElement("div", {
        className: "ers-section"
      }, React.createElement(BlogFeedContainer, null)), React.createElement("div", {
        className: "ers-section"
      }, React.createElement(ExploreCommentsContainerWrapper, null)), React.createElement("div", {
        className: "ers-section"
      }, React.createElement(ExploreTopProductsWrapper, null)));
    }
  }]);

  return ExploreRightSideBar;
}(React.Component);

var mapStateToExploreRightSideBarProps = function mapStateToExploreRightSideBarProps(state) {
  var categories = state.categories;
  var filters = state.filters;
  return {
    categories: categories
  };
};

var mapDispatchToExploreRightSideBarProps = function mapDispatchToExploreRightSideBarProps(dispatch) {
  return {
    dispatch: dispatch
  };
};

var ExploreRightSideBarWrapper = ReactRedux.connect(mapStateToExploreRightSideBarProps, mapDispatchToExploreRightSideBarProps)(ExploreRightSideBar);

var ExploreSupportersContainer =
/*#__PURE__*/
function (_React$Component7) {
  _inherits(ExploreSupportersContainer, _React$Component7);

  function ExploreSupportersContainer(props) {
    var _this7;

    _classCallCheck(this, ExploreSupportersContainer);

    _this7 = _possibleConstructorReturn(this, _getPrototypeOf(ExploreSupportersContainer).call(this, props));
    _this7.state = {};
    return _this7;
  }

  _createClass(ExploreSupportersContainer, [{
    key: "render",
    value: function render() {
      var supportersContainer;

      if (this.props.supporters) {
        var cArray = categoryHelpers.convertCatChildrenObjectToArray(this.props.supporters);
        var supporters = cArray.map(function (sp, index) {
          return React.createElement("div", {
            className: "supporter-item",
            key: index
          }, React.createElement("a", {
            href: "/member/" + sp.member_id,
            className: "item"
          }, React.createElement("img", {
            src: sp.profile_image_url
          })));
        });
        supportersContainer = React.createElement("div", {
          className: "supporter-list-wrapper"
        }, supporters);
      }

      return React.createElement("div", {
        id: "supporters-container",
        className: "sidebar-feed-container"
      }, React.createElement("h3", null, this.props.supporters.length, " people support those who create freedom"), supportersContainer);
    }
  }]);

  return ExploreSupportersContainer;
}(React.Component);

var mapStateToExploreSupportersContainerProps = function mapStateToExploreSupportersContainerProps(state) {
  var supporters = state.supporters;
  return {
    supporters: supporters
  };
};

var mapDispatchToExploreSupportersContainerProps = function mapDispatchToExploreSupportersContainerProps(dispatch) {
  return {
    dispatch: dispatch
  };
};

var ExploreSupportersContainerWrapper = ReactRedux.connect(mapStateToExploreSupportersContainerProps, mapDispatchToExploreSupportersContainerProps)(ExploreSupportersContainer);

var RssNewsContainer =
/*#__PURE__*/
function (_React$Component8) {
  _inherits(RssNewsContainer, _React$Component8);

  function RssNewsContainer(props) {
    var _this8;

    _classCallCheck(this, RssNewsContainer);

    _this8 = _possibleConstructorReturn(this, _getPrototypeOf(RssNewsContainer).call(this, props));
    _this8.state = {};
    return _this8;
  }

  _createClass(RssNewsContainer, [{
    key: "componentDidMount",
    value: function componentDidMount() {
      var self = this;
      $.getJSON("https://blog.opendesktop.org/?json=1&callback=?", function (res) {
        self.setState({
          items: res.posts
        });
      });
    }
  }, {
    key: "render",
    value: function render() {
      var feedItemsContainer;

      if (this.state.items) {
        var feedItems = this.state.items.slice(0, 3).map(function (fi, index) {
          return React.createElement("li", {
            key: index
          }, React.createElement("a", {
            className: "title",
            href: fi.url
          }, React.createElement("span", null, fi.title)), React.createElement("span", {
            className: "info-row"
          }, React.createElement("span", {
            className: "date"
          }, appHelpers.getTimeAgo(fi.date)), React.createElement("span", {
            className: "comment-counter"
          }, fi.comment_count, " comments")));
        });
        feedItemsContainer = React.createElement("ul", null, feedItems);
      }

      return React.createElement("div", {
        id: "rss-new-container",
        className: "sidebar-feed-container"
      }, React.createElement("h3", null, "News"), feedItemsContainer);
    }
  }]);

  return RssNewsContainer;
}(React.Component);

var BlogFeedContainer =
/*#__PURE__*/
function (_React$Component9) {
  _inherits(BlogFeedContainer, _React$Component9);

  function BlogFeedContainer(props) {
    var _this9;

    _classCallCheck(this, BlogFeedContainer);

    _this9 = _possibleConstructorReturn(this, _getPrototypeOf(BlogFeedContainer).call(this, props));
    _this9.state = {};
    return _this9;
  }

  _createClass(BlogFeedContainer, [{
    key: "componentDidMount",
    value: function componentDidMount() {
      var self = this;
      $.ajax("https://forum.opendesktop.org/latest.json").then(function (result) {
        var topics = result.topic_list.topics;
        topics.sort(function (a, b) {
          return new Date(b.last_posted_at) - new Date(a.last_posted_at);
        });
        topics = topics.slice(0, 3);
        self.setState({
          items: topics
        });
      });
    }
  }, {
    key: "render",
    value: function render() {
      var feedItemsContainer;

      if (this.state.items) {
        var feedItems = this.state.items.map(function (fi, index) {
          return React.createElement("li", {
            key: index
          }, React.createElement("a", {
            className: "title",
            href: "https://forum.opendesktop.org//t/" + fi.id
          }, React.createElement("span", null, fi.title)), React.createElement("span", {
            className: "info-row"
          }, React.createElement("span", {
            className: "date"
          }, appHelpers.getTimeAgo(fi.created_at)), React.createElement("span", {
            className: "comment-counter"
          }, fi.reply_count, " replies")));
        });
        feedItemsContainer = React.createElement("ul", null, feedItems);
      }

      return React.createElement("div", {
        id: "blog-feed-container",
        className: "sidebar-feed-container"
      }, React.createElement("h3", null, "Forum"), feedItemsContainer);
    }
  }]);

  return BlogFeedContainer;
}(React.Component);

var ExploreCommentsContainer =
/*#__PURE__*/
function (_React$Component10) {
  _inherits(ExploreCommentsContainer, _React$Component10);

  function ExploreCommentsContainer(props) {
    var _this10;

    _classCallCheck(this, ExploreCommentsContainer);

    _this10 = _possibleConstructorReturn(this, _getPrototypeOf(ExploreCommentsContainer).call(this, props));
    _this10.state = {};
    return _this10;
  }

  _createClass(ExploreCommentsContainer, [{
    key: "render",
    value: function render() {
      var commentsContainer;

      if (this.props.comments) {
        var comments = this.props.comments.map(function (cm, index) {
          return React.createElement("li", {
            key: index
          }, React.createElement("div", {
            className: "cm-content"
          }, React.createElement("span", {
            className: "cm-userinfo"
          }, React.createElement("img", {
            src: cm.profile_image_url
          }), React.createElement("span", {
            className: "username"
          }, React.createElement("a", {
            href: "/p/" + cm.comment_target_id
          }, cm.username))), React.createElement("a", {
            className: "title",
            href: "/member/" + cm.member_id
          }, React.createElement("span", null, cm.title)), React.createElement("span", {
            className: "content"
          }, cm.comment_text), React.createElement("span", {
            className: "info-row"
          }, React.createElement("span", {
            className: "date"
          }, appHelpers.getTimeAgo(cm.comment_created_at)))));
        });
        commentsContainer = React.createElement("ul", null, comments);
      }

      return React.createElement("div", {
        id: "blog-feed-container",
        className: "sidebar-feed-container"
      }, React.createElement("h3", null, "Forum"), commentsContainer);
    }
  }]);

  return ExploreCommentsContainer;
}(React.Component);

var mapStateToExploreCommentsContainerProps = function mapStateToExploreCommentsContainerProps(state) {
  var comments = state.comments;
  return {
    comments: comments
  };
};

var mapDispatchToExploreCommentsContainerProps = function mapDispatchToExploreCommentsContainerProps(dispatch) {
  return {
    dispatch: dispatch
  };
};

var ExploreCommentsContainerWrapper = ReactRedux.connect(mapStateToExploreCommentsContainerProps, mapDispatchToExploreCommentsContainerProps)(ExploreCommentsContainer);

var ExploreTopProducts =
/*#__PURE__*/
function (_React$Component11) {
  _inherits(ExploreTopProducts, _React$Component11);

  function ExploreTopProducts(props) {
    var _this11;

    _classCallCheck(this, ExploreTopProducts);

    _this11 = _possibleConstructorReturn(this, _getPrototypeOf(ExploreTopProducts).call(this, props));
    _this11.state = {};
    return _this11;
  }

  _createClass(ExploreTopProducts, [{
    key: "render",
    value: function render() {
      var topProductsContainer;

      if (this.props.topProducts) {
        var imageBaseUrl;

        if (store.getState().env === 'live') {
          imageBaseUrl = 'cn.opendesktop.org';
        } else {
          imageBaseUrl = 'cn.pling.it';
        }

        var topProducts = this.props.topProducts.map(function (tp, index) {
          return React.createElement("li", {
            key: index
          }, React.createElement("img", {
            src: "https://" + imageBaseUrl + "/cache/40x40/img/" + tp.image_small
          }), React.createElement("a", {
            href: "/p/" + tp.project_id
          }, tp.title), React.createElement("span", {
            className: "cat-name"
          }, tp.cat_title));
        });
        topProductsContainer = React.createElement("ol", null, topProducts);
      }

      return React.createElement("div", {
        id: "top-products-container",
        className: "sidebar-feed-container"
      }, React.createElement("h3", null, "3 Months Ranking"), React.createElement("small", null, "(based on downloads)"), topProductsContainer);
    }
  }]);

  return ExploreTopProducts;
}(React.Component);

var mapStateToExploreTopProductsProps = function mapStateToExploreTopProductsProps(state) {
  var topProducts = state.topProducts;
  return {
    topProducts: topProducts
  };
};

var mapDispatchToExploreTopProductsProps = function mapDispatchToExploreTopProductsProps(dispatch) {
  return {
    dispatch: dispatch
  };
};

var ExploreTopProductsWrapper = ReactRedux.connect(mapStateToExploreTopProductsProps, mapDispatchToExploreTopProductsProps)(ExploreTopProducts);
"use strict";

function _typeof(obj) { if (typeof Symbol === "function" && typeof Symbol.iterator === "symbol") { _typeof = function _typeof(obj) { return typeof obj; }; } else { _typeof = function _typeof(obj) { return obj && typeof Symbol === "function" && obj.constructor === Symbol && obj !== Symbol.prototype ? "symbol" : typeof obj; }; } return _typeof(obj); }

function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }

function _defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } }

function _createClass(Constructor, protoProps, staticProps) { if (protoProps) _defineProperties(Constructor.prototype, protoProps); if (staticProps) _defineProperties(Constructor, staticProps); return Constructor; }

function _possibleConstructorReturn(self, call) { if (call && (_typeof(call) === "object" || typeof call === "function")) { return call; } return _assertThisInitialized(self); }

function _assertThisInitialized(self) { if (self === void 0) { throw new ReferenceError("this hasn't been initialised - super() hasn't been called"); } return self; }

function _getPrototypeOf(o) { _getPrototypeOf = Object.setPrototypeOf ? Object.getPrototypeOf : function _getPrototypeOf(o) { return o.__proto__ || Object.getPrototypeOf(o); }; return _getPrototypeOf(o); }

function _inherits(subClass, superClass) { if (typeof superClass !== "function" && superClass !== null) { throw new TypeError("Super expression must either be null or a function"); } subClass.prototype = Object.create(superClass && superClass.prototype, { constructor: { value: subClass, writable: true, configurable: true } }); if (superClass) _setPrototypeOf(subClass, superClass); }

function _setPrototypeOf(o, p) { _setPrototypeOf = Object.setPrototypeOf || function _setPrototypeOf(o, p) { o.__proto__ = p; return o; }; return _setPrototypeOf(o, p); }

var HomePage =
/*#__PURE__*/
function (_React$Component) {
  _inherits(HomePage, _React$Component);

  function HomePage(props) {
    var _this;

    _classCallCheck(this, HomePage);

    _this = _possibleConstructorReturn(this, _getPrototypeOf(HomePage).call(this, props));
    _this.state = {
      device: store.getState().device,
      products: store.getState().products
    };
    return _this;
  }

  _createClass(HomePage, [{
    key: "componentWillReceiveProps",
    value: function componentWillReceiveProps(nextProps) {
      if (nextProps.device) {
        this.setState({
          device: nextProps.device
        });
      }

      if (nextProps.products) {
        this.setState({
          products: nextProps.products
        });
      }
    }
  }, {
    key: "render",
    value: function render() {
      return React.createElement("div", {
        id: "homepage"
      }, React.createElement("div", {
        className: "hp-wrapper"
      }, React.createElement(Introduction, {
        device: this.state.device,
        count: window.totalProjects
      })));
    }
  }]);

  return HomePage;
}(React.Component);

var mapStateToHomePageProps = function mapStateToHomePageProps(state) {
  var device = state.device;
  var products = state.products;
  return {
    device: device,
    products: products
  };
};

var mapDispatchToHomePageProps = function mapDispatchToHomePageProps(dispatch) {
  return {
    dispatch: dispatch
  };
};

var HomePageWrapper = ReactRedux.connect(mapStateToHomePageProps, mapDispatchToHomePageProps)(HomePage);

var Introduction =
/*#__PURE__*/
function (_React$Component2) {
  _inherits(Introduction, _React$Component2);

  function Introduction() {
    _classCallCheck(this, Introduction);

    return _possibleConstructorReturn(this, _getPrototypeOf(Introduction).apply(this, arguments));
  }

  _createClass(Introduction, [{
    key: "render",
    value: function render() {
      var introductionText, siteTitle, buttonsContainer;

      if (window.page === "appimages") {
        siteTitle = "AppImageHub";
        introductionText = React.createElement("p", null, "This catalog has ", this.props.count, " AppImages and counting.", React.createElement("br", null), "AppImages are self-contained apps which can simply be downloaded & run on any Linux distribution. For easy integration, download AppImageLauncher:");
        buttonsContainer = React.createElement("div", {
          className: "actions"
        }, React.createElement("a", {
          href: "/p/1228228",
          className: "mdl-button mdl-js-button mdl-button--raised mdl-js-ripple-effect mdl-button--colored mdl-color--primary"
        }, React.createElement("img", {
          src: "/theme/react/assets/img/icon-download_white.png"
        }), " AppImageLauncher"), React.createElement("a", {
          href: "/browse",
          className: "mdl-button mdl-js-button mdl-button--raised mdl-js-ripple-effect mdl-button--colored mdl-color--primary"
        }, "Browse all apps"), React.createElement("a", {
          href: "https://t.me/appimagehub",
          className: "mdl-button mdl-js-button mdl-button--raised mdl-js-ripple-effect mdl-button--colored mdl-color--primary",
          style: {
            "margin-left": "50px"
          }
        }, "Join our chat #AppImageHub"));
      } else if (window.page === "libreoffice") {
        siteTitle = "LibreOffice";
        introductionText = React.createElement("p", null, "Extensions add new features to your LibreOffice or make the use of already existing ones easier. Currently there are ", this.props.count, " project(s) available.");
        buttonsContainer = React.createElement("div", {
          className: "actions green"
        }, React.createElement("a", {
          href: window.baseUrl + "product/add",
          className: "mdl-button mdl-js-button mdl-button--raised mdl-js-ripple-effect mdl-button--colored mdl-color--primary"
        }, "Add Extension"), React.createElement("a", {
          href: window.baseUrl + "browse/",
          className: "mdl-button mdl-js-button mdl-button--raised mdl-js-ripple-effect mdl-button--colored mdl-color--primary"
        }, "Browse all Extensions"));
      }

      return React.createElement("div", {
        id: "introduction",
        className: "section"
      }, React.createElement("div", {
        className: "container"
      }, React.createElement("article", null, React.createElement("h2", {
        className: "mdl-color-text--primary"
      }, "Welcome to ", siteTitle), introductionText, buttonsContainer)));
    }
  }]);

  return Introduction;
}(React.Component);

var HpIntroSection =
/*#__PURE__*/
function (_React$Component3) {
  _inherits(HpIntroSection, _React$Component3);

  function HpIntroSection(props) {
    var _this2;

    _classCallCheck(this, HpIntroSection);

    _this2 = _possibleConstructorReturn(this, _getPrototypeOf(HpIntroSection).call(this, props));
    _this2.state = {};
    return _this2;
  }

  _createClass(HpIntroSection, [{
    key: "render",
    value: function render() {
      return React.createElement("div", {
        id: "homepage-search-container",
        className: "section intro"
      }, React.createElement("div", {
        className: "container"
      }, React.createElement("article", null, React.createElement("p", null, "Search thousands of snaps used by millions of people across 50 Linux distributions")), React.createElement("div", {
        id: "hp-search-form-container"
      }, React.createElement("select", {
        className: "mdl-selectfield__select"
      }, React.createElement("option", null, "categories")), React.createElement("input", {
        type: "text"
      }), React.createElement("button", null, "search"))));
    }
  }]);

  return HpIntroSection;
}(React.Component);

var mapStateToHpIntroSectionProps = function mapStateToHpIntroSectionProps(state) {
  var categories = state.categories;
  return {
    categories: categories
  };
};

var mapDispatchToHpIntroSectionProps = function mapDispatchToHpIntroSectionProps(dispatch) {
  return {
    dispatch: dispatch
  };
};

var HpIntroSectionWrapper = ReactRedux.connect(mapStateToHpIntroSectionProps, mapDispatchToHpIntroSectionProps)(HpIntroSection);

var ProductCarousel =
/*#__PURE__*/
function (_React$Component4) {
  _inherits(ProductCarousel, _React$Component4);

  function ProductCarousel(props) {
    var _this3;

    _classCallCheck(this, ProductCarousel);

    _this3 = _possibleConstructorReturn(this, _getPrototypeOf(ProductCarousel).call(this, props));
    _this3.state = {
      showRightArrow: true,
      showLeftArrow: false
    };
    _this3.updateDimensions = _this3.updateDimensions.bind(_assertThisInitialized(_this3));
    _this3.animateProductCarousel = _this3.animateProductCarousel.bind(_assertThisInitialized(_this3));
    return _this3;
  }

  _createClass(ProductCarousel, [{
    key: "componentWillMount",
    value: function componentWillMount() {
      window.addEventListener("resize", this.updateDimensions);
    }
  }, {
    key: "componentDidMount",
    value: function componentDidMount() {
      this.updateDimensions();
    }
  }, {
    key: "updateDimensions",
    value: function updateDimensions() {
      var containerWidth = $('#introduction').find('.container').width();
      var sliderWidth = containerWidth * 3;
      var itemWidth = containerWidth / 5;
      this.setState({
        sliderPosition: 0,
        containerWidth: containerWidth,
        sliderWidth: sliderWidth,
        itemWidth: itemWidth
      });
    }
  }, {
    key: "animateProductCarousel",
    value: function animateProductCarousel(dir) {
      var newSliderPosition = this.state.sliderPosition;

      if (dir === 'left') {
        newSliderPosition = this.state.sliderPosition - this.state.containerWidth;
      } else {
        newSliderPosition = this.state.sliderPosition + this.state.containerWidth;
      }

      this.setState({
        sliderPosition: newSliderPosition
      }, function () {
        var showLeftArrow = true,
            showRightArrow = true;
        var endPoint = this.state.sliderWidth - this.state.containerWidth;

        if (this.state.sliderPosition <= 0) {
          showLeftArrow = false;
        }

        if (this.state.sliderPosition >= endPoint) {
          showRightArrow = false;
        }

        this.setState({
          showLeftArrow: showLeftArrow,
          showRightArrow: showRightArrow
        });
      });
    }
  }, {
    key: "render",
    value: function render() {
      var _this4 = this;

      var carouselItemsDisplay;

      if (this.props.products && this.props.products.length > 0) {
        carouselItemsDisplay = this.props.products.map(function (product, index) {
          return React.createElement(ProductCarouselItem, {
            key: index,
            product: product,
            itemWidth: _this4.state.itemWidth
          });
        });
      }

      var rightArrowDisplay, leftArrowDisplay;

      if (this.state.showLeftArrow) {
        leftArrowDisplay = React.createElement("div", {
          className: "product-carousel-left"
        }, React.createElement("a", {
          onClick: function onClick() {
            return _this4.animateProductCarousel('left');
          },
          className: "carousel-arrow arrow-left"
        }, React.createElement("i", {
          className: "material-icons"
        }, "chevron_left")));
      }

      if (this.state.showRightArrow) {
        rightArrowDisplay = React.createElement("div", {
          className: "product-carousel-right"
        }, React.createElement("a", {
          onClick: function onClick() {
            return _this4.animateProductCarousel('right');
          },
          className: "carousel-arrow arrow-right"
        }, React.createElement("i", {
          className: "material-icons"
        }, "chevron_right")));
      }

      return React.createElement("div", {
        className: "product-carousel"
      }, React.createElement("div", {
        className: "product-carousel-header"
      }, React.createElement("h2", null, React.createElement("a", {
        href: this.props.link
      }, this.props.title, React.createElement("i", {
        className: "material-icons"
      }, "chevron_right")))), React.createElement("div", {
        className: "product-carousel-wrapper"
      }, leftArrowDisplay, React.createElement("div", {
        className: "product-carousel-container"
      }, React.createElement("div", {
        className: "product-carousel-slider",
        style: {
          "width": this.state.sliderWidth,
          "left": "-" + this.state.sliderPosition + "px"
        }
      }, carouselItemsDisplay)), rightArrowDisplay));
    }
  }]);

  return ProductCarousel;
}(React.Component);

var ProductCarouselItem =
/*#__PURE__*/
function (_React$Component5) {
  _inherits(ProductCarouselItem, _React$Component5);

  function ProductCarouselItem(props) {
    var _this5;

    _classCallCheck(this, ProductCarouselItem);

    _this5 = _possibleConstructorReturn(this, _getPrototypeOf(ProductCarouselItem).call(this, props));
    _this5.state = {};
    return _this5;
  }

  _createClass(ProductCarouselItem, [{
    key: "render",
    value: function render() {
      var imageBaseUrl;

      if (store.getState().env === 'live') {
        imageBaseUrl = 'cn.opendesktop.org';
      } else {
        imageBaseUrl = 'cn.pling.it';
      }

      return React.createElement("div", {
        className: "product-carousel-item",
        style: {
          "width": this.props.itemWidth
        }
      }, React.createElement("a", {
        href: "/p/" + this.props.product.project_id
      }, React.createElement("figure", null, React.createElement("img", {
        className: "very-rounded-corners",
        src: 'https://' + imageBaseUrl + '/cache/200x171/img/' + this.props.product.image_small
      })), React.createElement("div", {
        className: "product-info"
      }, React.createElement("span", {
        className: "product-info-title"
      }, this.props.product.title), React.createElement("span", {
        className: "product-info-user"
      }, this.props.product.username))));
    }
  }]);

  return ProductCarouselItem;
}(React.Component);
"use strict";

function _defineProperty(obj, key, value) { if (key in obj) { Object.defineProperty(obj, key, { value: value, enumerable: true, configurable: true, writable: true }); } else { obj[key] = value; } return obj; }

function _typeof(obj) { if (typeof Symbol === "function" && typeof Symbol.iterator === "symbol") { _typeof = function _typeof(obj) { return typeof obj; }; } else { _typeof = function _typeof(obj) { return obj && typeof Symbol === "function" && obj.constructor === Symbol && obj !== Symbol.prototype ? "symbol" : typeof obj; }; } return _typeof(obj); }

function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }

function _defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } }

function _createClass(Constructor, protoProps, staticProps) { if (protoProps) _defineProperties(Constructor.prototype, protoProps); if (staticProps) _defineProperties(Constructor, staticProps); return Constructor; }

function _possibleConstructorReturn(self, call) { if (call && (_typeof(call) === "object" || typeof call === "function")) { return call; } return _assertThisInitialized(self); }

function _getPrototypeOf(o) { _getPrototypeOf = Object.setPrototypeOf ? Object.getPrototypeOf : function _getPrototypeOf(o) { return o.__proto__ || Object.getPrototypeOf(o); }; return _getPrototypeOf(o); }

function _assertThisInitialized(self) { if (self === void 0) { throw new ReferenceError("this hasn't been initialised - super() hasn't been called"); } return self; }

function _inherits(subClass, superClass) { if (typeof superClass !== "function" && superClass !== null) { throw new TypeError("Super expression must either be null or a function"); } subClass.prototype = Object.create(superClass && superClass.prototype, { constructor: { value: subClass, writable: true, configurable: true } }); if (superClass) _setPrototypeOf(subClass, superClass); }

function _setPrototypeOf(o, p) { _setPrototypeOf = Object.setPrototypeOf || function _setPrototypeOf(o, p) { o.__proto__ = p; return o; }; return _setPrototypeOf(o, p); }

var ProductView =
/*#__PURE__*/
function (_React$Component) {
  _inherits(ProductView, _React$Component);

  function ProductView(props) {
    var _this;

    _classCallCheck(this, ProductView);

    _this = _possibleConstructorReturn(this, _getPrototypeOf(ProductView).call(this, props));
    _this.state = {
      tab: 'comments',
      showDownloadSection: false
    };
    _this.toggleTab = _this.toggleTab.bind(_assertThisInitialized(_this));
    _this.toggleDownloadSection = _this.toggleDownloadSection.bind(_assertThisInitialized(_this));
    return _this;
  }

  _createClass(ProductView, [{
    key: "componentDidMount",
    value: function componentDidMount() {
      var downloadTableHeight = $('#product-download-section').find('#files-tab').height();
      downloadTableHeight += 80;
      this.setState({
        downloadTableHeight: downloadTableHeight
      });
    }
  }, {
    key: "componentWillReceiveProps",
    value: function componentWillReceiveProps(nextProps) {
      if (nextProps.product !== this.props.product) {
        this.forceUpdate();
      }

      if (nextProps.lightboxGallery !== this.props.lightboxGallery) {
        this.forceUpdate();
      }
    }
  }, {
    key: "toggleTab",
    value: function toggleTab(tab) {
      this.setState({
        tab: tab
      });
    }
  }, {
    key: "toggleDownloadSection",
    value: function toggleDownloadSection() {
      var showDownloadSection = this.state.showDownloadSection === true ? false : true;
      this.setState({
        showDownloadSection: showDownloadSection
      });
    }
  }, {
    key: "render",
    value: function render() {
      var productGalleryDisplay;

      if (this.props.product.r_gallery.length > 0) {
        productGalleryDisplay = React.createElement(ProductViewGallery, {
          product: this.props.product
        });
      }

      var productGalleryLightboxDisplay;

      if (this.props.lightboxGallery.show === true) {
        productGalleryLightboxDisplay = React.createElement(ProductGalleryLightbox, {
          product: this.props.product
        });
      }

      var downloadSectionDisplayHeight;

      if (this.state.showDownloadSection === true) {
        downloadSectionDisplayHeight = this.state.downloadTableHeight;
      }

      return React.createElement("div", {
        id: "product-page"
      }, React.createElement("div", {
        id: "product-download-section",
        style: {
          "height": downloadSectionDisplayHeight
        }
      }, React.createElement(ProductViewFilesTab, {
        product: this.props.product,
        files: this.props.product.r_files
      })), React.createElement(ProductViewHeader, {
        product: this.props.product,
        user: this.props.user,
        onDownloadBtnClick: this.toggleDownloadSection
      }), productGalleryDisplay, React.createElement(ProductDescription, {
        product: this.props.product
      }), React.createElement(ProductNavBar, {
        onTabToggle: this.toggleTab,
        tab: this.state.tab,
        product: this.props.product
      }), React.createElement(ProductViewContent, {
        product: this.props.product,
        user: this.props.user,
        tab: this.state.tab
      }), productGalleryLightboxDisplay);
    }
  }]);

  return ProductView;
}(React.Component);

var mapStateToProductPageProps = function mapStateToProductPageProps(state) {
  var product = state.product;
  var user = state.user;
  var lightboxGallery = state.lightboxGallery;
  return {
    product: product,
    user: user,
    lightboxGallery: lightboxGallery
  };
};

var mapDispatchToProductPageProps = function mapDispatchToProductPageProps(dispatch) {
  return {
    dispatch: dispatch
  };
};

var ProductViewWrapper = ReactRedux.connect(mapStateToProductPageProps, mapDispatchToProductPageProps)(ProductView);

var ProductViewHeader =
/*#__PURE__*/
function (_React$Component2) {
  _inherits(ProductViewHeader, _React$Component2);

  function ProductViewHeader(props) {
    var _this2;

    _classCallCheck(this, ProductViewHeader);

    _this2 = _possibleConstructorReturn(this, _getPrototypeOf(ProductViewHeader).call(this, props));
    _this2.state = {};
    return _this2;
  }

  _createClass(ProductViewHeader, [{
    key: "render",
    value: function render() {
      var imageBaseUrl;

      if (store.getState().env === 'live') {
        imageBaseUrl = 'cn.opendesktop.org';
      } else {
        imageBaseUrl = 'cn.pling.it';
      }

      var productTagsDisplay;

      if (this.props.product.r_tags_user) {
        var tagsArray = this.props.product.r_tags_user.split(',');
        var tags = tagsArray.map(function (tag, index) {
          return React.createElement("span", {
            className: "mdl-chip",
            key: index
          }, React.createElement("span", {
            className: "mdl-chip__text"
          }, React.createElement("span", {
            className: "glyphicon glyphicon-tag"
          }), React.createElement("a", {
            href: "search/projectSearchText/" + tag + "/f/tags"
          }, tag)));
        });
        productTagsDisplay = React.createElement("div", {
          className: "product-tags"
        }, tags);
      }

      return React.createElement("div", {
        className: "wrapper",
        id: "product-view-header"
      }, React.createElement("div", {
        className: "container"
      }, React.createElement("div", {
        className: "section mdl-grid"
      }, React.createElement("div", {
        className: "product-view-header-left"
      }, React.createElement("figure", {
        className: "image-container"
      }, React.createElement("img", {
        src: 'https://' + imageBaseUrl + '/cache/140x140/img/' + this.props.product.image_small
      })), React.createElement("div", {
        className: "product-info"
      }, React.createElement("h1", null, this.props.product.title), React.createElement("div", {
        className: "info-row"
      }, React.createElement("a", {
        className: "user",
        href: "/member/" + this.props.product.member_id
      }, React.createElement("span", {
        className: "avatar"
      }, React.createElement("img", {
        src: this.props.product.profile_image_url
      })), React.createElement("span", {
        className: "username"
      }, this.props.product.username)), React.createElement("a", {
        href: "/browse/cat/" + this.props.product.project_category_id + "/order/latest?new=1"
      }, React.createElement("span", null, this.props.product.cat_title)), productTagsDisplay))), React.createElement("div", {
        className: "product-view-header-right"
      }, React.createElement("div", {
        className: "details-container"
      }, React.createElement("a", {
        onClick: this.props.onDownloadBtnClick,
        href: "#",
        className: "mdl-button mdl-js-button mdl-button--colored mdl-button--raised mdl-js-ripple-effect mdl-color--primary"
      }, "Download"), React.createElement(ProductViewHeaderLikes, {
        product: this.props.product,
        user: this.props.user
      }), React.createElement("div", {
        id: "product-view-header-right-side"
      }, React.createElement(ProductViewHeaderRatings, {
        product: this.props.product,
        user: this.props.user
      })))))));
    }
  }]);

  return ProductViewHeader;
}(React.Component);

var ProductViewHeaderLikes =
/*#__PURE__*/
function (_React$Component3) {
  _inherits(ProductViewHeaderLikes, _React$Component3);

  function ProductViewHeaderLikes(props) {
    var _this3;

    _classCallCheck(this, ProductViewHeaderLikes);

    _this3 = _possibleConstructorReturn(this, _getPrototypeOf(ProductViewHeaderLikes).call(this, props));
    _this3.state = {};
    _this3.onUserLike = _this3.onUserLike.bind(_assertThisInitialized(_this3));
    return _this3;
  }

  _createClass(ProductViewHeaderLikes, [{
    key: "componentDidMount",
    value: function componentDidMount() {
      var user = store.getState().user;
      var likedByUser = productHelpers.checkIfLikedByUser(user, this.props.product.r_likes);
      this.setState({
        likesTotal: this.props.product.r_likes.length,
        likedByUser: likedByUser
      });
    }
  }, {
    key: "onUserLike",
    value: function onUserLike() {
      if (this.props.user.username) {
        var url = "/p/" + this.props.product.project_id + "/followproject/";
        var self = this;
        $.ajax({
          url: url,
          cache: false
        }).done(function (response) {
          // error
          if (response.status === "error") {
            self.setState({
              msg: response.msg
            });
          } else {
            // delete
            if (response.action === "delete") {
              var likesTotal = self.state.likesTotal - 1;
              self.setState({
                likesTotal: likesTotal,
                likedByUser: false
              });
            } // insert
            else {
                var _likesTotal = self.state.likesTotal + 1;

                self.setState({
                  likesTotal: _likesTotal,
                  likedByUser: true
                });
              }
          }
        });
      } else {
        this.setState({
          msg: 'please login to like'
        });
      }
    }
  }, {
    key: "render",
    value: function render() {
      var cssContainerClass, cssHeartClass;

      if (this.state.likedByUser === true) {
        cssContainerClass = "liked-by-user";
        cssHeartClass = "plingheart fa heartproject fa-heart";
      } else {
        cssHeartClass = "plingheart fa fa-heart-o heartgrey";
      }

      return React.createElement("div", {
        className: cssContainerClass,
        id: "likes-container"
      }, React.createElement("div", {
        className: "likes"
      }, React.createElement("i", {
        className: cssHeartClass
      }), React.createElement("span", {
        onClick: this.onUserLike
      }, this.state.likesTotal)), React.createElement("div", {
        className: "likes-label-container"
      }, this.state.msg));
    }
  }]);

  return ProductViewHeaderLikes;
}(React.Component);

var ProductViewHeaderRatings =
/*#__PURE__*/
function (_React$Component4) {
  _inherits(ProductViewHeaderRatings, _React$Component4);

  function ProductViewHeaderRatings(props) {
    var _this4;

    _classCallCheck(this, ProductViewHeaderRatings);

    _this4 = _possibleConstructorReturn(this, _getPrototypeOf(ProductViewHeaderRatings).call(this, props));
    _this4.state = {
      userIsOwner: '',
      action: '',
      laplace_score: _this4.props.product.laplace_score
    };
    _this4.onRatingFormResponse = _this4.onRatingFormResponse.bind(_assertThisInitialized(_this4));
    return _this4;
  }

  _createClass(ProductViewHeaderRatings, [{
    key: "componentDidMount",
    value: function componentDidMount() {
      var userIsOwner = false;

      if (this.props.user && this.props.user.member_id === this.props.product.member_id) {
        userIsOwner = true;
      }

      var userRating = -1;

      if (userIsOwner === false) {
        userRating = productHelpers.getLoggedUserRatingOnProduct(this.props.user, this.props.product.r_ratings);
      }

      this.setState({
        userIsOwner: userIsOwner,
        userRating: userRating
      });
    }
  }, {
    key: "onRatingBtnClick",
    value: function onRatingBtnClick(action) {
      this.setState({
        showModal: false
      }, function () {
        this.setState({
          action: action,
          showModal: true
        }, function () {
          $('#ratings-form-modal').modal('show');
        });
      });
    }
  }, {
    key: "onRatingFormResponse",
    value: function onRatingFormResponse(modalResponse, val) {
      var self = this;
      this.setState({
        errorMsg: ''
      }, function () {
        jQuery.ajax({
          data: {},
          url: '/p/' + this.props.product.project_id + '/loadratings/',
          method: 'get',
          error: function error(jqXHR, textStatus, errorThrown) {
            self.setState({
              errorMsg: textStatus + " " + errorThrown
            });
            $('#ratings-form-modal').modal('hide');
          },
          success: function success(response) {
            // const laplace_score = productHelpers.calculateProductLaplaceScore(response);
            store.dispatch(setProductRatings(response));
            if (modalResponse.status !== "ok") self.setState({
              errorMsg: modalResponse.status + " - " + modalResponse.message
            });
            self.setState({
              laplace_score: modalResponse.laplace_score
            }, function () {});
            $('#ratings-form-modal').modal('hide');
          }
        });
      });
    }
  }, {
    key: "render",
    value: function render() {
      var _this5 = this;

      var ratingsFormModalDisplay;

      if (this.state.showModal === true) {
        if (this.props.user.username) {
          ratingsFormModalDisplay = React.createElement(RatingsFormModal, {
            user: this.props.user,
            userIsOwner: this.state.userIsOwner,
            userRating: this.state.userRating,
            action: this.state.action,
            product: this.props.product,
            onRatingFormResponse: this.onRatingFormResponse
          });
        } else {
          ratingsFormModalDisplay = React.createElement("div", {
            className: "modal please-login",
            id: "ratings-form-modal",
            tabIndex: "-1",
            role: "dialog"
          }, React.createElement("div", {
            className: "modal-dialog",
            role: "document"
          }, React.createElement("div", {
            className: "modal-content"
          }, React.createElement("div", {
            className: "modal-header"
          }, React.createElement("h4", {
            className: "modal-title"
          }, "Please Login"), React.createElement("button", {
            type: "button",
            id: "review-modal-close",
            className: "close",
            "data-dismiss": "modal",
            "aria-label": "Close"
          }, React.createElement("span", {
            "aria-hidden": "true"
          }, "\xD7"))), React.createElement("div", {
            className: "modal-body"
          }, React.createElement("a", {
            href: "/login/"
          }, "Login")))));
        }
      }

      return React.createElement("div", {
        className: "ratings-bar-container"
      }, React.createElement("div", {
        className: "ratings-bar-left",
        onClick: function onClick() {
          return _this5.onRatingBtnClick('minus');
        }
      }, React.createElement("i", {
        className: "material-icons"
      }, "remove")), React.createElement("div", {
        className: "ratings-bar-holder"
      }, React.createElement("div", {
        className: "green ratings-bar",
        style: {
          "width": this.state.laplace_score + "%"
        }
      }), React.createElement("div", {
        className: "ratings-bar-empty",
        style: {
          "width": 100 - this.state.laplace_score + "%"
        }
      })), React.createElement("div", {
        className: "ratings-bar-right",
        onClick: function onClick() {
          return _this5.onRatingBtnClick('plus');
        }
      }, React.createElement("i", {
        className: "material-icons"
      }, "add")), ratingsFormModalDisplay, React.createElement("p", {
        className: "ratings-bar-error-msg-container"
      }, this.state.errorMsg));
    }
  }]);

  return ProductViewHeaderRatings;
}(React.Component);

var RatingsFormModal =
/*#__PURE__*/
function (_React$Component5) {
  _inherits(RatingsFormModal, _React$Component5);

  function RatingsFormModal(props) {
    var _this6;

    _classCallCheck(this, RatingsFormModal);

    _this6 = _possibleConstructorReturn(this, _getPrototypeOf(RatingsFormModal).call(this, props));
    _this6.state = {
      action: _this6.props.action
    };
    _this6.submitRatingForm = _this6.submitRatingForm.bind(_assertThisInitialized(_this6));
    _this6.onTextAreaInputChange = _this6.onTextAreaInputChange.bind(_assertThisInitialized(_this6));
    return _this6;
  }

  _createClass(RatingsFormModal, [{
    key: "componentDidMount",
    value: function componentDidMount() {
      var actionIcon;

      if (this.props.action === 'plus') {
        actionIcon = '+';
      } else if (this.props.action === 'minus') {
        actionIcon = '-';
      }

      this.setState({
        action: this.props.action,
        actionIcon: actionIcon,
        text: actionIcon
      }, function () {
        this.forceUpdate();
      });
    }
  }, {
    key: "onTextAreaInputChange",
    value: function onTextAreaInputChange(e) {
      this.setState({
        text: e.target.value
      });
    }
  }, {
    key: "submitRatingForm",
    value: function submitRatingForm() {
      this.setState({
        loading: true
      }, function () {
        var self = this;
        var v;

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
          error: function error() {
            var msg = "Service is temporarily unavailable. Our engineers are working quickly to resolve this issue. <br/>Find out why you may have encountered this error.";
            self.setState({
              msg: msg
            });
          },
          success: function success(response) {
            self.props.onRatingFormResponse(response, v);
          }
        });
      });
    }
  }, {
    key: "render",
    value: function render() {
      var textAreaDisplay, modalBtnDisplay;

      if (!this.props.user) {
        textAreaDisplay = React.createElement("p", null, "Please login to comment");
        modalBtnDisplay = React.createElement("button", {
          type: "button",
          className: "btn btn-secondary",
          "data-dismiss": "modal"
        }, "Close");
      } else {
        if (this.props.userIsOwner) {
          textAreaDisplay = React.createElement("p", null, "Project owner not allowed");
          modalBtnDisplay = React.createElement("button", {
            type: "button",
            className: "btn btn-secondary",
            "data-dismiss": "modal"
          }, "Close");
        } else if (this.state.text) {
          textAreaDisplay = React.createElement("textarea", {
            onChange: this.onTextAreaInputChange,
            defaultValue: this.state.text,
            className: "form-control"
          });

          if (this.state.loading !== true) {
            if (this.state.msg) {
              modalBtnDisplay = React.createElement("p", null, this.state.msg);
            } else {
              modalBtnDisplay = React.createElement("button", {
                onClick: this.submitRatingForm,
                type: "button",
                className: "btn btn-primary"
              }, "Rate Now");
            }
          } else {
            modalBtnDisplay = React.createElement("span", {
              className: "glyphicon glyphicon-refresh spinning"
            });
          }
        }
      }

      return React.createElement("div", {
        className: "modal",
        id: "ratings-form-modal",
        tabIndex: "-1",
        role: "dialog"
      }, React.createElement("div", {
        className: "modal-dialog",
        role: "document"
      }, React.createElement("div", {
        className: "modal-content"
      }, React.createElement("div", {
        className: "modal-header"
      }, React.createElement("div", {
        className: this.props.action + " action-icon-container"
      }, this.state.actionIcon), React.createElement("h5", {
        className: "modal-title"
      }, "Add Comment (min. 1 char):"), React.createElement("button", {
        type: "button",
        id: "review-modal-close",
        className: "close",
        "data-dismiss": "modal",
        "aria-label": "Close"
      }, React.createElement("span", {
        "aria-hidden": "true"
      }, "\xD7"))), React.createElement("div", {
        className: "modal-body"
      }, textAreaDisplay), React.createElement("div", {
        className: "modal-footer"
      }, modalBtnDisplay))));
    }
  }]);

  return RatingsFormModal;
}(React.Component);

var ProductViewGallery =
/*#__PURE__*/
function (_React$Component6) {
  _inherits(ProductViewGallery, _React$Component6);

  function ProductViewGallery(props) {
    var _this7;

    _classCallCheck(this, ProductViewGallery);

    _this7 = _possibleConstructorReturn(this, _getPrototypeOf(ProductViewGallery).call(this, props));
    _this7.state = {
      loading: true,
      currentItem: 1,
      galleryWrapperMarginLeft: 0
    };
    _this7.updateDimensions = _this7.updateDimensions.bind(_assertThisInitialized(_this7));
    _this7.onLeftArrowClick = _this7.onLeftArrowClick.bind(_assertThisInitialized(_this7));
    _this7.onRightArrowClick = _this7.onRightArrowClick.bind(_assertThisInitialized(_this7));
    _this7.animateGallerySlider = _this7.animateGallerySlider.bind(_assertThisInitialized(_this7));
    return _this7;
  }

  _createClass(ProductViewGallery, [{
    key: "componentDidMount",
    value: function componentDidMount() {
      window.addEventListener("resize", this.updateDimensions);
      this.updateDimensions();
    }
  }, {
    key: "componentWillUnmount",
    value: function componentWillUnmount() {
      window.removeEventListener("resize", this.updateDimensions);
    }
  }, {
    key: "updateDimensions",
    value: function updateDimensions() {
      var productGallery = document.getElementById('product-gallery');
      var itemsWidth = 300;
      var itemsTotal = this.props.product.r_gallery.length + 1;
      this.setState({
        itemsWidth: itemsWidth,
        itemsTotal: itemsTotal,
        loading: false
      });
    }
  }, {
    key: "onLeftArrowClick",
    value: function onLeftArrowClick() {
      var nextItem;

      if (this.state.currentItem <= 1) {
        nextItem = this.state.itemsTotal;
      } else {
        nextItem = this.state.currentItem - 1;
      }

      var marginLeft = this.state.itemsWidth * (nextItem - 1);
      this.animateGallerySlider(nextItem, marginLeft);
    }
  }, {
    key: "onRightArrowClick",
    value: function onRightArrowClick() {
      var nextItem;

      if (this.state.currentItem === this.state.itemsTotal) {
        nextItem = 1;
      } else {
        nextItem = this.state.currentItem + 1;
      }

      var marginLeft = this.state.itemsWidth * (nextItem - 1);
      this.animateGallerySlider(nextItem, marginLeft);
    }
  }, {
    key: "animateGallerySlider",
    value: function animateGallerySlider(nextItem, marginLeft) {
      this.setState({
        currentItem: nextItem,
        galleryWrapperMarginLeft: "-" + marginLeft + "px"
      });
    }
  }, {
    key: "onGalleryItemClick",
    value: function onGalleryItemClick(num) {
      store.dispatch(showLightboxGallery(num));
    }
  }, {
    key: "render",
    value: function render() {
      var _this8 = this;

      var galleryDisplay;

      if (this.props.product.embed_code && this.props.product.embed_code.length > 0) {
        var imageBaseUrl;

        if (store.getState().env === 'live') {
          imageBaseUrl = 'http://cn.opendesktop.org';
        } else {
          imageBaseUrl = 'http://cn.pling.it';
        }

        if (this.props.product.r_gallery.length > 0) {
          var itemsWidth = this.state.itemsWidth;
          var currentItem = this.state.currentItem;
          var self = this;
          var moreItems = this.props.product.r_gallery.map(function (gi, index) {
            return React.createElement("div", {
              key: index,
              onClick: function onClick() {
                return _this8.onGalleryItemClick(index + 2);
              },
              className: currentItem === index + 2 ? "active-gallery-item gallery-item" : "gallery-item"
            }, React.createElement("img", {
              className: "media-item",
              src: imageBaseUrl + "/img/" + gi
            }));
          });
          galleryDisplay = React.createElement("div", {
            id: "product-gallery"
          }, React.createElement("a", {
            className: "gallery-arrow arrow-left",
            onClick: this.onLeftArrowClick
          }, React.createElement("i", {
            className: "material-icons"
          }, "chevron_left")), React.createElement("div", {
            className: "section"
          }, React.createElement("div", {
            style: {
              "width": this.state.itemsWidth * this.state.itemsTotal + "px",
              "marginLeft": this.state.galleryWrapperMarginLeft
            },
            className: "gallery-items-wrapper"
          }, React.createElement("div", {
            onClick: function onClick() {
              return _this8.onGalleryItemClick(1);
            },
            dangerouslySetInnerHTML: {
              __html: this.props.product.embed_code
            },
            className: this.state.currentItem === 1 ? "active-gallery-item gallery-item" : "gallery-item"
          }), moreItems)), React.createElement("a", {
            className: "gallery-arrow arrow-right",
            onClick: this.onRightArrowClick
          }, React.createElement("i", {
            className: "material-icons"
          }, "chevron_right")));
        }
      }

      return React.createElement("div", {
        className: "section",
        id: "product-view-gallery-container"
      }, React.createElement("div", {
        className: "container"
      }, React.createElement("div", {
        className: "section"
      }, galleryDisplay)));
    }
  }]);

  return ProductViewGallery;
}(React.Component);

var ProductGalleryLightbox =
/*#__PURE__*/
function (_React$Component7) {
  _inherits(ProductGalleryLightbox, _React$Component7);

  function ProductGalleryLightbox(props) {
    var _this9;

    _classCallCheck(this, ProductGalleryLightbox);

    _this9 = _possibleConstructorReturn(this, _getPrototypeOf(ProductGalleryLightbox).call(this, props));
    var currentItem;

    if (store.getState().lightboxGallery) {
      currentItem = store.getState().lightboxGallery.currentItem;
    } else {
      currentItem = 1;
    }

    _this9.state = {
      currentItem: currentItem,
      loading: true
    };
    _this9.updateDimensions = _this9.updateDimensions.bind(_assertThisInitialized(_this9));
    _this9.toggleNextGalleryItem = _this9.toggleNextGalleryItem.bind(_assertThisInitialized(_this9));
    _this9.togglePrevGalleryItem = _this9.togglePrevGalleryItem.bind(_assertThisInitialized(_this9));
    _this9.animateGallerySlider = _this9.animateGallerySlider.bind(_assertThisInitialized(_this9));
    _this9.onThumbnailClick = _this9.onThumbnailClick.bind(_assertThisInitialized(_this9));
    return _this9;
  }

  _createClass(ProductGalleryLightbox, [{
    key: "componentDidMount",
    value: function componentDidMount() {
      window.addEventListener("resize", this.updateDimensions);
      this.updateDimensions();
    }
  }, {
    key: "componentWillUnmount",
    value: function componentWillUnmount() {
      window.removeEventListener("resize", this.updateDimensions);
    }
  }, {
    key: "updateDimensions",
    value: function updateDimensions() {
      var thumbnailsSectionWidth = document.getElementById('thumbnails-section').offsetWidth;
      var itemsWidth = 300;
      var itemsTotal = this.props.product.r_gallery.length + 1;
      var thumbnailsMarginLeft = 0;

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
  }, {
    key: "togglePrevGalleryItem",
    value: function togglePrevGalleryItem() {
      var nextItem;

      if (this.state.currentItem <= 1) {
        nextItem = this.state.itemsTotal;
      } else {
        nextItem = this.state.currentItem - 1;
      }

      this.animateGallerySlider(nextItem);
    }
  }, {
    key: "toggleNextGalleryItem",
    value: function toggleNextGalleryItem() {
      var nextItem;

      if (this.state.currentItem === this.state.itemsTotal) {
        nextItem = 1;
      } else {
        nextItem = this.state.currentItem + 1;
      }

      this.animateGallerySlider(nextItem);
    }
  }, {
    key: "animateGallerySlider",
    value: function animateGallerySlider(currentItem) {
      this.setState({
        currentItem: currentItem
      }, function () {
        this.updateDimensions();
      });
    }
  }, {
    key: "onThumbnailClick",
    value: function onThumbnailClick(num) {
      this.animateGallerySlider(num);
    }
  }, {
    key: "hideLightbox",
    value: function hideLightbox() {
      store.dispatch(hideLightboxGallery());
    }
  }, {
    key: "render",
    value: function render() {
      var _this10 = this;

      var imageBaseUrl;

      if (store.getState().env === 'live') {
        imageBaseUrl = 'http://cn.opendesktop.org';
      } else {
        imageBaseUrl = 'http://cn.pling.it';
      }

      var currentItem = this.state.currentItem;
      var self = this;
      var thumbnails = this.props.product.r_gallery.map(function (gi, index) {
        return React.createElement("div", {
          key: index,
          onClick: function onClick() {
            return self.onThumbnailClick(index + 2);
          },
          className: self.state.currentItem === index + 2 ? "active thumbnail-item" : "thumbnail-item"
        }, React.createElement("img", {
          className: "media-item",
          src: imageBaseUrl + "/img/" + gi
        }));
      });
      var mainItemDisplay;

      if (currentItem === 1) {
        mainItemDisplay = React.createElement("div", {
          dangerouslySetInnerHTML: {
            __html: this.props.product.embed_code
          }
        });
      } else {
        var mainItem = this.props.product.r_gallery[currentItem - 2];
        mainItemDisplay = React.createElement("img", {
          className: "media-item",
          src: imageBaseUrl + "/img/" + mainItem
        });
      }

      return React.createElement("div", {
        id: "product-gallery-lightbox"
      }, React.createElement("a", {
        id: "close-lightbox",
        onClick: this.hideLightbox
      }, React.createElement("i", {
        className: "material-icons"
      }, "cancel")), React.createElement("div", {
        id: "lightbox-gallery-main-view"
      }, React.createElement("a", {
        className: "gallery-arrow",
        onClick: this.togglePrevGalleryItem,
        id: "arrow-left"
      }, React.createElement("i", {
        className: "material-icons"
      }, "chevron_left")), React.createElement("div", {
        className: "current-gallery-item"
      }, mainItemDisplay), React.createElement("a", {
        className: "gallery-arrow",
        onClick: this.toggleNextGalleryItem,
        id: "arrow-right"
      }, React.createElement("i", {
        className: "material-icons"
      }, "chevron_right"))), React.createElement("div", {
        id: "lightbox-gallery-thumbnails"
      }, React.createElement("div", {
        className: "section",
        id: "thumbnails-section"
      }, React.createElement("div", {
        id: "gallery-items-wrapper",
        style: {
          "width": this.state.itemsTotal * this.state.itemsWidth + "px",
          "marginLeft": this.state.thumbnailsMarginLeft + "px"
        }
      }, React.createElement("div", {
        onClick: function onClick() {
          return _this10.onThumbnailClick(1);
        },
        dangerouslySetInnerHTML: {
          __html: this.props.product.embed_code
        },
        className: this.state.currentItem === 1 ? "active thumbnail-item" : "thumbnail-item"
      }), thumbnails))));
    }
  }]);

  return ProductGalleryLightbox;
}(React.Component);

var ProductDescription =
/*#__PURE__*/
function (_React$Component8) {
  _inherits(ProductDescription, _React$Component8);

  function ProductDescription(props) {
    var _this11;

    _classCallCheck(this, ProductDescription);

    _this11 = _possibleConstructorReturn(this, _getPrototypeOf(ProductDescription).call(this, props));
    _this11.state = {};
    return _this11;
  }

  _createClass(ProductDescription, [{
    key: "render",
    value: function render() {
      return React.createElement("div", {
        id: "product-description",
        className: "section"
      }, React.createElement("div", {
        className: "container"
      }, React.createElement("div", {
        className: "main-content"
      }, React.createElement("article", null, React.createElement("p", {
        dangerouslySetInnerHTML: {
          __html: this.props.product.description
        }
      })), React.createElement("aside", null, React.createElement("ul", null, React.createElement("li", null, React.createElement("span", {
        className: "key"
      }, "License"), React.createElement("span", {
        className: "val"
      }, this.props.product.project_license_title)), React.createElement("li", null, React.createElement("span", {
        className: "key"
      }, "Last Update"), React.createElement("span", {
        className: "val"
      }, this.props.product.changed_at.split(' ')[0])))))));
    }
  }]);

  return ProductDescription;
}(React.Component);

var ProductNavBar =
/*#__PURE__*/
function (_React$Component9) {
  _inherits(ProductNavBar, _React$Component9);

  function ProductNavBar() {
    _classCallCheck(this, ProductNavBar);

    return _possibleConstructorReturn(this, _getPrototypeOf(ProductNavBar).apply(this, arguments));
  }

  _createClass(ProductNavBar, [{
    key: "render",
    value: function render() {
      var _this12 = this;

      var productNavBarDisplay;
      var filesMenuItem, ratingsMenuItem, favsMenuItem, plingsMenuItem;

      if (this.props.product.r_files.length > 0) {
        filesMenuItem = React.createElement("a", {
          className: this.props.tab === "files" ? "item active" : "item",
          onClick: function onClick() {
            return _this12.props.onTabToggle('files');
          }
        }, "Files (", this.props.product.r_files.length, ")");
      }

      if (this.props.product.r_ratings.length > 0) {
        var activeRatingsNumber = productHelpers.getActiveRatingsNumber(this.props.product.r_ratings);
        ratingsMenuItem = React.createElement("a", {
          className: this.props.tab === "ratings" ? "item active" : "item",
          onClick: function onClick() {
            return _this12.props.onTabToggle('ratings');
          }
        }, "Ratings & Reviews (", activeRatingsNumber, ")");
      }

      if (this.props.product.r_likes.length > 0) {
        favsMenuItem = React.createElement("a", {
          className: this.props.tab === "favs" ? "item active" : "item",
          onClick: function onClick() {
            return _this12.props.onTabToggle('favs');
          }
        }, "Favs (", this.props.product.r_likes.length, ")");
      }

      if (this.props.product.r_plings.length > 0) {
        plingsMenuItem = React.createElement("a", {
          className: this.props.tab === "plings" ? "item active" : "item",
          onClick: function onClick() {
            return _this12.props.onTabToggle('plings');
          }
        }, "Plings (", this.props.product.r_plings.length, ")");
      }

      return React.createElement("div", {
        className: "wrapper"
      }, React.createElement("div", {
        className: "container"
      }, React.createElement("div", {
        className: "explore-top-bar"
      }, React.createElement("a", {
        className: this.props.tab === "comments" ? "item active" : "item",
        onClick: function onClick() {
          return _this12.props.onTabToggle('comments');
        }
      }, "Comments (", this.props.product.r_comments.length, ")"), filesMenuItem, ratingsMenuItem, favsMenuItem, plingsMenuItem)));
    }
  }]);

  return ProductNavBar;
}(React.Component);

var ProductViewContent =
/*#__PURE__*/
function (_React$Component10) {
  _inherits(ProductViewContent, _React$Component10);

  function ProductViewContent() {
    _classCallCheck(this, ProductViewContent);

    return _possibleConstructorReturn(this, _getPrototypeOf(ProductViewContent).apply(this, arguments));
  }

  _createClass(ProductViewContent, [{
    key: "render",
    value: function render() {
      var currentTabDisplay;

      if (this.props.tab === 'comments') {
        currentTabDisplay = React.createElement("div", {
          className: "product-tab",
          id: "comments-tab"
        }, React.createElement(ProductCommentsContainer, {
          product: this.props.product,
          user: this.props.user
        }));
      } else if (this.props.tab === 'files') {
        currentTabDisplay = React.createElement(ProductViewFilesTab, {
          product: this.props.product,
          files: this.props.product.r_files
        });
      } else if (this.props.tab === 'ratings') {
        currentTabDisplay = React.createElement(ProductViewRatingsTabWrapper, {
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

      return React.createElement("div", {
        className: "wrapper"
      }, React.createElement("div", {
        className: "container"
      }, React.createElement("div", {
        className: "section",
        id: "product-view-content-container"
      }, currentTabDisplay)));
    }
  }]);

  return ProductViewContent;
}(React.Component);

var ProductCommentsContainer =
/*#__PURE__*/
function (_React$Component11) {
  _inherits(ProductCommentsContainer, _React$Component11);

  function ProductCommentsContainer(props) {
    var _this13;

    _classCallCheck(this, ProductCommentsContainer);

    _this13 = _possibleConstructorReturn(this, _getPrototypeOf(ProductCommentsContainer).call(this, props));
    _this13.state = {};
    return _this13;
  }

  _createClass(ProductCommentsContainer, [{
    key: "render",
    value: function render() {
      var _this14 = this;

      var commentsDisplay;
      var cArray = categoryHelpers.convertCatChildrenObjectToArray(this.props.product.r_comments);

      if (cArray.length > 0) {
        var product = this.props.product;
        var comments = cArray.map(function (c, index) {
          if (c.level === 1) {
            return React.createElement(CommentItem, {
              user: _this14.props.user,
              product: product,
              comment: c.comment,
              key: index,
              level: 1
            });
          }
        });
        commentsDisplay = React.createElement("div", {
          className: "comment-list"
        }, comments);
      }

      return React.createElement("div", {
        className: "product-view-section",
        id: "product-comments-container"
      }, React.createElement(CommentForm, {
        user: this.props.user,
        product: this.props.product
      }), commentsDisplay);
    }
  }]);

  return ProductCommentsContainer;
}(React.Component);

var CommentForm =
/*#__PURE__*/
function (_React$Component12) {
  _inherits(CommentForm, _React$Component12);

  function CommentForm(props) {
    var _this15;

    _classCallCheck(this, CommentForm);

    _this15 = _possibleConstructorReturn(this, _getPrototypeOf(CommentForm).call(this, props));
    _this15.state = {
      text: '',
      errorMsg: '',
      errorTitle: '',
      loading: false
    };
    _this15.updateCommentText = _this15.updateCommentText.bind(_assertThisInitialized(_this15));
    _this15.submitComment = _this15.submitComment.bind(_assertThisInitialized(_this15));
    _this15.updateComments = _this15.updateComments.bind(_assertThisInitialized(_this15));
    return _this15;
  }

  _createClass(CommentForm, [{
    key: "updateCommentText",
    value: function updateCommentText(e) {
      this.setState({
        text: e.target.value
      });
    }
  }, {
    key: "submitComment",
    value: function submitComment() {
      this.setState({
        loading: true
      }, function () {
        var msg = this.state.text;
        var self = this;
        var data = {
          p: this.props.product.project_id,
          m: this.props.user.member_id,
          msg: this.state.text
        };

        if (this.props.comment) {
          data.i = this.props.comment.comment_id;
        }

        jQuery.ajax({
          data: data,
          url: '/productcomment/addreply/',
          type: 'post',
          dataType: 'json',
          error: function error(jqXHR, textStatus, errorThrown) {
            var results = JSON && JSON.parse(jqXHR.responseText) || $.parseJSON(jqXHR.responseText);
            self.setState({
              errorMsg: results.message,
              errorTitle: results.title,
              login_url: results.login_url,
              status: 'error'
            });
          },
          success: function success(results) {
            var baseUrl;

            if (store.getState().env === 'live') {
              baseUrl = 'cn.opendesktop.org';
            } else {
              baseUrl = 'cn.pling.it';
            }

            $.ajax({
              url: '/productcomment?p=' + self.props.product.project_id,
              cache: false
            }).done(function (response) {
              self.updateComments(response);
            });
          }
        });
      });
    }
  }, {
    key: "updateComments",
    value: function updateComments(response) {
      store.dispatch(setProductComments(response));
      this.setState({
        text: '',
        loading: false
      }, function () {
        if (this.props.hideReplyForm) {
          this.props.hideReplyForm();
        }
      });
    }
  }, {
    key: "render",
    value: function render() {
      var commentFormDisplay;

      if (this.props.user.username) {
        if (this.state.loading) {
          commentFormDisplay = React.createElement("div", {
            className: "comment-form-container"
          }, React.createElement("p", null, React.createElement("span", {
            className: "glyphicon glyphicon-refresh spinning"
          }), " posting comment"));
        } else {
          var submitBtnDisplay;

          if (this.state.text.length === 0) {
            submitBtnDisplay = React.createElement("button", {
              disabled: "disabled",
              type: "button",
              className: "mdl-button mdl-js-button mdl-button--raised mdl-js-ripple-effect mdl-button--colored mdl-color--primary"
            }, "send");
          } else {
            submitBtnDisplay = React.createElement("button", {
              onClick: this.submitComment,
              type: "button",
              className: "mdl-button mdl-js-button mdl-button--raised mdl-js-ripple-effect mdl-button--colored mdl-color--primary"
            }, React.createElement("span", {
              className: "glyphicon glyphicon-send"
            }), "send");
          }

          var errorDisplay;

          if (this.state.status === 'error') {
            errorDisplay = React.createElement("div", {
              className: "comment-form-error-display-container"
            }, React.createElement("div", {
              dangerouslySetInnerHTML: {
                __html: this.state.errorTitle
              }
            }), React.createElement("div", {
              dangerouslySetInnerHTML: {
                __html: this.state.errorMsg
              }
            }));
          }

          commentFormDisplay = React.createElement("div", {
            className: "comment-form-container"
          }, React.createElement("span", null, "Add Comment"), React.createElement("textarea", {
            className: "form-control",
            onChange: this.updateCommentText,
            value: this.state.text
          }), errorDisplay, submitBtnDisplay);
        }
      } else {
        commentFormDisplay = React.createElement("p", null, "Please ", React.createElement("a", {
          href: "/login?redirect=ohWn43n4SbmJZWlKUZNl2i1_s5gggiCE"
        }, "login"), " or ", React.createElement("a", {
          href: "/register"
        }, "register"), " to add a comment");
      }

      return React.createElement("div", {
        id: "product-page-comment-form-container"
      }, commentFormDisplay);
    }
  }]);

  return CommentForm;
}(React.Component);

var CommentItem =
/*#__PURE__*/
function (_React$Component13) {
  _inherits(CommentItem, _React$Component13);

  function CommentItem(props) {
    var _this16;

    _classCallCheck(this, CommentItem);

    _this16 = _possibleConstructorReturn(this, _getPrototypeOf(CommentItem).call(this, props));
    _this16.state = {
      showCommentReplyForm: false
    };
    _this16.filterByCommentLevel = _this16.filterByCommentLevel.bind(_assertThisInitialized(_this16));
    _this16.onToggleReplyForm = _this16.onToggleReplyForm.bind(_assertThisInitialized(_this16));
    _this16.onReportComment = _this16.onReportComment.bind(_assertThisInitialized(_this16));
    _this16.onConfirmReportClick = _this16.onConfirmReportClick.bind(_assertThisInitialized(_this16));
    return _this16;
  }

  _createClass(CommentItem, [{
    key: "filterByCommentLevel",
    value: function filterByCommentLevel(val) {
      if (val.level > this.props.level && this.props.comment.comment_id === val.comment.comment_parent_id) {
        return val;
      }
    }
  }, {
    key: "onToggleReplyForm",
    value: function onToggleReplyForm() {
      var showCommentReplyForm = this.state.showCommentReplyForm === true ? false : true;
      this.setState({
        showCommentReplyForm: showCommentReplyForm
      });
    }
  }, {
    key: "onReportComment",
    value: function onReportComment() {
      $('#report-' + this.props.comment.comment_id).modal('show');
    }
  }, {
    key: "onConfirmReportClick",
    value: function onConfirmReportClick(commentId, productId) {
      jQuery.ajax({
        data: {
          i: commentId,
          p: productId
        },
        url: "/report/comment/",
        type: "POST",
        dataType: "json",
        error: function error(jqXHR, textStatus, errorThrown) {
          var results = JSON && JSON.parse(jqXHR.responseText) || $.parseJSON(jqXHR.responseText);
          $("#report-" + commentId).find('.modal-header-text').empty().append(results.title);
          $("#report-" + commentId).find('.modal-body').empty().append(results.message);
          setTimeout(function () {
            $("#report-" + commentId).modal('hide');
          }, 2000);
        },
        success: function success(results) {
          if (results.status == 'ok') {
            $("#report-" + commentId).find(".comment-report-p").empty().html(results.message.split('</p>')[0].split('<p>')[1]);
          }

          if (results.status == 'error') {
            if (results.message != '') {
              $("#report-" + commentId).find(".comment-report-p").empty().html(results.message);
            } else {
              $("#report-" + commentId).find(".comment-report-p").empty().html('Service is temporarily unavailable.');
            }
          }

          setTimeout(function () {
            $("#report-" + commentId).modal('hide');
          }, 2000);
        }
      });
    }
  }, {
    key: "render",
    value: function render() {
      var commentRepliesContainer;
      var filteredComments = categoryHelpers.convertCatChildrenObjectToArray(this.props.product.r_comments).filter(this.filterByCommentLevel);

      if (filteredComments.length > 0) {
        var product = this.props.product;
        var user = this.props.user;
        var comments = filteredComments.map(function (c, index) {
          return React.createElement(CommentItem, {
            user: user,
            product: product,
            comment: c.comment,
            key: index,
            level: c.level
          });
        });
        commentRepliesContainer = React.createElement("div", {
          className: "comment-item-replies-container"
        }, comments);
      }

      var displayIsSupporter;

      if (this.props.comment.issupporter === "1") {
        displayIsSupporter = React.createElement("li", null, React.createElement("span", {
          className: "is-supporter-display uc-icon"
        }, "S"));
      }

      var displayIsCreater;

      if (this.props.comment.member_id === this.props.product.member_id) {
        displayIsCreater = React.createElement("li", null, React.createElement("span", {
          className: "is-creater-display uc-icon"
        }, "C"));
      }

      var commentReplyFormDisplay;

      if (this.state.showCommentReplyForm) {
        commentReplyFormDisplay = React.createElement(CommentForm, {
          comment: this.props.comment,
          user: this.props.user,
          product: this.props.product,
          hideReplyForm: this.onToggleReplyForm
        });
      }

      return React.createElement("div", {
        className: "comment-item"
      }, React.createElement("div", {
        className: "comment-user-avatar"
      }, React.createElement("img", {
        src: this.props.comment.profile_image_url
      })), React.createElement("div", {
        className: "comment-item-content"
      }, React.createElement("div", {
        className: "comment-item-header"
      }, React.createElement("ul", null, React.createElement("li", null, React.createElement("a", {
        className: "comment-username",
        href: "/member/" + this.props.comment.member_id
      }, this.props.comment.username)), displayIsSupporter, displayIsCreater, React.createElement("li", null, React.createElement("span", {
        className: "comment-created-at"
      }, appHelpers.getTimeAgo(this.props.comment.comment_created_at))))), React.createElement("div", {
        className: "comment-item-text"
      }, this.props.comment.comment_text), React.createElement("div", {
        className: "comment-item-actions"
      }, React.createElement("a", {
        onClick: this.onToggleReplyForm
      }, React.createElement("i", {
        className: "material-icons reverse"
      }, "reply"), React.createElement("span", null, "Reply")), React.createElement("a", {
        onClick: this.onReportComment
      }, React.createElement("i", {
        className: "material-icons"
      }, "warning"), React.createElement("span", null, "Report")), React.createElement(ReportCommentModal, {
        comment: this.props.comment,
        product: this.props.product,
        user: this.props.user,
        onConfirmReportClick: this.onConfirmReportClick
      }))), commentReplyFormDisplay, commentRepliesContainer);
    }
  }]);

  return CommentItem;
}(React.Component);

var ReportCommentModal =
/*#__PURE__*/
function (_React$Component14) {
  _inherits(ReportCommentModal, _React$Component14);

  function ReportCommentModal(props) {
    var _this17;

    _classCallCheck(this, ReportCommentModal);

    _this17 = _possibleConstructorReturn(this, _getPrototypeOf(ReportCommentModal).call(this, props));
    _this17.state = {
      status: "ready"
    };
    return _this17;
  }

  _createClass(ReportCommentModal, [{
    key: "onConfirmReportClick",
    value: function onConfirmReportClick(commmentId, productId) {
      this.setState({
        status: "loading"
      }, function () {
        this.props.onConfirmReportClick(commmentId, productId);
      });
    }
  }, {
    key: "render",
    value: function render() {
      var _this18 = this;

      var confirmActionButtonIconDisplay;

      if (this.state.status === "ready") {
        confirmActionButtonIconDisplay = React.createElement("i", {
          className: "material-icons reverse"
        }, "reply");
      } else if (this.state.status === "loading") {
        confirmActionButtonIconDisplay = React.createElement("span", {
          className: "glyphicon glyphicon-refresh spinning"
        });
      }

      return React.createElement("div", {
        className: "modal report-comment-modal",
        id: "report-" + this.props.comment.comment_id,
        tabIndex: "-1",
        role: "dialog"
      }, React.createElement("div", {
        className: "modal-dialog",
        role: "document"
      }, React.createElement("div", {
        className: "modal-content"
      }, React.createElement("div", {
        className: "modal-header"
      }, React.createElement("h4", {
        className: "modal-title"
      }, "Report Comment"), React.createElement("button", {
        type: "button",
        id: "review-modal-close",
        className: "close",
        "data-dismiss": "modal",
        "aria-label": "Close"
      }, React.createElement("span", {
        "aria-hidden": "true"
      }, "\xD7"))), React.createElement("div", {
        className: "modal-body"
      }, React.createElement("p", {
        className: "comment-report-p"
      }, "Do you really want to report this comment?")), React.createElement("div", {
        className: "modal-footer"
      }, React.createElement("a", {
        onClick: function onClick() {
          return _this18.onConfirmReportClick(_this18.props.comment.comment_id, _this18.props.product.project_id);
        }
      }, confirmActionButtonIconDisplay, " yes")))));
    }
  }]);

  return ReportCommentModal;
}(React.Component);

var ProductViewFilesTab =
/*#__PURE__*/
function (_React$Component15) {
  _inherits(ProductViewFilesTab, _React$Component15);

  function ProductViewFilesTab() {
    _classCallCheck(this, ProductViewFilesTab);

    return _possibleConstructorReturn(this, _getPrototypeOf(ProductViewFilesTab).apply(this, arguments));
  }

  _createClass(ProductViewFilesTab, [{
    key: "render",
    value: function render() {
      var _this19 = this;

      var filesDisplay;
      var files = this.props.files.map(function (f, index) {
        return React.createElement(ProductViewFilesTabItem, {
          product: _this19.props.product,
          key: index,
          file: f
        });
      });
      var summeryRow = productHelpers.getFilesSummary(this.props.files);
      filesDisplay = React.createElement("tbody", null, files, React.createElement("tr", null, React.createElement("td", null, summeryRow.total, " files (0 archived)"), React.createElement("td", null), React.createElement("td", null), React.createElement("td", null), React.createElement("td", null), React.createElement("td", null, summeryRow.downloads), React.createElement("td", null), React.createElement("td", null, appHelpers.getFileSize(summeryRow.fileSize)), React.createElement("td", null), React.createElement("td", null)));
      return React.createElement("div", {
        id: "files-tab",
        className: "product-tab"
      }, React.createElement("table", {
        className: "mdl-data-table mdl-js-data-table mdl-shadow--2dp"
      }, React.createElement("thead", null, React.createElement("tr", null, React.createElement("th", {
        className: "mdl-data-table__cell--non-numericm"
      }, "File"), React.createElement("th", {
        className: "mdl-data-table__cell--non-numericm"
      }, "Version"), React.createElement("th", {
        className: "mdl-data-table__cell--non-numericm"
      }, "Description"), React.createElement("th", {
        className: "mdl-data-table__cell--non-numericm"
      }, "Packagetype"), React.createElement("th", {
        className: "mdl-data-table__cell--non-numericm"
      }, "Architecture"), React.createElement("th", {
        className: "mdl-data-table__cell--non-numericm"
      }, "Downloads"), React.createElement("th", {
        className: "mdl-data-table__cell--non-numericm"
      }, "Date"), React.createElement("th", {
        className: "mdl-data-table__cell--non-numericm"
      }, "Filesize"), React.createElement("th", {
        className: "mdl-data-table__cell--non-numericm"
      }, "DL"), React.createElement("th", {
        className: "mdl-data-table__cell--non-numericm"
      }, "OCS-Install"))), filesDisplay));
    }
  }]);

  return ProductViewFilesTab;
}(React.Component);

var ProductViewFilesTabItem =
/*#__PURE__*/
function (_React$Component16) {
  _inherits(ProductViewFilesTabItem, _React$Component16);

  function ProductViewFilesTabItem(props) {
    var _this20;

    _classCallCheck(this, ProductViewFilesTabItem);

    _this20 = _possibleConstructorReturn(this, _getPrototypeOf(ProductViewFilesTabItem).call(this, props));
    _this20.state = {
      downloadLink: ""
    };
    return _this20;
  }

  _createClass(ProductViewFilesTabItem, [{
    key: "componentDidMount",
    value: function componentDidMount() {
      var baseUrl, downloadLinkUrlAttr;

      if (store.getState().env === 'live') {
        baseUrl = 'opendesktop.org';
        downloadLinkUrlAttr = "https%3A%2F%dl.opendesktop.org%2Fapi%2F";
      } else {
        baseUrl = 'pling.cc';
        downloadLinkUrlAttr = "https%3A%2F%2Fcc.ppload.com%2Fapi%2F";
      }

      var f = this.props.file;
      var timestamp = Math.floor(new Date().getTime() / 1000 + 3600);
      var fileDownloadHash = appHelpers.generateFileDownloadHash(f, store.getState().env);
      var downloadLink = "https://" + baseUrl + "/p/" + this.props.product.project_id + "/startdownload?file_id=" + f.id + "&file_name=" + f.title + "&file_type=" + f.type + "&file_size=" + f.size + "&url=" + downloadLinkUrlAttr + "files%2Fdownload%2Fid%2F" + f.id + "%2Fs%2F" + fileDownloadHash + "%2Ft%2F" + timestamp + "%2Fu%2F" + this.props.product.member_id + "%2F" + f.title;
      this.setState({
        downloadLink: downloadLink
      });
    }
  }, {
    key: "render",
    value: function render() {
      var f = this.props.file;
      return React.createElement("tr", null, React.createElement("td", {
        className: "mdl-data-table__cell--non-numericm"
      }, React.createElement("a", {
        href: this.state.downloadLink
      }, f.title)), React.createElement("td", null, f.version), React.createElement("td", {
        className: "mdl-data-table__cell--non-numericm"
      }, f.description), React.createElement("td", {
        className: "mdl-data-table__cell--non-numericm"
      }, f.packagename), React.createElement("td", {
        className: "mdl-data-table__cell--non-numericm"
      }, f.archname), React.createElement("td", null, f.downloaded_count), React.createElement("td", {
        className: "mdl-data-table__cell--non-numericm"
      }, appHelpers.getTimeAgo(f.created_timestamp)), React.createElement("td", {
        className: "mdl-data-table__cell--non-numericm"
      }, appHelpers.getFileSize(f.size)), React.createElement("td", null, React.createElement("a", {
        href: this.state.downloadLink
      }, React.createElement("i", {
        className: "material-icons"
      }, "cloud_download"))), React.createElement("td", null, f.ocs_compatible));
    }
  }]);

  return ProductViewFilesTabItem;
}(React.Component);

var ProductViewRatingsTab =
/*#__PURE__*/
function (_React$Component17) {
  _inherits(ProductViewRatingsTab, _React$Component17);

  function ProductViewRatingsTab(props) {
    var _this21;

    _classCallCheck(this, ProductViewRatingsTab);

    _this21 = _possibleConstructorReturn(this, _getPrototypeOf(ProductViewRatingsTab).call(this, props));
    _this21.state = {
      filter: 'active'
    };
    _this21.filterLikes = _this21.filterLikes.bind(_assertThisInitialized(_this21));
    _this21.filterDislikes = _this21.filterDislikes.bind(_assertThisInitialized(_this21));
    _this21.filterActive = _this21.filterActive.bind(_assertThisInitialized(_this21));
    _this21.setFilter = _this21.setFilter.bind(_assertThisInitialized(_this21));
    return _this21;
  }

  _createClass(ProductViewRatingsTab, [{
    key: "filterLikes",
    value: function filterLikes(rating) {
      if (rating.user_like === "1") {
        return rating;
      }
    }
  }, {
    key: "filterDislikes",
    value: function filterDislikes(rating) {
      if (rating.user_dislike === "1") {
        return rating;
      }
    }
  }, {
    key: "filterActive",
    value: function filterActive(rating) {
      if (rating.rating_active === "1") {
        return rating;
      }
    }
  }, {
    key: "setFilter",
    value: function setFilter(filter) {
      this.setState({
        filter: filter
      });
    }
  }, {
    key: "render",
    value: function render() {
      var _this22 = this;

      var ratingsLikes = this.props.ratings.filter(this.filterLikes);
      var ratingsDislikes = this.props.ratings.filter(this.filterDislikes);
      var ratingsActive = this.props.ratings.filter(this.filterActive);
      var ratingsDisplay;

      if (this.props.ratings.length > 0) {
        var ratings;

        if (this.state.filter === "all") {
          ratings = this.props.ratings;
        } else if (this.state.filter === "active") {
          ratings = ratingsActive;
        } else if (this.state.filter === "dislikes") {
          ratings = ratingsDislikes;
        } else if (this.state.filter === "likes") {
          ratings = ratingsLikes;
        }

        var ratingsItems = ratings.map(function (r, index) {
          return React.createElement(RatingItem, {
            key: index,
            rating: r
          });
        });
        ratingsDisplay = React.createElement("div", {
          className: "product-ratings-list comment-list"
        }, ratingsItems);
      }

      var subMenuItemClassName = " mdl-button mdl-js-button mdl-button--raised mdl-js-ripple-effect";
      var subMenuActiveItemClassName = "active mdl-button--colored mdl-color--primary item";
      return React.createElement("div", {
        id: "ratings-tab",
        className: "product-tab"
      }, React.createElement("div", {
        className: "ratings-filters-menu"
      }, React.createElement("span", {
        className: "btn-container",
        onClick: function onClick() {
          return _this22.setFilter("dislikes");
        }
      }, React.createElement("a", {
        className: this.state.filter === "dislikes" ? subMenuActiveItemClassName + subMenuItemClassName : subMenuItemClassName,
        onClick: this.showDislikes
      }, "show dislikes (", ratingsDislikes.length, ")")), React.createElement("span", {
        className: "btn-container",
        onClick: function onClick() {
          return _this22.setFilter("likes");
        }
      }, React.createElement("a", _defineProperty({
        onClick: this.setDislikesFilter,
        className: this.state.filter === "likes" ? subMenuActiveItemClassName + subMenuItemClassName : subMenuItemClassName
      }, "onClick", this.showLikes), "show likes (", ratingsLikes.length, ")")), React.createElement("span", {
        className: "btn-container",
        onClick: function onClick() {
          return _this22.setFilter("active");
        }
      }, React.createElement("a", _defineProperty({
        onClick: this.setDislikesFilter,
        className: this.state.filter === "active" ? subMenuActiveItemClassName + subMenuItemClassName : subMenuItemClassName
      }, "onClick", this.showActive), "show active reviews (", ratingsActive.length, ")")), React.createElement("span", {
        className: "btn-container",
        onClick: function onClick() {
          return _this22.setFilter("all");
        }
      }, React.createElement("a", _defineProperty({
        onClick: this.setDislikesFilter,
        className: this.state.filter === "all" ? subMenuActiveItemClassName + subMenuItemClassName : subMenuItemClassName
      }, "onClick", this.showAll), "show all (", this.props.ratings.length, ")"))), ratingsDisplay);
    }
  }]);

  return ProductViewRatingsTab;
}(React.Component);

var mapStateToProductViewRatingsTabProps = function mapStateToProductViewRatingsTabProps(state) {
  var ratings = state.product.r_ratings;
  return {
    ratings: ratings
  };
};

var mapDispatchToProductViewRatingsTabProps = function mapDispatchToProductViewRatingsTabProps(dispatch) {
  return {
    dispatch: dispatch
  };
};

var ProductViewRatingsTabWrapper = ReactRedux.connect(mapStateToProductViewRatingsTabProps, mapDispatchToProductViewRatingsTabProps)(ProductViewRatingsTab);

var RatingItem =
/*#__PURE__*/
function (_React$Component18) {
  _inherits(RatingItem, _React$Component18);

  function RatingItem(props) {
    var _this23;

    _classCallCheck(this, RatingItem);

    _this23 = _possibleConstructorReturn(this, _getPrototypeOf(RatingItem).call(this, props));
    _this23.state = {};
    return _this23;
  }

  _createClass(RatingItem, [{
    key: "render",
    value: function render() {
      return React.createElement("div", {
        className: "product-rating-item comment-item"
      }, React.createElement("div", {
        className: "rating-user-avatar comment-user-avatar"
      }, React.createElement("img", {
        src: this.props.rating.profile_image_url
      })), React.createElement("div", {
        className: "rating-item-content comment-item-content"
      }, React.createElement("div", {
        className: "rating-item-header comment-item-header"
      }, React.createElement("a", {
        href: "/member/" + this.props.rating.member_id
      }, this.props.rating.username), React.createElement("span", {
        className: "comment-created-at"
      }, appHelpers.getTimeAgo(this.props.rating.created_at))), React.createElement("div", {
        className: "rating-item-text comment-item-text"
      }, this.props.rating.comment_text)));
    }
  }]);

  return RatingItem;
}(React.Component);

var ProductViewFavTab =
/*#__PURE__*/
function (_React$Component19) {
  _inherits(ProductViewFavTab, _React$Component19);

  function ProductViewFavTab(props) {
    var _this24;

    _classCallCheck(this, ProductViewFavTab);

    _this24 = _possibleConstructorReturn(this, _getPrototypeOf(ProductViewFavTab).call(this, props));
    _this24.state = {};
    return _this24;
  }

  _createClass(ProductViewFavTab, [{
    key: "render",
    value: function render() {
      var favsDisplay;

      if (this.props.likes) {
        var favs = this.props.likes.map(function (like, index) {
          return React.createElement(UserCardItem, {
            key: index,
            like: like
          });
        });
        favsDisplay = React.createElement("div", {
          className: "favs-list supporter-list"
        }, favs);
      }

      return React.createElement("div", {
        className: "product-tab",
        id: "fav-tab"
      }, favsDisplay);
    }
  }]);

  return ProductViewFavTab;
}(React.Component);

var ProductViewPlingsTab =
/*#__PURE__*/
function (_React$Component20) {
  _inherits(ProductViewPlingsTab, _React$Component20);

  function ProductViewPlingsTab(props) {
    var _this25;

    _classCallCheck(this, ProductViewPlingsTab);

    _this25 = _possibleConstructorReturn(this, _getPrototypeOf(ProductViewPlingsTab).call(this, props));
    _this25.state = {};
    return _this25;
  }

  _createClass(ProductViewPlingsTab, [{
    key: "render",
    value: function render() {
      var plingsDisplay;

      if (this.props.plings) {
        var plings = this.props.plings.map(function (pling, index) {
          return React.createElement(UserCardItem, {
            key: index,
            pling: pling
          });
        });
        plingsDisplay = React.createElement("div", {
          className: "plings-list supporter-list"
        }, plings);
      }

      return React.createElement("div", {
        className: "product-tab",
        id: "plings-tab"
      }, plingsDisplay);
    }
  }]);

  return ProductViewPlingsTab;
}(React.Component);

var UserCardItem =
/*#__PURE__*/
function (_React$Component21) {
  _inherits(UserCardItem, _React$Component21);

  function UserCardItem(props) {
    var _this26;

    _classCallCheck(this, UserCardItem);

    _this26 = _possibleConstructorReturn(this, _getPrototypeOf(UserCardItem).call(this, props));
    _this26.state = {};
    return _this26;
  }

  _createClass(UserCardItem, [{
    key: "render",
    value: function render() {
      var item;

      if (this.props.like) {
        item = this.props.like;
      } else if (this.props.pling) {
        item = this.props.pling;
      }

      var cardTypeDisplay;

      if (this.props.like) {
        cardTypeDisplay = React.createElement("i", {
          className: "fa fa-heart myfav",
          "aria-hidden": "true"
        });
      } else if (this.props.pling) {
        cardTypeDisplay = React.createElement("img", {
          src: "/images/system/pling-btn-active.png"
        });
      }

      return React.createElement("div", {
        className: "supporter-list-item"
      }, React.createElement("div", {
        className: "item-content"
      }, React.createElement("div", {
        className: "user-avatar"
      }, React.createElement("img", {
        src: item.profile_image_url
      })), React.createElement("span", {
        className: "username"
      }, React.createElement("a", {
        href: "/member/" + item.member_id
      }, item.username)), React.createElement("span", {
        className: "card-type-holder"
      }, cardTypeDisplay), React.createElement("span", {
        className: "created-at"
      }, appHelpers.getTimeAgo(item.created_at))));
    }
  }]);

  return UserCardItem;
}(React.Component);
"use strict";

function _typeof(obj) { if (typeof Symbol === "function" && typeof Symbol.iterator === "symbol") { _typeof = function _typeof(obj) { return typeof obj; }; } else { _typeof = function _typeof(obj) { return obj && typeof Symbol === "function" && obj.constructor === Symbol && obj !== Symbol.prototype ? "symbol" : typeof obj; }; } return _typeof(obj); }

function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }

function _defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } }

function _createClass(Constructor, protoProps, staticProps) { if (protoProps) _defineProperties(Constructor.prototype, protoProps); if (staticProps) _defineProperties(Constructor, staticProps); return Constructor; }

function _possibleConstructorReturn(self, call) { if (call && (_typeof(call) === "object" || typeof call === "function")) { return call; } return _assertThisInitialized(self); }

function _getPrototypeOf(o) { _getPrototypeOf = Object.setPrototypeOf ? Object.getPrototypeOf : function _getPrototypeOf(o) { return o.__proto__ || Object.getPrototypeOf(o); }; return _getPrototypeOf(o); }

function _assertThisInitialized(self) { if (self === void 0) { throw new ReferenceError("this hasn't been initialised - super() hasn't been called"); } return self; }

function _inherits(subClass, superClass) { if (typeof superClass !== "function" && superClass !== null) { throw new TypeError("Super expression must either be null or a function"); } subClass.prototype = Object.create(superClass && superClass.prototype, { constructor: { value: subClass, writable: true, configurable: true } }); if (superClass) _setPrototypeOf(subClass, superClass); }

function _setPrototypeOf(o, p) { _setPrototypeOf = Object.setPrototypeOf || function _setPrototypeOf(o, p) { o.__proto__ = p; return o; }; return _setPrototypeOf(o, p); }

var _ReactRedux = ReactRedux,
    Provider = _ReactRedux.Provider,
    connect = _ReactRedux.connect;
var store = Redux.createStore(reducer);

var App =
/*#__PURE__*/
function (_React$Component) {
  _inherits(App, _React$Component);

  function App(props) {
    var _this;

    _classCallCheck(this, App);

    _this = _possibleConstructorReturn(this, _getPrototypeOf(App).call(this, props));
    _this.state = {
      loading: true,
      version: 1
    };
    _this.updateDimensions = _this.updateDimensions.bind(_assertThisInitialized(_this));
    return _this;
  }

  _createClass(App, [{
    key: "componentWillMount",
    value: function componentWillMount() {
      // device
      this.updateDimensions();
    }
  }, {
    key: "componentDidMount",
    value: function componentDidMount() {
      // domain
      store.dispatch(setDomain(window.location.hostname)); // env

      var env = appHelpers.getEnv(window.location.hostname);
      store.dispatch(setEnv(env)); // device

      window.addEventListener("resize", this.updateDimensions); // view

      if (window.view) store.dispatch(setView(view)); // products

      if (window.products) {
        store.dispatch(setProducts(products));
      } // product (single)


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
      } // pagination


      if (window.pagination) {
        store.dispatch(setPagination(pagination));
      } // filters


      if (window.filters) {
        store.dispatch(setFilters(filters));
      } // top products


      if (window.topProducts) {
        store.dispatch(setTopProducts(topProducts));
      } // categories


      if (window.categories) {
        // set categories
        store.dispatch(setCategories(categories));

        if (window.catId) {
          // current categories
          var currentCategories = categoryHelpers.findCurrentCategories(categories, catId);
          store.dispatch(setCurrentCategory(currentCategories.category));
          store.dispatch(setCurrentSubCategory(currentCategories.subcategory));
          store.dispatch(setCurrentSecondSubCategory(currentCategories.secondSubCategory));
        }
      } // supporters


      if (window.supporters) {
        store.dispatch(setSupporters(supporters));
      } // comments


      if (window.comments) {
        store.dispatch(setComments(comments));
      } // user


      if (window.user) {
        store.dispatch(setUser(user));
      } // finish loading


      this.setState({
        loading: false
      });
    }
  }, {
    key: "componentWillUnmount",
    value: function componentWillUnmount() {
      // device
      window.removeEventListener("resize", this.updateDimensions);
    }
  }, {
    key: "updateDimensions",
    value: function updateDimensions() {
      var device = appHelpers.getDeviceWidth(window.innerWidth);
      store.dispatch(setDevice(device));
    }
  }, {
    key: "render",
    value: function render() {
      var displayView = React.createElement(HomePageWrapper, null);

      if (store.getState().view === 'explore') {
        displayView = React.createElement(ExplorePageWrapper, null);
      } else if (store.getState().view === 'product') {
        displayView = React.createElement(ProductViewWrapper, null);
      }

      return React.createElement("div", {
        id: "app-root"
      }, displayView);
    }
  }]);

  return App;
}(React.Component);

var AppWrapper =
/*#__PURE__*/
function (_React$Component2) {
  _inherits(AppWrapper, _React$Component2);

  function AppWrapper() {
    _classCallCheck(this, AppWrapper);

    return _possibleConstructorReturn(this, _getPrototypeOf(AppWrapper).apply(this, arguments));
  }

  _createClass(AppWrapper, [{
    key: "render",
    value: function render() {
      return React.createElement(Provider, {
        store: store
      }, React.createElement(App, null));
    }
  }]);

  return AppWrapper;
}(React.Component);

ReactDOM.render(React.createElement(AppWrapper, null), document.getElementById('explore-content'));
