import React, { useEffect, useContext, Suspense, lazy  } from 'react';

import { isMobile } from 'react-device-detect';
import { AppContext } from './context/context-provider'; 
import { GetViewFromUrl, GetIdFromUrl, usePrevious } from './app-helpers';

import Layout from './layout';

const AudioPlayer = lazy(() => import( './partials/audio-player/audio-player'));

function App(){

    const { appState, appDispatch } = useContext(AppContext);
    const previousView = usePrevious(appState.view);

    useEffect(() => { 
        window.addEventListener('forward', event => { onForwardButtonEvent(event,history.state) });
        window.addEventListener('back', event => { onBackButtonEvent(event, history.state) });
    },[])

    useEffect(() => {
        if (appState.view && previousView !== null && appState.view !== previousView) appDispatch({type:'RESET_LAYOUT',resetLayout:true});
    },[appState.view])

    useEffect(() => {
        if (appState.isChangeUrl === true){
            const newView = appState.newView;
            onSetView(newView.url,newView.title,newView.id)
        }
    },[appState.isChangeUrl])

    function onSetView(url,title,catId){
        
        if (url.indexOf('/browse/') > -1) url = "/browse2/" + url.split('/browse/')[1];
        else if (url.indexOf('/p/') > -1) url = "/p2/" + url.split('/p/')[1];
        else if (url.indexOf('/u/') > -1) url = "/u2/" + url.split('/u/')[1];
        
        const view = GetViewFromUrl(url);
        const id = GetIdFromUrl(url);

        const historyVal = window.location;
        window.history.pushState("", title, url);
        if (view === "home-page") document.title = "Pling.com";
        else if (title) document.title = title + " - Pling.com";

        let categoryId = 0;
        if (catId || catId === 0) categoryId = catId;
        else if (url.indexOf('cat/') > -1){
            categoryId = url.split('/cat/')[1];
            categoryId = parseInt(categoryId.split('/')[0]);
        }

        let resetLayout = true;
        if (view === appState.view) resetLayout = true;

        let viewLoading = true;
        /*if (view === "home-page" && window.homePageData ||
            view === "product-browse" && window.productBrowseData ||
            view === "product-view" && window.productViewData ){
            viewLoading = false;
        }*/

        appDispatch({
            type:'SET_VIEW',
            url:url,
            view:view,
            location:historyVal,
            id:id,
            categoryId:categoryId,
            isBack:false,
            isForward:false,
            resetLayout:resetLayout,
            viewLoading:viewLoading,
        });
        
        window.scrollTo({
            top: 0,
            behavior: 'smooth'
        });

    }

    function onCategoryClick(cat,catLink){
        appDispatch({type:'SET_TOTAL_COUNT',totalcount:cat.product_count})
        const title = cat.title ? cat.title : cat.name;
        onSetView(catLink,title);
    }

    function onBackButtonEvent(e){
    
        window.scrollTo({
            top: 0,
            behavior: 'smooth'
        });

        const storedHistory = JSON.parse(localStorage.getItem("history"));
        let currentHistoryIndex = parseInt(window.localStorage.getItem('currentHistoryIndex') - 2);

        let categories;
        if (storedHistory[currentHistoryIndex].viewData && storedHistory[currentHistoryIndex].viewData.categories ){
            categories = storedHistory[currentHistoryIndex].viewData.categories;
        }

        const viewData = storedHistory[currentHistoryIndex].viewData;
        if (viewData) viewData.loaded = true;

        appDispatch({
            type:'SET_VIEW',
            view:storedHistory[currentHistoryIndex].view,
            id:storedHistory[currentHistoryIndex].id,
            categoryId:storedHistory[currentHistoryIndex].categoryId,
            categories:categories,
            url:storedHistory[currentHistoryIndex].url,
            viewData:viewData,
            historyIndex:currentHistoryIndex,
            isBack:true,
            isForward:false,
            viewLoading:false,
        })
    }

    function onForwardButtonEvent(e){

        window.scrollTo({
            top: 0,
            behavior: 'smooth'
        });

        const storedHistory = JSON.parse(localStorage.getItem("history"));
        const currentHistoryIndex = parseInt(window.localStorage.getItem('currentHistoryIndex'));

        let categories;
        if (storedHistory[currentHistoryIndex].viewData && storedHistory[currentHistoryIndex].viewData.categories ){
            categories = storedHistory[currentHistoryIndex].viewData.categories;
        }

        const viewData = storedHistory[currentHistoryIndex].viewData;
        if (viewData) viewData.loaded = true;
        
        appDispatch({
            type:'SET_VIEW',
            view:storedHistory[currentHistoryIndex].view,
            id:storedHistory[currentHistoryIndex].id,
            categoryId:storedHistory[currentHistoryIndex].categoryId,
            categories:categories,
            url:storedHistory[currentHistoryIndex].url,
            viewData:viewData,
            historyIndex:currentHistoryIndex,
            isForward:true,
            isBack:false,
            viewLoading:false,
        })
    }

    function onMediaItemUpdate(mediaPlayerData){
        appDispatch({type:'SET_MEDIA_PLAYER_DATA',mediaPlayerData:mediaPlayerData})
    }

    return (
        <div id="app" className={(isMobile === true ? "mobile-view" : "desktop-view")}>
            <Layout 
                onMediaItemUpdate={onMediaItemUpdate}
                onCategoryClick={onCategoryClick}
                onSetView={onSetView}
            />
            <Suspense fallback={''}>
                <AudioPlayer 
                    onChangeUrl={onSetView}
                />
            </Suspense>
            <div className="footer container-wide">
                <div className="container-normal text-center">
                    <div className="footer-links link-primary-invert">
                        <p><a className="pui-pill" href="/terms">Terms</a></p>
                        <p><a className="pui-pill" href="/privacy">Privacy</a></p>
                        <p><a className="pui-pill" href="/imprint">Imprint</a></p>
                        <p><a className="pui-pill" href="/contact">Contact</a></p>
                        <p><a className="pui-pill facebook-pill" href="https://www.facebook.com/opendesktop.org">Facebook</a></p>
                        <p><a className="pui-pill twitter-pill" href="https://twitter.com/opendesktop">Twitter</a></p>
                    </div>
                    <p className="footer-text"><span>© 2021 pling.com - Libre Publishing</span> All rights reserved. All trademarks are copyright by their respective owners. All contributors are responsible for their uploads.</p>
                </div>
            </div>
        </div>
    )
}

export default App;