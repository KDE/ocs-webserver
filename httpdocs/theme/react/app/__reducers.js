const reducer = Redux.combineReducers({
  products:productsReducer,
  product:productReducer,
  lightboxGallery:lightboxGalleryReducer,
  pagination:paginationReducer,
  topProducts:topProductsReducer,
  categories:categoriesReducer,
  comments:commentsReducer,
  users:usersReducer,
  user:userReducer,
  supporters:supportersReducer,
  domain:domainReducer,
  env:envReducer,
  device:deviceReducer,
  view:viewReducer,
  filters:filtersReducer
});

/* reducers */

  function productsReducer(state = {}, action){
    if (action.type === 'SET_PRODUCTS'){
      return action.products;
    } else {
      return state;
    }
  }

  function productReducer(state = {}, action){
    if (action.type === 'SET_PRODUCT'){
      return action.product;
    } else if (action.type === 'SET_PRODUCT_FILES'){
      const s = Object.assign({},state,{
        r_files:action.files
      });
      return s;
    } else if (action.type === 'SET_PRODUCT_UPDATES'){
      const s = Object.assign({},state,{
        r_updates:action.updates
      });
      return s;
    } else if (action.type === 'SET_PRODUCT_RATINGS'){
      const s = Object.assign({},state,{
        r_ratings:action.ratings
      });
      return s;
    } else if (action.type === 'SET_PRODUCT_LIKES'){
      const s = Object.assign({},state,{
        r_likes:action.likes
      });
      return s;
    } else if (action.type === 'SET_PRODUCT_PLINGS'){
      const s = Object.assign({},state,{
        r_plings:action.plings
      });
      return s;
    } else if (action.type === 'SET_PRODUCT_USER_RATINGS'){
      const s = Object.assign({},state,{
        r_userRatings:action.userRatings
      });
      return s;
    } else if (action.type === 'SET_PRODUCT_GALLERY'){
      const s = Object.assign({},state,{
        r_gallery:action.gallery
      });
      return s;
    } else if (action.type === 'SET_PRODUCT_COMMENTS'){
      const s = Object.assign({},state,{
        r_comments:action.comments
      });
      return s;
    } else if (action.type === 'SET_PRODUCT_ORIGINS'){
      const s = Object.assign({},state,{
        r_origins:action.origins
      });
      return s;
    } else if (action.type === 'SET_PRODUCT_RELATED'){
      const s = Object.assign({},state,{
        r_related:action.related
      });
      return s;
    } else if (action.type === 'SET_PRODUCT_MORE_PRODUCTS'){
      const s = Object.assign({},state,{
        r_more_products:action.products
      });
      return s;
    } else if (action.type === 'SET_PRODUCT_MORE_PRODUCTS_OTHER_USERS'){
      const s = Object.assign({},state,{
        r_more_products_other_users:action.products
      });
      return s;
    } else if (action.type === 'SET_PRODUCT_TAGS'){
      const s = Object.assign({},state,{
        r_tags_user:action.userTags,
        r_tags_system:action.systemTags
      });
      return s;
    } else {
      return state;
    }
  }

  function lightboxGalleryReducer(state = {}, action){
    if (action.type === 'SHOW_LIGHTBOX'){
      const s = Object.assign({},state,{
        show:true,
        currentItem:action.item
      });
      return s;
    } else if (action.type === 'HIDE_LIGHTBOX'){
      const s = Object.assign({},state,{
        show:false
      });
      return s;
    } else {
      return state;
    }
  }

  function paginationReducer(state = {}, action){
    if (action.type === 'SET_PAGINATION'){
      return action.pagination;
    } else {
      return state;
    }
  }

  function topProductsReducer(state = {}, action){
    if (action.type === 'SET_TOP_PRODUCTS'){
      return action.products;
    } else {
      return state;
    }
  }

  function categoriesReducer(state = {}, action){
    if (action.type === 'SET_CATEGORIES'){
      const s = Object.assign({},state,{
        items:categories
      });
      return s;
    } else if (action.type === 'SET_CURRENT_CAT'){
      const s = Object.assign({},state,{
        current:action.cat
      });
      return s;
    } else if (action.type === 'SET_CURRENT_SUBCAT'){
      const s = Object.assign({},state,{
        currentSub:action.cat
      });
      return s;
    } else if (action.type === 'SET_CURRENT_SECONDSUBCAT'){
      const s = Object.assign({},state,{
        currentSecondSub:action.cat
      });
      return s;
    } else {
      return state;
    }
  }

  function commentsReducer(state = {},action){
    if (action.type === 'SET_COMMENTS'){
      return action.comments;
    } else {
      return state;
    }
  }

  function usersReducer(state = {},action){
    if (action.type === 'SET_USERS'){
      return action.users;
    } else {
      return state;
    }
  }

  function userReducer(state = {}, action){
    if (action.type === 'SET_USER'){
      return action.user;
    } else {
      return state;
    }
  }

  function supportersReducer(state = {},action){
    if (action.type === 'SET_SUPPORTERS'){
      return action.supporters;
    } else {
      return state;
    }
  }

  function domainReducer(state = {},action){
    if (action.type === 'SET_DOMAIN'){
      return action.domain;
    } else {
      return state;
    }
  }

  function envReducer(state = {},action){
    if (action.type === 'SET_ENV'){
      return action.env;
    } else {
      return state;
    }
  }

  function deviceReducer(state = {}, action){
    if (action.type === 'SET_DEVICE'){
      return action.device;
    } else {
      return state;
    }
  }

  function viewReducer(state = {}, action){
    if (action.type === 'SET_VIEW'){
      return action.view;
    } else {
      return state;
    }
  }

  function filtersReducer(state = {}, action){
    if (action.type === 'SET_FILTERS'){
      return action.filters;
    } else {
      return state;
    }
  }

