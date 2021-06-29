export const ProductViewReducerInitialState = {
    loading:true,
    product:null,
    changelogs:null,
    ratings:null,
    plings:null,
    affiliates:null,
    likes:null,
    comments:null,
    ratingOfUser:null,
    watchComments:false,
    loaded:null,
    isCollectionView:false,
    cinemaMode:false
}
  
function ProductViewReducer(state,action){
    switch(action.type){
        case 'SET_DATA':{
            let isCollectionView = false;
            if (action.data.product.type_id === "3") isCollectionView = true;
            return {
                ... state,
                ... action.data,
                product:{
                    ...action.data.product
                },
                loading:false,
                loaded:true,
                isCollectionView:isCollectionView
            }
        }
        case 'TOGGLE_CINEMA_MODE':{
            return { ...state, cinemaMode: state.cinemaMode === true ? false : true}
        }
        case 'SET_FILES':{
            return { ...state, filesTab:action.files}
        }
        case 'SET_CHANGELOGS':{
            return { ...state, changelogs:action.changelogs }
        }
        case 'SET_RATINGS':{
            return { ...state, ratings:action.ratings }
        }
        case 'SET_PLINGS':{
            return { ...state, plings:action.plings }
        }
        case 'SET_AFFILIATES':{
            return { ...state, affiliates:action.affiliates }
        }
        case 'SET_LIKES':{
            return { ...state, likes:action.likes }
        }
        case 'SET_COMMENTS':{
            
            let commentsPage = 1;
            if (action.page) commentsPage = action.page;
            if (state.watchComments === true){
                const newRatingsofUser = {
                    ... action.comments[0].comment,
                    score:action.comments[0].comment.rating
                }
                
                return { 
                    ...state, 
                    commentsTab:action.comments, 
                    commentsTabPage:commentsPage, 
                    ratingOfUser:newRatingsofUser, 
                    watchComments:false
                }
            } else {
                return { 
                    ...state, 
                    commentsTab:action.comments, 
                    commentsTabPage:commentsPage
                }
            }
        }
        case 'INCREMENT_COMMENT_COUNT':{
            return {
                ...state,
                commentsTabCnt: state.commentsTabCnt + 1
            }
        }
        case 'WATCH_COMMENTS':{
            return { ...state, watchComments:true }
        }
        case 'SET_USER_RATINGS':{
            return { ...state, ratingOfUser:action.userRatings}
        }
        case 'SET_PRODUCT_IS_DEPRECATED':{
            return { ...state, isProductDeprecatedModerator:action.value }
        }
        case 'SET_LOADING':{
            return { ...state, loading:true}
        }
        case 'SET_LOADED':{
            return { ...state, loaded:action.value}
        }
        case 'FINISH_LOADING_PRODUCT':{
            return { ...state, loading:false, loaded:true }
        }
        case 'RESET_STATE':{
            return {
                loading:true,
                product:null,
                changelogs:null,
                ratings:null,
                plings:null,
                affiliates:null,
                likes:null,
                comments:null,
                ratingOfUser:null,
                watchComments:false,
                loaded:false,
            }   
        }
        default:{
            return state;
        }
    }
}
  
export default ProductViewReducer;
  