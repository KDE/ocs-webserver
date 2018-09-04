const metaHeaderReducer = Redux.combineReducers({
  user:userReducer
});

/* reducers */

  function userReducer(state = {}, action){
    if (action.type === 'SET_USER'){
      return action.user;
    } else {
      return state;
    }
  }

/* /reducers */

/* dispatch */

  function setUser(user){
    return {
      type:'SET_USER',
      user:user
    }
  }

/* /dispatch */
