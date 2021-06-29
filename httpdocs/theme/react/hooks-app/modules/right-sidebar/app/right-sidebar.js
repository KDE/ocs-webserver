import React, { lazy, Suspense, useEffect, useState } from 'react';
import '../style/right-sidebar.css';

import BlurBG from '../../../layout/style/media/blur2.png';

// import RightSideBarExploreTemplate from './templates/right-sidebar-explore-template';
// import RightSideBarProductTemplate from './templates/right-sidebar-product-template';

const RightSideBarExploreTemplate = lazy(() => import('./templates/right-sidebar-explore-template'))
const RightSideBarProductTemplate = lazy(() => import('./templates/right-sidebar-product-template'))

function RightSideBar(props){

    let rightSideBarTemplate;
    if (props.view === "home-page" || window.location.pathname === "/"){
        rightSideBarTemplate = (
            <Suspense fallback={''}>
                <RightSideBarExploreTemplate 
                    isStartPage={true} 
                    showGit={true} 
                    dataRightSideBar={props.dataRightSideBar}
                    isLoading={false}
                />
            </Suspense>
        )
    } else if (props.view === "product-browse" || window.location.pathname.indexOf("/browse/") > -1 ){
        rightSideBarTemplate = (
            <Suspense fallback={''}>
                <RightSideBarExploreTemplate 
                    isStartPage={false} 
                    showGit={false} 
                    dataRightSideBar={props.dataRightSideBar}
                    isLoading={false}
                />
            </Suspense>
        )
    } else if (props.view === "product-view" || window.location.pathname.indexOf('/p/') > -1) {
        rightSideBarTemplate = (
            <Suspense fallback={''}>
                <RightSideBarProductTemplate 
                    user={props.user}
                    maker={props.maker}
                    files={props.files} 
                    dataRightSideBar={props.dataRightSideBar}
                    product={props.product}         
                    isLoading={false}
                    isCollectionView={props.isCollectionView}
                />
            </Suspense>
        )
    }

    return (
        <React.Fragment>
            <div className="pui-sidebar-content">
                {rightSideBarTemplate}
            </div>
            <img src={BlurBG} className="sidebar-bg"/>
        </React.Fragment>
    )
}

export default RightSideBar;