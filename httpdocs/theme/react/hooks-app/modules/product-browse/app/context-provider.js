import React from 'react';
import ProductBrowseReducer,{productBrowseReducerInitialState} from './product-browse-reducer';

export const Context = React.createContext();
const Provider = Context.Provider;

const StoreContextProvider = (props) => {
  const [productBrowseState,productBrowseDispatch] = React.useReducer(ProductBrowseReducer,productBrowseReducerInitialState);

  return(
    <Provider {...props} value={{
        productBrowseState,productBrowseDispatch
    }}/>
  )
}

export default StoreContextProvider;
