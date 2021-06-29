import { useContext, useState, useRef, useEffect, Suspense, lazy } from 'react';
import { AppContext } from './context/context-provider';
import StoreContextProvider, { Context } from '../modules/product-browse/app/context/context-provider';
import { usePrevious } from './app-helpers';
import DummyProductList from '../modules/product-browse/app/dummy-product-list';

const RightSideBar = lazy(() => import('../modules/right-sidebar/app/right-sidebar'))
const ProductBrowse = lazy(() => import('../modules/product-browse/app/product-browse'))


import './style/app.css';

function ProductBrowseLayoutComponent(props){

    let xhr, browseStateTimeOut;

    const { appState, appDispatch } = useContext(AppContext)
    const { productBrowseState, productBrowseDispatch } = useContext(Context);

    const previousUrl = usePrevious(appState.url);
    const previousCatId = usePrevious(appState.categoryId);
    const previousTag = usePrevious(appState.tag);

    useEffect(() => {
        initProductBrowse();
        if (productBrowseState.loaded === false) productBrowseDispatch({type:'LOAD_LAYOUT',value:true})
        return () => { 
            clearTimeout(browseStateTimeOut);
            if (xhr && xhr.abort) xhr.abort(); 
        }
    },[])
    
    useEffect(() => {
        if (productBrowseState.loadLayout === true){
            // browseStateTimeOut = setTimeout(function(){      
                productBrowseDispatch({type:'LOAD_LAYOUT',value:false});
                initProductBrowse();
            // }, 400);
        }
    },[productBrowseState.loadLayout])

    useEffect(() => {
        if (appState.isBack !== true && appState.isForward !== true && productBrowseState.products && productBrowseState.products.length > 0){
            appDispatch({type:'SET_VIEW_DATA',viewData:productBrowseState,url:appState.url});
        }
    },[productBrowseState])

    useEffect(() => {
        if (productBrowseState.totalcount !== null) appDispatch({type:'SET_TOTAL_COUNT',totalcount:productBrowseState.totalcount})
    },[productBrowseState.totalcount])

    useEffect(() => {
        if (appState.url !== null && previousUrl){
            let appStateUrl = appState.url;
            if (appState.url.indexOf('/browse2/') > -1)  appStateUrl = "/browse2/" + appState.url.split('/browse2/')[1];
            if (appStateUrl !== previousUrl){
                const sameCatId = appState.categoryId === previousCatId ? true : false;
                productBrowseDispatch({type:'LOAD_LAYOUT',value:true,sameCatId:sameCatId})
            }
        }
    },[appState.url])

    function initProductBrowse(){   
        let readyData = null;
        if (appState.viewData && appState.viewData !== null) readyData = appState.viewData;
        else if (window.productBrowseData ){
            if (!appState.tag || appState.tag && appState.tag.tag_id === window.productBrowseData.filters.tag[0]) readyData = window.productBrowseData;
        }
        if (appState.resetLayout === true){
            if (readyData){
                const readyDataIsForState = checkIfReadyDataIsForState(readyData);
                if (readyDataIsForState === true) setReadyData(readyData);
                else onGetViewData();
            }
            else onGetViewData()
        } else {
            if (readyData){
                const readyDataIsForState = checkIfReadyDataIsForState(readyData);
                if (readyDataIsForState === true) setReadyData(readyData);
                else onGetViewData();
            }
            else onGetViewData()
        }
    }

    function checkIfReadyDataIsForState(readyData){

        let val = false;

        if (parseInt(readyData.cat_id) === parseInt(appState.categoryId) || !readyData.cat_id && !appState.categoryId){
            val = true;
            if (appState.url.indexOf('/page/') > -1){
                const page = parseInt(appState.url.split('/page/')[1].split('/')[0]);
                if (readyData.page === page){
                    val = true;
                    if (appState.url.indexOf('/order/') -1){
                        if (readyData.filters.order === appState.url.split('/order/')[1].split('/')[0]) val = true;
                        else val = false;
                    }
                } 
                else val = false;
            }
        }
        return val;
    }

    function setReadyData(readyData){
        readyData.loaded = true;
        readyData.loading = false;
        readyData.productsLoading = false;
        productBrowseDispatch({type:'SET_LOADED',value:true})
        productBrowseDispatch({type:'RESET_PRODUCTS'});
        productBrowseDispatch({type:'SET_DATA',data:readyData});
        onFinishLoadingView(readyData);
    }

    function onGetViewData(){
        let page = 1;
        if (appState.url.indexOf('/page/') > -1) page = parseInt(appState.url.split('/page/')[1].split('/')[0]);
        let order = 'latest';
        if (appState.url.indexOf('/order/') > -1) order = appState.url.split('/order/')[1].split('/')[0];
        productBrowseDispatch({type:'RESET_STATE',totalcount:appState.totalcount,page:page,sameCatId:productBrowseState.sameCatId})
        getProductBrowseData(appState.url,page,order,true,appState.totalcount);
    }

    function onPageChange(url,page){
        url = url.split('/browse/')[1];
        url = '/browse2/' + url;
        productBrowseDispatch({type:'SET_LOADED',value:false})
        props.onChangeUrl(url);
    }

    function onOrderChange(url,order){
        url = "/browse2";
        if (appState.categoryId) url += "/cat/"+appState.categoryId;
        url += "/page/1/order/"+order;
        props.onChangeUrl(url,window.title);
    }

    function getProductBrowseData(url,page,order,fullData,totalcount){
        // set page for pagination before products load
        if (!page){
            page = 1;
            if (url.indexOf('/page/') > -1){
                page = url.split('/page/')[1];
                if (page.indexOf('/') > -1) page = parseInt(page.split('/')[0]);
            }
        }
        productBrowseDispatch({type:'SET_PAGE',page:page});

        // set pagination total page count before products load
        if (totalcount) productBrowseDispatch({type:'SET_TOTAL_COUNT',totalcount:totalcount})

        // set order product load
        if (!order){
            order = "latest";
            if (url.indexOf('/order/') > -1) order = url.split('/order/')[1];
            else if (url.indexOf('/ord/') > -1) order = url.split('/ord/')[1];
            if (order.indexOf('/') > -1) order = order.split('/')[0];
        }
        productBrowseDispatch({type:'SET_ORDER_FILTER',order:order})

        // set products loading
        productBrowseDispatch({type:'SET_PRODUCTS_LOADING'});
        const ajaxUrl = url + ( url.indexOf('?') > -1 ? '&' : '?') + 'json=1';
        // load products
        xhr = $.ajax({url:ajaxUrl}).done(function(data){
            if (fullData === true) productBrowseDispatch({type:'SET_DATA',data:data})
            else productBrowseDispatch({type:'SET_PRODUCTS',data:data})
            onFinishLoadingView(data);
        })
    }

    function onFinishLoadingView(data){
        appDispatch({type:'SET_CATEGORIES',categories:data.categories,categoryId:data.cat_id,categoryTags:data.catTags,storeConfig:data.storeConfig,src:"product-browse"});
        appDispatch({type:'FINISH_LOADING_VIEW'})
    }


    let isLoading = productBrowseState.productsLoading === true || productBrowseState.loading === true ? true : false;
    if (productBrowseState.sameCatId === true) isLoading = false;

    const dataRightSideBar = {
        comments:productBrowseState.comments,
        topprods:productBrowseState.topprods,
        authMember:productBrowseState.authMember
    }

    return (
        <React.Fragment>
            <div className="GridFlex-cell content">
                <div id="product-browse-container">
                    <Suspense fallback={<DummyProductList showFilters={true}/>}>
                        <ProductBrowse
                            viewMode={"layout"}
                            onChangeUrl={props.onChangeUrl}
                            onPageChange={onPageChange}
                            onOrderChange={onOrderChange}
                            onMediaItemUpdate={props.onMediaItemUpdate}
                        />
                    </Suspense>
                </div>
            </div>
            <div className="GridFlex-cell sidebar-right">
                <div style={{minHeight:props.appMinHeight}} className="project-share-new col-lg-12 col-md-12 col-sm-12 col-xs-12" id="right-sidebar-container">
                    <RightSideBar
                        view={"product-browse"}
                        viewMode={"layout"}
                        onChangeUrl={props.onChangeUrl}
                        dataRightSideBar={dataRightSideBar}
                        user={productBrowseState.authMember}
                        isLoading={isLoading}
                    />
                </div>
            </div>
        </React.Fragment>
    )
}

function ProductBrowseLayout(props){
    return (
    <StoreContextProvider>
        <ProductBrowseLayoutComponent
            {...props}
        />
    </StoreContextProvider>
    )
}

export default ProductBrowseLayout;