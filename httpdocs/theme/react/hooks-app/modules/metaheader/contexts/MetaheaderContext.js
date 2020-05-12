import React, {createContext,useState} from 'react';
//import {metaheaderReducer} from '../reducers/metaheaderReducer';
export const MetaheaderContext = createContext();

const MetaheaderContextProvider = (props) => {
    //const [state, dispatch] = useReducer(metaheaderReducer,{...window.config});
    const [state, setState] = useState({...props.config})
    return (
        <MetaheaderContext.Provider value={{state, setState}}>
         {props.children}
        </MetaheaderContext.Provider>        
    )
}
export default MetaheaderContextProvider




