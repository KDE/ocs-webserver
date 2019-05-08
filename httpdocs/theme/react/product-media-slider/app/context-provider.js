import React from 'react';
import AppReducer,{AppReducerInitialState} from './reducers/app-reducer.js';

export const Context = React.createContext();
const Provider = Context.Provider;

const StoreContextProvider = (props) => {
  const [appState, appDispatch] = React.useReducer(AppReducer,AppReducerInitialState);

  return(
    <Provider {...props} value={{
      appState,appDispatch
    }}/>
  )
}

export default StoreContextProvider;
