import React from 'react';
import CatTreeReducer,{catTreeInitialState} from './category-tree-reducer';

export const Context = React.createContext();
const Provider = Context.Provider;

const StoreContextProvider = (props) => {
  const [catTreeState,catTreeDispatch] = React.useReducer(CatTreeReducer,catTreeInitialState);

  return(
    <Provider {...props} value={{
      catTreeState,catTreeDispatch
    }}/>
  )
}

export default StoreContextProvider;
