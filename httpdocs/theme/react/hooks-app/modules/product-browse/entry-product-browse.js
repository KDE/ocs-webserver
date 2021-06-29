import React, { useContext, useEffect, useState } from 'react';
import ReactDOM from 'react-dom';
import StoreContextProvider, { Context } from './app/context/context-provider.js';
import ProductBrowse from './app/product-browse.js';
import CategoryTreeWrapper from '../category-tree-static/app/category-tree';
import RightSideBar from '../right-sidebar/app/right-sidebar';

function ProductBrowseWrapper(){

    const { productBrowseState, productBrowseDispatch } = useContext(Context);
    
    useEffect(() => {
        productBrowseDispatch({type:'SET_DATA',data:productBrowseData})
    },[])
    
    let rightSidebarDisplay, catTreeDisplay;
    if (productBrowseState.loading === false){
        rightSidebarDisplay = (
            <RightSideBar 
                dataRightSideBar={productBrowseState.rightsidebarData}
                user={productBrowseState.authMember}
                product={productBrowseState.product} 
                view={"product-browse"} 
            />
        )        
        catTreeDisplay = (
            <CategoryTreeWrapper
                tags={productBrowseData.categoriesTopTags}
                storeStyle={productBrowseData.header.ocsStoreTemplate}
            />
        )
    }
    
    return (
        <div className="main">
            <div className="pui-sidebar">
                {catTreeDisplay}
            </div>
            <div className="pui-main">
                <ProductBrowse/>
            </div>
            <div className="pui-sidebar-right">
                {rightSidebarDisplay}
            </div>
        </div>
    )
}

function ProductBrowseContainer(){
    return (
        <StoreContextProvider>
            <ProductBrowseWrapper/>
        </StoreContextProvider>
    )
}

const rootElement = document.getElementById("product-browse-container");
ReactDOM.render(<ProductBrowseContainer />, rootElement);