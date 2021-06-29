import { GetIdFromUrl, GetViewFromUrl } from "../app-helpers";

/** INIT APP VALUES */

    // url / view
    let view = GetViewFromUrl(window.location.pathname);
    let id = GetIdFromUrl(window.location.pathname);
        
    // categories, filters
    let categories = window.catTree, 
        categoryId = window.categoryId, 
        categoryTags = [],
        header,
        initTag;
    if (window.homePageData){
        if (window.homePageData.categories) categories = window.homePageData.categories;
    } else if (window.productBrowseData){
        initTag = {tag_id:window.productBrowseData.filters.tag[0]}
        header = window.productBrowseData.header;
        if (window.productBrowseData.categories){
            if (window.productBrowseData.categories) categories = window.productBrowseData.categories;
            if (window.productBrowseData.cat_id) categoryId = window.productBrowseData.cat_id;
            categoryTags = window.productBrowseData.cat_tags;
        }
    } else if (window.productViewData){
        header = window.productViewData.header;
        if (window.productViewData.categories){
            if (window.productViewData.categories) categories = window.productViewData.categories;
            if (window.productViewData.cat_id) categoryId = window.productViewData.cat_id;
            categoryTags = window.productViewData.cat_tags;
        }
    }

    // history
    let historyEntry = {
        view: view,
        id: id,
        categoryId:categoryId,
        url: window.location.pathname
    }

    let historyIndex = 1;
    let history = [historyEntry]
    window.localStorage.setItem('history', JSON.stringify(history));
    window.localStorage.setItem('currentHistoryIndex', historyIndex);

    // set init reducer
    export const appReducerInitialState = {
        history: history,
        historyIndex:historyIndex,
        header:header,
        viewLoading: false,
        isBack: false,
        url: window.location.pathname,
        view: view,
        id: id,
        categories: categories,
        categoryTags: categoryTags,
        categoryId: categoryId,
        storeConfig: window.productBrowseData ? window.productBrowseData.storeConfig : null,
        filters: null,
        totalcount: null,
        resetLayout: false,
        mediaPlayer: null,
        tag:initTag
    }

/** /INIT APP VALUES */

