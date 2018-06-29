const reducer = Redux.combineReducers({
  products:productsReducer,
  pagination:paginationReducer,
  topProducts:topProductsReducer,
  categories:categoriesReducer,
  comments:commentsReducer,
  users:usersReducer,
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
        current:action.catId
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

  function setCurrentCategory(catId){
    return {
      type:'SET_CURRENT_CAT',
      catId:catId
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
