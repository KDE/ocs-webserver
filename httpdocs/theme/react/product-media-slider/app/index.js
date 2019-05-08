import React, { useState } from 'react';
import ReactDOM from 'react-dom';
import StoreContextProvider,{Context} from './context-provider';

function ProductMediaSlider(){

  const { productMediaSliderState, productMediaSliderDispatch } = React.useContext(Context);
  const [ loading, setLoading ] = useState(true)

  React.useEffect(() => { initProductMediaSlider() },[])

  // init product media slider
  function initProductMediaSlider(){
    productMediaSliderDispatch({type:'SET_PRODUCT',product:window.product});
    productMediaSliderDispatch({type:'SET_PRODUCT_GALLERY',gallery:JSON.parse(window.galleryPicturesJson)});
    setLoading(false);
  }

  let appDisplay;
  if (loading === false) appDisplay = <div>media player</div>

  console.log(productMediaSliderState);

  return (
    <main id="media-player">
      {appDisplay}
    </main>
  )
}

function ProductMediaSliderContainer(){
  return (
    <StoreContextProvider>
      <ProductMediaSlider/>
    </StoreContextProvider>
  );
}

const rootElement = document.getElementById("product-media-slider-container");
ReactDOM.render(<ProductMediaSliderContainer />, rootElement);