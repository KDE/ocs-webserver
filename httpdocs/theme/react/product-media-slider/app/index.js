import React, { useState } from 'react';
import ReactDOM from 'react-dom';
import StoreContextProvider,{Context} from './context-provider';

function ProductMediaSlider(){

  /* Component */

  const { productMediaSliderState, productMediaSliderDispatch } = React.useContext(Context);
  const [ product, setProduct ] = useState(window.product);
  const productMainSlide = product.embed_code !== null ? product.embed_code : product.image_small;
  const galleryArray = [ productMainSlide, ... window.galleryPicturesJson ];
  const [ gallery, setGallery ] = useState(galleryArray);
  const [ containerWidth, setContainerWidth ] = useState(document.getElementById('product-title-div').clientWidth());
  const [ sliderWidth, setSliderWidth ] = useState('');
  const [ currentSlide, setCurrentSlide ] = useState(0)
  const [ loading, setLoading ] = useState(true);

  React.useEffect(() => { 
    window.addEventListener("resize", updateDimensions);
    window.addEventListener("orientationchange", updateDimensions);
    setLoading(false);
  },[])

  // update dimensions
  function updateDimensions(){
    setContainerWidth(document.getElementById("product-main").clientWidth())
    setSliderWidth(containerWidth * gallery.length);
  }

  /* Render */
  
  console.log(containerWidth);
  console.log(sliderWidth);

  let mediaSlidesDisplay;
  if (loading === false){
    
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