import { useState, useContext, Suspense, useEffect, lazy } from 'react';
import { AppContext } from './context/context-provider';
import HomePageView from '../modules/homepage-view/homepage-view';
import DummyHomepageView from '../modules/homepage-view/dummy-homepage-view';
import RightSideBar from '../modules/right-sidebar/app/right-sidebar';
// const HomePageView = lazy(() => import( '../modules/homepage-view/homepage-view'))

function HomePageLayout(props){

    const { appState, appDispatch } = useContext(AppContext);

    let initHomePageData = {}
    if (window.homePageData) initHomePageData = window.homePageData;
    const [ homePageData, setHomePageData ] = useState(initHomePageData);
    const [ loading, setLoading ] = useState(false);

    useEffect(() => {
        initHomePageLaoyut();
    },[])

    function initHomePageLaoyut(){
        if (!homePageData){
            setLoading(true);
            let readyData = null;
            if (appState.viewData && appState.viewData !== null) readyData = appState.viewData;
            if (readyData){
                setHomePageData(readyData);
                finishLoadingHomePage(readyData)
            }
            else {
                $.ajax({url:'/home2/?json=1'}).done(function(res){
                    setHomePageData(res);
                    finishLoadingHomePage(res);
                });
            }
        } else {
            finishLoadingHomePage(homePageData);
        }
    }

    function finishLoadingHomePage(data){
        appDispatch({type:'SET_VIEW_DATA',viewData:data,url:appState.url});
        appDispatch({type:'FINISH_LOADING_VIEW'});
        appDispatch({type:'SET_CATEGORIES',categoryId:0,src:"homepage"})
        setLoading(false);
    }

    function onSetSpotlightProduct(sp){
        const newHomePageData = { ...homePageData, featuredProduct:sp}
        setHomePageData(newHomePageData);
        appDispatch({type:'SET_VIEW_DATA',viewData:newHomePageData,url:appState.url});
    }

    let homePageViewDisplay;
    if (loading === false ){
        homePageViewDisplay = (
            <HomePageView 
                data={homePageData} 
                onChangeUrl={props.onChangeUrl} 
                onSetSpotlightProduct={onSetSpotlightProduct}
            />
        )

    } else homePageViewDisplay = <DummyHomepageView/>

    const rightSideBarData = {
        comments:homePageData.comments,
        supporters:homePageData.supporters,
        countSupporters:homePageData.countSupporters
    }

    return (
        <React.Fragment>
            <div className="pui-main">
                {homePageViewDisplay}
            </div>
            <RightSideBar
                view={"home-page"}
                viewMode={"layout"}
                onChangeUrl={props.onChangeUrl}
                dataRightSideBar={rightSideBarData}
                user={homePageData.authMember}
                isLoading={loading}
            />
        </React.Fragment>
    )
}

export default HomePageLayout;