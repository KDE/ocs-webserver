export const ProductMediaSliderReducerInitialState = {
    loading:true,
    product:null,
    gallery:null
  }
  
  function ProductMediaSlider(state,action){
    switch(action.type){
      case 'SET_PRODUCT':{
        return {... state, product:action.product}
      }
      case 'SET_PRODUCT_GALLERY':{
        return {... state, gallery:action.gallery}
      }
      default:{
        return state;
      }
    }
  }
  
  export default ProductMediaSlider;
  