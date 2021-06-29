import React from 'react';
import ProductViewReducer,{ProductViewReducerInitialState} from './product-view-reducer.js';

export const Context = React.createContext();
const Provider = Context.Provider;

const StoreContextProvider = (props) => {
  const [ productViewState, productViewDispatch ] = React.useReducer(ProductViewReducer,ProductViewReducerInitialState);

  return(
    <Provider {...props} value={{
      productViewState,productViewDispatch
    }}/>
  )
}

export default StoreContextProvider;
