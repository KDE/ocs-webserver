import React, { useEffect, useState } from 'react';
import ReactDOM from 'react-dom';
import HomePageView from './homepage-view';
import AppImageHubHomePage from './appimagehub-homepage-view';
import CategoryTreeWrapper from '../category-tree-static/app/category-tree';
import RightSideBar from '../right-sidebar/app/right-sidebar';

function HomePageContainer(){
    let mainContainerClass = "main";
    let homePageLayout = (
        <React.Fragment>
            <div className="pui-sidebar">
                <CategoryTreeWrapper/>                
            </div>
            <div className="pui-main">
                <HomePageView
                    data={homePageData}
                />
            </div>
            <div className="pui-sidebar-right">
                <RightSideBar 
                    dataRightSideBar={{
                        comments:homePageData.comments,
                        supporters:homePageData.supporters,
                        countSupporters:homePageData.countSupporters,
                        authMember:homePageData.authMember
                    }}
                />
            </div>
        </React.Fragment>
    )

    if (homePageData.storeConfigIdName === "appimagehub"){
        mainContainerClass = "";
        homePageLayout = <AppImageHubHomePage data={homePageData}/>
    }

    return (
        <div className={mainContainerClass}>
            {homePageLayout}
        </div>
    )
}

const rootElement = document.getElementById("homepage-container");
ReactDOM.render(<HomePageContainer />, rootElement);