import {
  GetSelectedCategory, 
  GenerateCurrentViewedCategories
} from '../category-helpers';

let selectedCategory;
if (window.categoryId){
  selectedCategory = GetSelectedCategory([...window.catTree],window.categoryId);
}

let initialCurrentViewedCategories = [];
if (selectedCategory){
    initialCurrentViewedCategories = GenerateCurrentViewedCategories([...window.catTree],selectedCategory,[])
    if (selectedCategory.has_children === true) initialCurrentViewedCategories.push(selectedCategory);
    if (initialCurrentViewedCategories.length > 0){
        initialCurrentViewedCategories.forEach(function(icvc,index){
            icvc.level = index + 1;
        })
    }
}

let initialSelectedCategoriesId = [];
initialCurrentViewedCategories.forEach(function(c,index){
    initialSelectedCategoriesId.push(c.id);
});

let initialStoreInfo;
if (window.config.sName){
    json_store_for_tree.forEach(function(d,index){
        if (d.host === window.config.sName){
            initialStoreInfo = d;
        }
    });
}

export const catTreeInitialState = {
  categoryTree: [...window.catTree],
  categoryId: window.categoryId,
  catTreeFilter: window.cat_tree_filter,
  selectedCategory:selectedCategory,
  selectedCategoriesId:initialSelectedCategoriesId,
  currentViewedCategories:initialCurrentViewedCategories,
  currentCategoryLevel:initialCurrentViewedCategories.length,
  searchPhrase:undefined,
  searchMode:undefined,
  showBackButton:true,
  showBreadCrumbs:true,
  showForwardButton:false,
  storeInfo:initialStoreInfo
}

function CatTreeReducer(state,action){
  switch(action.type){
    case 'SET_CURRENT_VIEWED_CATEGORIES':{
      return { ...state, currentViewedCategories:action.val }
    }
    case 'SET_CURRENT_CATEGORY_LEVEL':{
      return { ...state, currentCategoryLevel:action.val }
    }
    case 'SET_SEARCH_MODE':{
      return { ...state, searchMode:action.val }
    }
    case 'SET_SEARCH_PHRASE':{
      return { ...state, searchPhrase:action.val }
    }
    case 'SET_ACTIVE_CATEGORY_LINK_STYLE':{
      return { ...state, activeCategoryStyle:action.val }
    }
    default:{
      return state;
    }
  }
}

export default CatTreeReducer;