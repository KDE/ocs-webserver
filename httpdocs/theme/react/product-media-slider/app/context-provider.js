import React from 'react';
import ProductMediaSlider,{ProductMediaSliderReducerInitialState} from './reducers/product-media-slider-reducer.js';

export const Context = React.createContext();
const Provider = Context.Provider;

const StoreContextProvider = (props) => {
  const [productMediaSliderState, productMediaSliderDispatch] = React.useReducer(ProductMediaSlider,ProductMediaSliderReducerInitialState);

  return(
    <Provider {...props} value={{
      productMediaSliderState,productMediaSliderDispatch
    }}/>
  )
}

export default StoreContextProvider;
