import "core-js/shim";
import "regenerator-runtime/runtime";
import React from 'react'
import MetaheaderContextProvider from '../contexts/MetaheaderContext';
import MetaHeaderComponent from "./MetaHeaderComponent";

const MetaHeader = (props) => {
  
  return (      
    <MetaheaderContextProvider {...props}>      
      <MetaHeaderComponent />  
    </MetaheaderContextProvider>
    
  )
}

export default MetaHeader
