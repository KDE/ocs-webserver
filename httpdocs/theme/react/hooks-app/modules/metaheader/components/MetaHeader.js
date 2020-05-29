import "core-js/shim";
import "regenerator-runtime/runtime";
import React from 'react'
import MetaheaderContextProvider from '../contexts/MetaheaderContext';
import MetaHeaderComponent from "./MetaHeaderComponent";

import '../style/metaheader.css';
import '../style/metaheader-extra.css';

const MetaHeader = (props) => {
  console.log('meta header module');
  return (      
    <MetaheaderContextProvider {...props}>      
      <MetaHeaderComponent />  
    </MetaheaderContextProvider>
    
  )
}

export default MetaHeader
