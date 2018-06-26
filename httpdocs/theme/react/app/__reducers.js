const reducer = Redux.combineReducers({
  products:productsReducer,
  users:usersReducer,
  supporters:supportersReducer,
  domain:domainReducer,
  env:envReducer,
  device:deviceReducer
});

function productsReducer(state = {}, action){
  if (action.type === 'SET_PRODUCTS'){
    return action.products;
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

function setProducts(products){
  return {
    type:'SET_PRODUCTS',
    products:products
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
