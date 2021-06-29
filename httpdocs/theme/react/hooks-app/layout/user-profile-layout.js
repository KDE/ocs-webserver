import { useContext, useState, useEffect, useRef, Suspense, lazy } from 'react';
import { AppContext } from './context/context-provider'
import { Context } from '../modules/user-profile/context/context-provider';
import { usePrevious } from './app-helpers';
import DummyUserProfile from '../modules/user-profile/dummy-user-profile';
import UserProfileContextProvider from '../modules/user-profile/context/context-provider';
const UserProfile = lazy(() => import('../modules/user-profile/user-profile'));


function UserProfileLayoutComponent(props){

    const { appState, appDispatch } = useContext(AppContext);
    const previousUrl = usePrevious(appState.url);    
    const { userProfileState, userProfileDispatch } = useContext(Context);

    let xhr;

    useEffect(() => {
        userProfileDispatch({type:'RESET_LAYOUT'});
        return () => {
            if (xhr && xhr.abort) xhr.abort();
        }
    },[])

    useEffect(() => {
        if (previousUrl && appState.url && appState.url !== null && previousUrl !== null && appState.url !== previousUrl){
            userProfileDispatch({type:'RESET_LAYOUT'});
        }
    },[appState.url])

    useEffect(() => {
        if (userProfileState.data === null){
            initUserProfileLayout();
        } else {
            appDispatch({type:'SET_VIEW_DATA',viewData:userProfileState,url:appState.url});
            if (window.userProfileData) window.userProfileData = userProfileState.data;
        }
    },[userProfileState])

    function initUserProfileLayout(){
        let readyData = null;
        if (window.userProfileData && window.userProfileData.member.username === appState.id) readyData = window.userProfileData;
        if (appState.viewData && appState.viewData.data &&  appState.viewData.data.member.username === appState.id){
            readyData = appState.viewData.data;
            if (appState.viewData.tabsState){
                userProfileDispatch({type:'SET_TABS',tabsState:appState.viewData.tabsState});
            }
        }
        if (readyData !== null) {
            finishLoadingUserProfile(readyData)
        }
        else {
            xhr = $.ajax({url:'/u2/'+appState.id+'?json=1'}).done(function(res){
                finishLoadingUserProfile(res);
            });
        }
    }

    function finishLoadingUserProfile(data){
        userProfileDispatch({type:'SET_DATA',data:data});
        appDispatch({type:'FINISH_LOADING_VIEW'})
    }

    let userProfileLayoutDisplay = <DummyUserProfile />
    if (userProfileState.loading === false && userProfileState.loaded === true){
        userProfileLayoutDisplay = (
            <Suspense fallback={<DummyUserProfile/>}>
                <UserProfile 
                    {...props}
                />                
            </Suspense>
        )
    }
    
    return (
        <React.Fragment>
            {userProfileLayoutDisplay}
        </React.Fragment>
    )
}

function UserProfileLayout(props){
    return (
        <UserProfileContextProvider>
            <UserProfileLayoutComponent
                {...props}
            />
        </UserProfileContextProvider>
    )
}

export default UserProfileLayout;