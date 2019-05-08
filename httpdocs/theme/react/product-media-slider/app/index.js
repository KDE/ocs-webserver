import React, { useState } from 'react';
import ReactDOM from 'react-dom';
import StoreContextProvider,{Context} from './context-provider';

function ProductMediaSlider(){

  /* Component */

  const { productMediaSliderState, productMediaSliderDispatch } = React.useContext(Context);
  const [ loading, setLoading ] = useState(true)

  React.useEffect(() => { initProductMediaSlider() },[])

  // init product media slider
  function initProductMediaSlider(){
    productMediaSliderDispatch({type:'SET_PRODUCT',product:window.product});
    productMediaSliderDispatch({type:'SET_PRODUCT_GALLERY',gallery:window.galleryPicturesJson});
    setLoading(false);
  }

  /* Render */

  console.log(productMediaSliderState);

  let mediaSlidesDisplay;
  if (loading === false){
    let productMainSlide = productMediaSliderState.product.embed_code !== null ? productMediaSliderState.product.embed_code : productMediaSliderState.product.image_small;
    const slidesArray = [ productMainSlide, ... productMediaSliderState.gallery ];
    const slidesDisplay = slidesArray.map((s,index) => (
      <MediaSlide 
        key={index}
        slideIndex={index}
        slideUrl={s}
      />
    ));
    mediaSlidesDisplay = <div id="media-slides">{slidesDisplay}</div>
  }

  return (
    <main id="media-slider">
      {mediaSlidesDisplay}
    </main>
  )
}

function MediaSlide(props){
  console.log(props)
  return(
    <div className="slide">
    </div>
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