function AppReducer(state, action) {

    switch (action.type) {

        case 'SET_CATEGORIES': {
            let catId = state.categoryId;
            if (typeof(action.categoryId) === "number") catId = parseInt(action.categoryId);
            return { ...state, 
                categories: action.categories ? action.categories : state.categories, 
                categoryId: catId,
                storeConfig: action.storeConfig ? action.storeConfig : state.storeConfig,
                categoryTags: action.categoryTags
            }
        }

        case 'SET_VIEW': {

            let history = [...state.history];
            let historyIndex = action.historyIndex ? action.historyIndex : state.historyIndex;

            if (action.location && action.location !== null) {

                const historyEntry = {
                    view: action.view ? action.view : state.view,
                    id: action.id,
                    categoryId:action.categoryId,
                    url: action.location.href
                }

                historyIndex += 1;
                if (historyIndex <= 0) historyIndex = 0;
                history = [...history.slice(0,historyIndex - 1), historyEntry]
                window.localStorage.setItem('history', JSON.stringify(history));
                window.localStorage.setItem('currentHistoryIndex', historyIndex);

            } else if (action.isBack === true) {
                const currentHistoryIndex = window.localStorage.getItem('currentHistoryIndex');
                historyIndex = currentHistoryIndex - 1;
                if (historyIndex <= 0) historyIndex = 0;
                window.localStorage.setItem('currentHistoryIndex', historyIndex);

            } else if (action.isForward === true){
                
                const currentHistoryIndex = parseInt(window.localStorage.getItem('currentHistoryIndex'));
                historyIndex = currentHistoryIndex + 1;
                if (historyIndex <= 0) historyIndex = 0;
                window.localStorage.setItem('currentHistoryIndex', historyIndex);

            }

            let url = state.url;
            if (action.url) url = action.url;

            let view = state.view;
            if (action.view) view = action.view;

            let id = state.id;
            if (action.id) id = action.id;

            let categories = state.categories;
            if (action.categories) categories = action.categories;

            let categoryId = state.categoryId;
            if (action.categoryId || action.categoryId === 0) categoryId = parseInt(action.categoryId);

            let filters = state.filters;
            if (action.filters) filters = action.filters;

            return {
                ...state,
                history: history,
                historyIndex:historyIndex,
                url: url,
                view: action.view,
                id: action.id,
                categories: categories,
                categoryId: categoryId,
                isBack: action.isBack,
                isForward: action.isForward,
                viewLoading: action.viewLoading,
                viewData:action.viewData,
                isChangeUrl:false,
                newView:null
            }
        }

        case 'FINISH_LOADING_VIEW': {
            return { ...state, viewLoading: false }
        }

        case 'SET_TOTAL_COUNT': {
            return { ...state, totalcount: action.totalcount }
        }

        case 'EMIT_CHNAGE_URL':{
            return {
                ...state,
                isChangeUrl:true,
                newView:{
                    url:action.url,
                    title:action.title,
                    id:action.id
                }
            }
        }
        
        case 'SET_VIEW_DATA':{
            let newHistoryArray = [];
            state.history.forEach(function(h,index){
                let newHistoryEntry = h;
                if (h.url.indexOf(action.url) > -1){
                    newHistoryEntry = { ...h, viewData:action.viewData }
                }
                newHistoryArray.push(newHistoryEntry)
            });
            window.localStorage.setItem('history', JSON.stringify(newHistoryArray));
            return { ...state, history:newHistoryArray, header:action.viewData.header, categoryTags:action.viewData.categoriesTopTags}
        }

        case 'SET_STORE_CONFIG':{
            return { ...state, storeConfig:action.storeConfig }
        }

        case 'RESET_LAYOUT': {
            return { ...state, resetLayout: action.resetLayout }
        }

        case 'SET_TAG':{
            return { ...state, tag:action.tag}
        }

        /* MEDIA PLAYER */

        case 'SET_MEDIA_PLAYER_DATA': {

            let audioVolume = 0.5;
            if (state.mediaPlayer && state.mediaPlayer.audioVolume) audioVolume = state.mediaPlayer.audioVolume;

            const mediaPlayer = {
                ...action.mediaPlayerData,
                isPlaying: false,
                isPaused: false,
                trackProgress: 0,
                trackDuration: 0,
                trackTime: 0,
                trackTimeSeconds: "00:00",
                audioVolume: audioVolume
            }
            return {
                ...state,
                mediaPlayer: mediaPlayer
            }
        }

        case 'SET_MEDIA_PLAYER': {
            return { ...state, mediaPlayer:action.mediaPlayer }
        }

        case 'SET_IS_PLAYING': {
            const mediaPlayer = {
                ...state.mediaPlayer,
                isPlaying: action.value
            }
            return { ...state, mediaPlayer }
        }

        case 'SET_IS_PAUSED': {
            const mediaPlayer = {
                ...state.mediaPlayer,
                isPaused: action.value
            }
            return { ...state, mediaPlayer }
        }

        case 'SET_TRACK_PROGRESS': {
            const mediaPlayer = {
                ...state.mediaPlayer,
                trackProgress: action.value
            }
            return { ...state, mediaPlayer }
        }

        case 'SET_TRACK_DURATION': {
            const mediaPlayer = {
                ...state.mediaPlayer,
                trackDuration: action.value
            }
            return { ...state, mediaPlayer }
        }

        case 'SET_TRACK_TIME': {
            const mediaPlayer = {
                ...state.mediaPlayer,
                trackTime: action.value
            }
            return { ...state, mediaPlayer }
        }

        case 'SET_TRACK_TIME_SECONDS': {
            const mediaPlayer = {
                ...state.mediaPlayer,
                trackTimeSeconds: action.value
            }
            return { ...state, mediaPlayer }
        }

        case 'SET_IS_MUTED': {
            const mediaPlayer = {
                ...state.mediaPlayer,
                isMuted: action.value
            }
            return { ...state, mediaPlayer }
        }

        case 'SET_VOLUME': {
            const mediaPlayer = {
                ...state.mediaPlayer,
                audioVolume: action.value
            }
            return { ...state, mediaPlayer }
        }

        case 'SET_HAS_INIT':{
            const mediaPlayer = {
                ...state.mediaPlayer,
                hasInit: action.value
            }
            return { ...state, mediaPlayer }            
        }

        /* MEDIA PLAYER */

        default: {
            return state;
        }
    }
}

export default AppReducer;
