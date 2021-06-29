
let initLoadedVal = false, 
    initDataVal = null;

    if (window.userProfileData){
    initLoadedVal = true;
    initDataVal = window.userProfileData;
}

// set init reducer
export const userProfileReducerInitialState = {
    loading:false,
    loaded:initLoadedVal,
    tabsState:{
        currentTab:"Products"
    },
    data:initDataVal
}

/** /INIT APP VALUES */

function UserProfileReducer(state, action) {

    switch (action.type) {

        case 'SET_DATA':{

            let tabsState = state.tabsState;
            if (action.tabsState) tabsState = action.tabsState;

            return { ... state, tabsState:tabsState, data:action.data, loading:false, loaded:true }
        }

        case 'SET_TABS':{
            return { ...state , tabsState:action.tabsState }
        }

        case 'SET_TAB':{
            const tabsState = {
                ...state.tabsState,
                currentTab:action.tab
            }
            return { ...state , tabsState:tabsState }
        }

        case 'UPDATE_PRODUCTS_TAB':{
            const data = {
                ...state.data,
                userProducts:action.products
            }
            const tabsState = {
                ...state.tabsState,
                productsTabPage:action.page
            }
            return { ...state, data:data , tabsState: tabsState }
        }

        case 'UPDATE_ORIGINALS_TAB':{
            const data = {
                ...state.data,
                userOriginalProducts:action.products
            }
            const tabsState = {
                ...state.tabsState,
                originalsTabPage:action.page
            }
            return { ...state, data:data , tabsState: tabsState }
        }

        case 'UPDATE_FEATURED_TAB':{
            const data = {
                ...state.data,
                userFeaturedProducts:action.products
            }
            const tabsState = {
                ...state.tabsState,
                featuredTabPage:action.page
            }
            return { ...state, data:data , tabsState: tabsState }
        }

        case 'UPDATE_COLLECTIONS_TAB':{
            const data = {
                ...state.data,
                userCollections:action.products
            }
            const tabsState = {
                ...state.tabsState,
                collectionsTabPage:action.page
            }
            return { ...state, data:data , tabsState: tabsState }
        }

        case 'UPDATE_COMMENTS_TAB':{
            const data = {
                ...state.data,
                comments:action.items
            }
            const tabsState = {
                ...state.tabsState,
                commentsTabPage:action.page
            }
            return { ...state, data:data , tabsState: tabsState }
        }


        case 'UPDATE_RATED_TAB':{
            const data = {
                ...state.data,
                rated:action.items
            }
            const tabsState = {
                ...state.tabsState,
                ratedTabPage:action.page
            }
            return { ...state, data:data , tabsState: tabsState }
        }

        case 'UPDATE_PLINGED_TAB':{
            const data = {
                ...state.data,
                plings:action.items
            }
            const tabsState = {
                ...state.tabsState,
                plingsTabPage:action.page
            }
            return { ...state, data:data , tabsState: tabsState }
        }

        case 'UPDATE_FANS_TAB':{
            const data = {
                ...state.data,
                likes:action.items
            }
            const tabsState = {
                ...state.tabsState,
                likesTabPage:action.page
            }
            return { ...state, data:data , tabsState: tabsState }
        }

        case 'UPDATE_DELETED_TAB':{
            const data = {
                ...state.data,
                userDeletedProducts:action.products
            }
            const tabsState = {
                ...state.tabsState,
                deletedTabPage:action.page
            }
            return { ...state, data:data , tabsState: tabsState }
        }

        case 'UPDATE_UNPUBLISHED_TAB':{
            const data = {
                ...state.data,
                userUnpublishedProducts:action.products
            }
            const tabsState = {
                ...state.tabsState,
                unbpublishedTabPage:action.page
            }
            return { ...state, data:data , tabsState: tabsState }
        }

        case 'UPDATE_DUPLICATES_TAB':{
            const data = {
                ...state.data,
                userDuplicatesProducts:action.products
            }
            const tabsState = {
                ...state.tabsState,
                duplicatesTabPage:action.page
            }
            return { ...state, data:data , tabsState: tabsState }
        }

        case 'RESET_LAYOUT':{
            return {
                data:null,
                loaded:false,
                loading:true,
                tabsState:{
                    currentTab:"Products"
                }
            }
        }

        default: {
            return state;
        }
    }
}

export default UserProfileReducer;
