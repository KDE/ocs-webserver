import React, { useEffect, useState } from 'react';
import SupportersModule from '../modules/supporter-module';
import NewsModule from '../modules/news-module';
import ForumModule from '../modules/forum-module';
import GitProjectsModule  from '../modules/git-projects-module';
import CommentsModule from '../modules/comments-module';
import TopProductsModule  from '../modules/top-products-module';

import PlingAppLogo from '../../../../layout/style/media/pling-app-store-icon.png';

export default function RightSideBarExploreTemplate(props){

    const dataRightSideBar = props.dataRightSideBar;
    const [ storeAbout, setStoreAbout ] = useState();    
    const [ catAbout, setCatAbout ] = useState();

    useEffect(() => {
        
        if (dataRightSideBar.catabout){
            fetch('/partials/' + dataRightSideBar.catabout.split('/partials/')[1]).then(res => res.text()).then(res => {
                setCatAbout(res);
            })
        }

        if (dataRightSideBar.storeabout){
            fetch('/partials/' + dataRightSideBar.storeabout.split('/partials/')[1]).then(res => res.text()).then(res => {
                setStoreAbout(res);
            })
        }
    },[])

    function onImageBannerClick(e){
        e.preventDefault();
        props.onChangeUrl("/p/1175480",null,245);
    }

    // supporters, gitlab projects, news
    let supporterModuleDisplay, gitProjectsModuleDisplay, newsModuleDisplay;
    if (props.isStartPage === true){
        newsModuleDisplay = <NewsModule />
        if (!props.isLoading ) supporterModuleDisplay = <SupportersModule onChangeUrl={props.onChangeUrl} supporters={dataRightSideBar.supporters} countSupporters={dataRightSideBar.countSupporters} authMember={dataRightSideBar.authMember} />
        if (props.showGit === true) gitProjectsModuleDisplay = <GitProjectsModule />
    }

    // comments
    let commentsModuleDisplay;
    if (!props.isLoading && dataRightSideBar.comments && dataRightSideBar.comments.length > 0){
        commentsModuleDisplay = (
                <CommentsModule
                    isLoading={props.isLoading}
                    comments={dataRightSideBar.comments}
                    onChangeUrl={props.onChangeUrl}
                />
        )
    }

    // top products
    let topProductsModuleDisplay;
    if (!props.isLoading && dataRightSideBar.topprods && dataRightSideBar.topprods.length > 0) {
        topProductsModuleDisplay = (
            <TopProductsModule 
                topProducts={dataRightSideBar.topprods}
                onChangeUrl={props.onChangeUrl}
            />
        )
    }

    let catAboutdisplay;
    if (catAbout && productBrowseData && productBrowseData.cat_showDescription && productBrowseData.cat_showDescription !== 0){
        catAboutdisplay = (
            <React.Fragment>
                <hr className="hr-dark"/>
                <div className="prod-widget-box" dangerouslySetInnerHTML={{__html:catAbout}}></div>
            </React.Fragment>
        )
    }

    let storeAboutDisplay;
    if (storeAbout){
        storeAboutDisplay = (
            <React.Fragment>
                <hr className="hr-dark"/>
                <div className="prod-widget-box" dangerouslySetInnerHTML={{__html:storeAbout}}></div>
            </React.Fragment>
        )
    }


    return (
        <React.Fragment>
            <div className="pling-app-block">
                <a href="https://www.pling.com/p/1175480/">
                    <div className="app-download" style={{paddingRight:"0px"}}>
                        <figure><img src={PlingAppLogo}/></figure>
                        <p><span className="app-subtitle">Download the App</span> Pling<span className="app-title-thin">Store</span></p>
                    </div>
                </a>
            </div>
            {catAboutdisplay}
            {storeAboutDisplay}
            {supporterModuleDisplay}
            <hr className="hr-dark"/>
            {newsModuleDisplay}
            <ForumModule/>
            {commentsModuleDisplay}
            {topProductsModuleDisplay}
        </React.Fragment>
    )

}