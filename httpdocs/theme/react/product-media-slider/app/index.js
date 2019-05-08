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
  const [ containerWidth, setContainerWidth ] = useState(document.getElementById('product-title-div').width());
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
    const newContainerWidth = document.getElementById("product-main").width();
    setContainerWidth(newContainerWidth)
    setSliderWidth(containerWidth * gallery.length);
  }

  /* Render */
  
  console.log(containerWidth);
  console.log(sliderWidth);

  let slidesDisplay;
  if (loading === false){
    slidesDisplay = gallery.map((s,index) => (
      <SlideItem 
        key={index}
        slideIndex={index}
        slideUrl={s}
      />
    ));
  }

  return (
    <main id="media-slider">
      {slidesDisplay}
    </main>
  )
}

function SlideItem(props){
  console.log(props)
  return(
    <div className="slide-item">
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