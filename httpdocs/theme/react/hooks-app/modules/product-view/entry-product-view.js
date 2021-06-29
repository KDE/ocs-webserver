import React, { useContext, useEffect } from 'react';
import ReactDOM from 'react-dom';
import ProductView from './app/product-view';
import StoreContextProvider, {Context} from './app/context/context-provider';
import CategoryTreeWrapper from '../category-tree-static/app/category-tree';
import RightSideBar from '../right-sidebar/app/right-sidebar';
import { Base64 } from 'js-base64';

function ProductViewWrapper(){
    
    const { productViewState, productViewDispatch } = useContext(Context);

    useEffect(() => {
        productViewDispatch({type:'SET_DATA',data:JSON.parse(Base64.decode(productViewDataEncoded))});
    },[])


    let rightSidebarDisplay, catTreeDisplay;
    if (productViewState.loading === false){
        let rightSideBarFiles = [];
        if (productViewState.filesTab && productViewState.filesTab.length > 0) rightSideBarFiles = productViewState.filesTab.filter(file => file.active === "1");    
        rightSidebarDisplay = (
            <RightSideBar 
                dataRightSideBar={productViewState.rightsidebarData}
                user={productViewState.authMember}
                maker={productViewState.member}
                files={rightSideBarFiles}
                product={productViewState.product} 
                view={"product-view"} 
                isCollectionView={productViewState.isCollectionView}
            />
        )
        
        catTreeDisplay = (
            <CategoryTreeWrapper
                tags={productViewState.categoriesTopTags}
                storeStyle={productViewState.header.ocsStoreTemplate}
            />
        )
    }

    return (
        <React.Fragment>
            <div className="pui-sidebar">
                {catTreeDisplay}
            </div>
            <div className="pui-main">
                <ProductView/>
            </div>
            <div className="pui-sidebar-right">
                {rightSidebarDisplay}
            </div>
        </React.Fragment>
    )
}

function ProductViewContainer(){

    return (
        <StoreContextProvider>
            <div className="main">
                <ProductViewWrapper/>
            </div> 
        </StoreContextProvider>
    )
}

const rootElement = document.getElementById("product-view-container");
ReactDOM.render(<ProductViewContainer />, rootElement);