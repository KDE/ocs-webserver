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
  const parentContainerElement = document.getElementById('product-title-div');
  const [ containerWidth, setContainerWidth ] = useState(parentContainerElement.offsetWidth);
  const [ sliderWidth, setSliderWidth ] = useState('');
  const [ currentSlide, setCurrentSlide ] = useState(0)
  const [ sliderPosition, setSliderPosition ] = useState(containerWidth * currentSlide)
  const [ loading, setLoading ] = useState(true);

  console.log('*********')
  console.log(product);
  console.log(gallery);
  console.log(parentContainerElement);
  console.log(containerWidth);
  console.log(sliderWidth);
  console.log(currentSlide);
  console.log(sliderPosition);
  console.log(loading);
  console.log('*********')
  
  React.useEffect(() => { 
    window.addEventListener("resize", updateDimensions);
    window.addEventListener("orientationchange", updateDimensions);
    setLoading(false);
  },[])

  // update dimensions
  function updateDimensions(){
    const newContainerWidth = parentContainerElement.offsetWidth;
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
  const [ mediaType, setMediaType ] = useState(props.slideUrl.indexOf('<iframe') > -1 ? "embed" : "image");
  let slideContentDisplay;
  if (mediaType === "embed"){
    slideContentDisplay = <div dangerouslySetInnerHTML __html={{props.slideUrl}}></div>
  } else if (mediaType === "image"){
    slideContentDisplay = <img src={props.slideUrl}/>
  } else { 
    console.log('whot');
  }
  return(
    <div className="slide-item">
      {slideContentDisplay}
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