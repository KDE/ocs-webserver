import React, {createContext,useState} from 'react';
export const AppSupportersContext = createContext();

const AppSupportersContextProvider = (props) => {
    
    const [state, setState] = useState({...window.data})
    return (
        <AppSupportersContext.Provider value={{state, setState}}>
         {props.children}
        </AppSupportersContext.Provider>        
    )
}
export default AppSupportersContextProvider




