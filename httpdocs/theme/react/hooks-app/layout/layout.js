import React, { useContext, Suspense, lazy } from 'react';
import { AppContext } from './context/context-provider'; 

import CategoryTree from '../modules/category-tree/app/category-tree';
import DummyCategoryTree from '../modules/category-tree/app/dummy-category-tree';

const HomePageLayout = lazy(() => import('./home-page-layout'));
import DummyHomepageLayout from './dummy-layouts/dummy-homepage-layout';

const ProductBrowseLayout = lazy(() => import( './product-browse-layout'));
import DummyProductBrowseLayout from './dummy-layouts/dummy-product-browse-layout';

const ProductViewLayout = lazy(() => import( './product-view-layout'));
import DummyProductViewLayout from './dummy-layouts/dummy-product-view-layout';

const UserProfileLayout = lazy(() => import( './user-profile-layout'));
import DummyUserProfile from '../modules/user-profile/dummy-user-profile';

function Layout(props) {

    const { appState, appDispatch } = useContext(AppContext);
    const appMinHeight = (  window.innerHeight - 220 ) + "px";

    console.log('app state');
    console.log(appState);

    let layoutDisplay, 
        mainContainerId = "", 
        MainContainerCssClass = "",
        mainWrapperId = "",
        mainWrapperCssClass = "",
        girdFlexCssClass = "",
        catTreeContainerCssClass = ""
        
    switch(appState.view) {
        case 'home-page':
            layoutDisplay = (
                <Suspense fallback={<DummyHomepageLayout/>}>
                    <HomePageLayout
                        onChangeUrl={props.onSetView}
                        appMinHeight={appMinHeight}
                    />
                </Suspense>
            )
            break;

        case 'product-browse':
            layoutDisplay = (
                <Suspense fallback={<DummyProductBrowseLayout />}>
                    <ProductBrowseLayout
                        onChangeUrl={props.onSetView}
                        appMinHeight={appMinHeight}
                        onMediaItemUpdate={props.onMediaItemUpdate}
                    />
                </Suspense>
            )
            break;

        case 'product-view':

            mainContainerId = "product-page-content";
            mainWrapperId = "product-page-view";
            mainWrapperCssClass = "display-flex";
            catTreeContainerCssClass = "col-lg-2 col-md-2 col-sm-4 col-xs-12 sidebar-left";

            layoutDisplay = (
                <Suspense fallback={<DummyProductViewLayout/>}>
                    <ProductViewLayout
                        onChangeUrl={props.onSetView}
                        onCategoryClick={props.onCategoryClick}
                        appMinHeight={appMinHeight}
                        onMediaItemUpdate={props.onMediaItemUpdate}
                    />
                </Suspense>
            )

            break;
        case 'user-profile':
            layoutDisplay = (
                <Suspense fallback={<DummyUserProfile/>}>
                    <UserProfileLayout
                        onChangeUrl={props.onSetView}
                    />
                </Suspense>
            )
            break;
        default:
            layoutDisplay = "404 PAGE"
    }
    
    let categoryTreeDisplay = (
        <CategoryTree 
            viewLoading={appState.viewLoading}
            onChangeUrl={props.onCategoryClick}
            onSetView={props.onSetView}
            appMinHeight={appMinHeight}
            categories={appState.categories}
            categoryId={appState.categoryId}
            filters={appState.filters}
            storeConfig={appState.storeConfig}
        />
    )
    
    if (!appState.categories) categoryTreeDisplay = <DummyCategoryTree/>

    let appLayoutDisplay;
    if (appState.view === "home-page" || appState.view === "product-browse" || appState.view === "product-view" ){
        appLayoutDisplay = (
            <React.Fragment>
                    <div className="main">
                        <div className="pui-sidebar">
                            <div id="category-tree-container">
                                {categoryTreeDisplay}
                            </div>
                        </div>
                        {layoutDisplay}
                    </div>
            </React.Fragment>
        )        
    }/* else if (){
        appLayoutDisplay = (
            <React.Fragment>
                        <div className={girdFlexCssClass}>
                            <div className={catTreeContainerCssClass}>
                                <div id="category-tree-container">
                                    {categoryTreeDisplay}
                                </div>
                            </div>
                            {layoutDisplay}
                        </div>
            </React.Fragment>
        )
    }*/ else {
        appLayoutDisplay = layoutDisplay;
    }

    return (
        <React.Fragment>
            {appLayoutDisplay}
        </React.Fragment>
    )
}

export default Layout;