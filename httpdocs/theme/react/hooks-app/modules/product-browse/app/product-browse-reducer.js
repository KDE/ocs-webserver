  export const productBrowseReducerInitialState = {
    isPlaying:false,
    current:null,
    currentPlayIndex:null
  }
  
  function AppReducer(state,action){
    switch(action.type){
      case "SET_CURRENT_ITEM":{
        const s = {
            isPlaying:true,
            current:action.itemId,
            currentPlayIndex:action.pIndex
        }
        return s;
      }
      case "PAUSE":{
        const s = {
            ...state,
            isPlaying:false
        }
        return s;
      }
      default:{
        return state;
      }
    }
  }
  
  export default AppReducer;
  