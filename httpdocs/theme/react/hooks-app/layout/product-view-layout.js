import { useContext, useEffect, useRef, Suspense, lazy } from 'react';
import { AppContext } from './context/context-provider';
import StoreContextProvider, { Context } from '../modules/product-view/app/context/context-provider';
import { usePrevious } from './app-helpers';

import RightSideBar from '../modules/right-sidebar/app/right-sidebar';
import DummyProductView from '../modules/product-view/app/dummy-product-view';

const ProductView = lazy(() => import( '../modules/product-view/app/product-view'))

function ProductViewLayoutComponent(props){

    let xhr;

    const { appState, appDispatch } = useContext(AppContext);
    const { productViewState, productViewDispatch } = useContext(Context);

    const previousId = usePrevious(appState.id);

    useEffect(() => { 
        initProductViewLayout();
        return () => { if (xhr && xhr.abort) xhr.abort(); }
    },[]);

    useEffect(() => { 
        if (appState.isBack !== true && appState.isForward !== true && productViewState.product){
            appDispatch({type:'SET_VIEW_DATA',viewData:productViewState,url:appState.url});
        }
    },[productViewState])

    useEffect(() => {
        if (previousId && appState.id !== previousId){
            productViewDispatch({type:'SET_LOADING'});
            productViewDispatch({type:'RESET_STATE'});
            setTimeout(() => {
                initProductViewLayout();
            }, 1);
        }
    },[appState.id])

    function initProductViewLayout(){
        let readyData = null;
        if (appState.viewData && appState.viewData !== null) readyData = appState.viewData;
        else if (window.productViewData) readyData = window.productViewData;

        if (appState.resetLayout === true){
            if (readyData && parseInt(readyData.product.project_id) === parseInt(appState.id)) setReadydata(readyData);
            else getProductViewData();
        } else {
            if (readyData && parseInt(readyData.product.project_id) === parseInt(appState.id)) setReadydata(readyData);
            else getProductViewData();
        }
    }

    function setReadydata(readyData){
        productViewDispatch({type:'SET_LOADED',value:true})
        productViewDispatch({type:'SET_DATA',data:readyData})
        appDispatch({type:'FINISH_LOADING_VIEW'})
        // if (!appState.categoryId || appState.categoryId === 0) appDispatch({type:'SET_CATEGORIES',categoryId:parseInt(readyData.product.project_category_id)})
    }

    function getProductViewData(){
        productViewDispatch({type:'SET_LOADED',value:false})
        xhr = $.ajax({url:'/p2/'+appState.id+'?json=1'}).done(function(res){
            productViewDispatch({type:'SET_DATA',data:res})
            appDispatch({type:'SET_CATEGORIES',categories:res.categories,categoryId:parseInt(res.product.project_category_id)})
            appDispatch({type:'FINISH_LOADING_VIEW'})
        })
    }

    let rightSideBarDisplay;
    if (productViewState.loaded === true && productViewState.loading === false){
        let rightSideBarFiles = [];
        if (productViewState.filesTab) rightSideBarFiles = productViewState.filesTab.filter(file => file.active === "1");
        rightSideBarDisplay = (
            <RightSideBar 
                onChangeUrl={props.onChangeUrl}
                dataRightSideBar={productViewState.rightsidebar}
                user={productViewState.authMember}
                files={rightSideBarFiles}
                product={productViewState.product} 
                view={"product-view"} 
                id={appState.id}
            />
        )
    }
    
    let rightSideBarCssStyle = {
        minHeight:"800px",
        padding: productViewState.loaded === false ? "0" : "10px 15px 10px 0"
    }

    return (
        <React.Fragment>
            <div className="col-lg-8 col-md-8 col-sm-8 col-xs-12" id="product-main">
                <div id="product-view-container">
                    <Suspense fallback={<DummyProductView/>}>
                        <ProductView {...props} resetLayout={appState.resetLayout} />
                    </Suspense>
                </div>               
            </div>
            <div className="col-lg-2 col-md-2 col-sm-12 col-xs-12 flex-right" id="product-maker">                                                                         
                <div style={rightSideBarCssStyle} id="right-sidebar-container" className={"project-share-new col-lg-12 col-md-12 col-sm-12 col-xs-12 " + ( productViewState.loaded === false ? "dummy-fill dummy-fill-to-white" : "")}>
                    {rightSideBarDisplay}
                </div>
            </div>
        </React.Fragment>
    )
}

function ProductViewLayout(props){
    
    return (
        <StoreContextProvider>
            <ProductViewLayoutComponent {...props}/>
        </StoreContextProvider>
    )
}

export default ProductViewLayout;