/* /reducers */

/* dispatch */

  function setProducts(products){
    return {
      type:'SET_PRODUCTS',
      products:products
    }
  }

  function setProduct(product){
    return {
      type:'SET_PRODUCT',
      product:product
    }
  }

  function setProductFiles(files){
    return {
      type:'SET_PRODUCT_FILES',
      files:files
    }
  }

  function setProductUpdates(updates){
    return {
      type:'SET_PRODUCT_UPDATES',
      updates:updates
    }
  }

  function setProductRatings(ratings){
    return {
      type:'SET_PRODUCT_RATINGS',
      ratings:ratings
    }
  }

  function setProductLikes(likes){
    return {
      type:'SET_PRODUCT_LIKES',
      likes:likes
    }
  }

  function setProductPlings(plings){
    return {
      type:'SET_PRODUCT_PLINGS',
      plings:plings
    }
  }

  function setProductUserRatings(userRatings){
    return {
      type:'SET_PRODUCT_USER_RATINGS',
      userRatings:userRatings
    }
  }

  function setProductGallery(gallery){
    return {
      type:'SET_PRODUCT_GALLERY',
      gallery:gallery
    }
  }

  function setProductComments(comments){
    return {
      type:'SET_PRODUCT_COMMENTS',
      comments:comments
    }
  }

  function setProductOrigins(origins){
    return {
      type:'SET_PRODUCT_ORIGINS',
      origins:origins
    }
  }

  function setProductRelated(related){
    return {
      type:'SET_PRODUCT_RELATED',
      related:related
    }
  }

  function setProductMoreProducts(products){
    return {
      type:'SET_PRODUCT_MORE_PRODUCTS',
      products:products
    }
  }

  function setProductMoreProductsOtherUsers(products){
    return {
      type:'SET_PRODUCT_MORE_PRODUCTS_OTHER_USERS',
      products:products
    }
  }

  function setProductTags(userTags,systemTags){
    return {
      type:'SET_PRODUCT_TAGS',
      userTags:userTags,
      systemTags:systemTags
    }
  }

  function showLightboxGallery(num){
    return {
      type:'SHOW_LIGHTBOX',
      item:num
    }
  }

  function hideLightboxGallery(){
    return {
      type:'HIDE_LIGHTBOX'
    }
  }

  function setPagination(pagination){
    return {
      type:'SET_PAGINATION',
      pagination:pagination
    }
  }

  function setTopProducts(topProducts){
    return {
      type:'SET_TOP_PRODUCTS',
      products:topProducts
    }
  }

  function setCategories(categories){
    return {
      type:'SET_CATEGORIES',
      categories:categories
    }
  }

  function setCurrentCategory(cat){
    return {
      type:'SET_CURRENT_CAT',
      cat:cat
    }
  }

  function setCurrentSubCategory(cat){
    return {
      type:'SET_CURRENT_SUBCAT',
      cat:cat
    }
  }

  function setCurrentSecondSubCategory(cat){
    return {
      type:'SET_CURRENT_SECONDSUBCAT',
      cat:cat
    }
  }

  function setComments(comments){
    return {
      type:'SET_COMMENTS',
      comments:comments
    }
  }

  function setUsers(users){
    return {
      type:'SET_USERS',
      users:users
    }
  }

  function setUser(user){
    return {
      type:'SET_USER',
      user:user
    }
  }

  function setSupporters(supporters){
    return {
      type:'SET_SUPPORTERS',
      supporters:supporters
    }
  }

  function setDomain(domain){
    return {
      type:'SET_DOMAIN',
      domain:domain
    }
  }

  function setEnv(env){
    return {
      type:'SET_ENV',
      env:env
    }
  }

  function setDevice(device){
    return {
      type:'SET_DEVICE',
      device:device
    }
  }

  function setView(view){
    return {
      type:'SET_VIEW',
      view:view
    }
  }

  function setFilters(filters){
    return {
      type:'SET_FILTERS',
      filters:filters
    }
  }

/* /dispatch */
