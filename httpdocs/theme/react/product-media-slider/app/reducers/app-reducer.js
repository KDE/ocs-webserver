export const AppReducerInitialState = {
    loading:true,
    mediaType:null,
    product:null
  }
  
  function AppReducer(state,action){
    switch(action.type){
      case 'SET_PRODUCT':{
        return {... state, product:action.product}
      }
      default:{
        return state;
      }
    }
  }
  
  export default AppReducer;
  