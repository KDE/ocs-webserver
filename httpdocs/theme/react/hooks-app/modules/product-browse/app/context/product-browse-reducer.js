  let initLoading = true,
      initProductsLoading = false,
      initCategories = null,
      initCatId = null,
      initFilters = null,
      initLoaded = null,
      initLoadLayout = false,
      browseListType = "standard",
      initProducts = null,
      initAuthMember = null,
      initIsBrowseFavorites = false;
    
  if (window.productBrowseData){
    initLoading = false;
    initProductsLoading = false;
    initCategories = window.productBrowseData.categories;
    initCatId = window.productBrowseData.cat_id;
    initFilters = window.productBrowseData.filters;
    initLoaded = true;
    initLoadLayout = false;
    browseListType = window.productBrowseData.browseListType;
    initProducts = window.productBrowseData.products;
    initAuthMember = window.productBrowseData.authMember;
    initIsBrowseFavorites = window.productBrowseData.isBrowseFavorite;
  }
    
  export const productBrowseReducerInitialState = {
    loading:initLoading,
    productsLoading:initProductsLoading,
    categories:initCategories,
    cat_id:initCatId,
    filters:initFilters,
    loaded:initLoaded,
    loadLayout:initLoadLayout,
    browseListType:browseListType,
    products:initProducts,
    sameCatId:false,
    authMember:initAuthMember,
    isBrowseFavorite:initIsBrowseFavorites
  }
  
  function ProductBrowseReducer(state,action){
    switch(action.type){
      case 'SET_DATA':{
        return { ...state, ...action.data,productsLoading:false, loading:false}
      }
      case 'SET_PRODUCTS_LOADING':{
        return { ...state, productsLoading:true}
      }
      case 'SET_PRODUCTS':{
        const data = action.data;
        return { ...state, products:action.data.products, totalcount:data.totalcount, pageLimit:data.pageLimit, tabs:data.tabs, productsLoading:false }
      }
      case 'SET_ORDER_FILTER':{
        let filters = { ...state.filters, order:action.order};
        return {...state, filters:filters}
      }
      case 'SET_PAGE':{
        let page = state.page;
        if (action.page) page = action.page
        return { ...state, page:page}
      }
      case 'RESET_STATE':{

        let comments = null, topproducts = null;
        if (action.sameCatId === true){
          comments = state.comments;
          topproducts = state.topproducts;
        }

        return  {
          loading:true,
          productsLoading:false,
          categories:null,
          cat_id:null,
          filters:null,
          isPlaying:false,
          current:null,
          currentPlayIndex:null,
          loaded:false,
          loadLayout:false,
          pageLimit:10,
          page:action.page,
          totalcount:action.totalcount,
          sameCatId:action.sameCatId,
          authMember:state.authMember,
          comments:comments,
          topproducts:topproducts
        }      
      }
      case 'SET_TOTAL_COUNT':{
        return { ...state, totalcount:action.totalcount}
      }

      case 'RESET_PRODUCTS':{
        return { ...state, products: null}
      }

      case 'SET_LOADING':{
        return {...state, loading:action.loading }
      }
      case 'SET_LOADED':{
        let loading = action.loading ? action.loading : state.loading;
        return { ...state, loaded:action.value, loading:loading}
      }
      case 'LOAD_LAYOUT':{
        return { ...state, loadLayout: action.value,sameCatId:action.sameCatId ? action.sameCatId : state.sameCatId }
      }
      case "SET_CURRENT_ITEM":{
        const s = {
            ... state,
            isPlaying:true,
            current:action.itemId,
            currentPlayIndex:action.pIndex
        }
        return s;
      }
      case "PAUSE":{
        const s = {
            ...state,
            isPlaying:false
        }
        return s;
      }
      default:{
        return state;
      }
    }
  }
  
  export default ProductBrowseReducer;
  