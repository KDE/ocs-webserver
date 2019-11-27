window.appHelpers = function () {
  function getEnv(domain) {
    let env;

    if (this.splitByLastDot(domain) === 'com' || this.splitByLastDot(domain) === 'org') {
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

    const timestamp = Math.floor(new Date().getTime() / 1000 + 3600);
    const hash = md5(salt + file.collection_id + timestamp);
    return hash;
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

  function calculateProductLaplaceScore(ratings) {
    let laplace_score = 0;
    let upvotes = 0;
    let downvotes = 0;
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
    getNumberOfProducts,
    generatePaginationObject,
    calculateProductRatings,
    getActiveRatingsNumber,
    getFilesSummary,
    checkIfLikedByUser,
    getLoggedUserRatingOnProduct,
    calculateProductLaplaceScore
  };
}();
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
