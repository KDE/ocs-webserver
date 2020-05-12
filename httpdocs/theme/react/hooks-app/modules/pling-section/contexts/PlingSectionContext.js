import React, {createContext,useState} from 'react';
export const PlingSectionContext = createContext();

const PlingSectionContextProvider = (props) => {
    
    const [state, setState] = useState({...props.config})
    return (
        <PlingSectionContext.Provider value={{state, setState}}>
         {props.children}
        </PlingSectionContext.Provider>        
    )
}
export default PlingSectionContextProvider




