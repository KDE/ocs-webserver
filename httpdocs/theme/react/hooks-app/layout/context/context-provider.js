import React from 'react';
import AppReducer,{appReducerInitialState} from './app-reducer.js';

export const AppContext = React.createContext();
const Provider = AppContext.Provider;

const AppContextProvider = (props) => {
  const [ appState, appDispatch ] = React.useReducer(AppReducer,appReducerInitialState);

  return(
    <Provider {...props} value={{
      appState,appDispatch
    }}/>
  )
}

export default AppContextProvider;
