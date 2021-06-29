import React from 'react';
import UserProfileReducer,{userProfileReducerInitialState} from './user-profile-reducer.js';

export const Context = React.createContext();
const Provider = Context.Provider;

const UserProfileContextProvider = (props) => {
  const [ userProfileState, userProfileDispatch ] = React.useReducer(UserProfileReducer,userProfileReducerInitialState);

  return(
    <Provider {...props} value={{
      userProfileState,userProfileDispatch
    }}/>
  )
}

export default UserProfileContextProvider;
