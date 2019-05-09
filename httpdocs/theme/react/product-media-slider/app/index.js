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
  const [ currentSlide, setCurrentSlide ] = useState(0)
  const [ sliderWidth, setSliderWidth ] = useState(containerWidth * gallery.length);
  const [ sliderHeight, setSliderHeight ] = useState()
  const [ sliderPosition, setSliderPosition ] = useState(containerWidth * currentSlide)
  const [ loading, setLoading ] = useState(true);

  console.log('*********')
  console.log(containerWidth);
  console.log(sliderWidth);
  console.log(currentSlide);
  console.log(sliderPosition);
  console.log('*********')
  
  React.useEffect(() => { 
    window.addEventListener("resize", updateDimensions);
    window.addEventListener("orientationchange", updateDimensions);
    setLoading(false);
  },[])

  React.useEffect(() => { updateDimensions() },[currentSlide])

  // update dimensions
  function updateDimensions(){
    const newContainerWidth = parentContainerElement.offsetWidth;
    setContainerWidth(newContainerWidth)
    setSliderWidth(containerWidth * gallery.length);
    setSliderPosition(containerWidth * currentSlide);
    const newGalleryHeight =  document.getElementById('slide-'+currentSlide).offsetHeight;
    setSliderHeight(newGalleryHeight)
  }

  /* Render */

  // slider wrapper style
  const sliderWrapperStyle = {
    width:sliderWidth+'px',
    left:'-'+sliderPosition+'px',
    height:sliderHeight+'px'
  }

  // prev / next slide arrow values
  const prevCurrentSlide = currentSlide > 1 ? (currentSlide - 1) : gallery.length;
  const nextCurrentSlide = currentSlide < gallery.length ? ( currentSlide + 1 ) : 0;

  // slides display
  let slidesDisplay;
  if (loading === false){
    slidesDisplay = gallery.map((s,index) => (
      <SlideItem 
        key={index}
        slideIndex={index}
        slideUrl={s}
        currentSlide={currentSlide}
        containerWidth={containerWidth}
      />
    ));
  }

  return (
    <main id="media-slider">
      <a className="left carousel-control" id="arrow-left" onClick={() => setCurrentSlide(prevCurrentSlide)}>
        <span className="glyphicon glyphicon-chevron-left"></span>
      </a>
      <div id="slider-wrapper" style={sliderWrapperStyle}>
        {slidesDisplay}    
      </div>
      <a className="right carousel-control" id="arrow-right" onClick={() => setCurrentSlide(nextCurrentSlide)}>
        <span className="glyphicon glyphicon-chevron-right"></span>
      </a>
      <SlidesNavigation
        gallery={gallery}
        currentSlide={currentSlide}
        onChangeCurrentSlide={e => setCurrentSlide(e)}
      />
    </main>
  )
}

function SlideItem(props){
  
  const mediaType = props.slideUrl.indexOf('<iframe') > -1 ? "embed" : "image";
  
  let slideContentDisplay;
  if (mediaType === "embed") slideContentDisplay = <div dangerouslySetInnerHTML={{__html: props.slideUrl}} />;
  else if (mediaType === "image") slideContentDisplay = <img src={props.slideUrl}/>
  else console.log('whot');

  const slideItemStyle = { width:props.containerWidth }
  console.log(slideItemStyle);
  console.log(props);
  
  return(
    <div className="slide-item" id={"slide-"+props.slideIndex} style={slideItemStyle}>
      {slideContentDisplay}
    </div>
  )
}

function SlidesNavigation(props){

  const slidesNavigationDisplay = props.gallery.map((g,index) => (
    <li key={index} className={ props.currentSlide === index ? "active" : ""}>
      <a onClick={e => props.onChangeCurrentSlide(index)}></a>
    </li>
  ))

  return (
    <div id="slide-navigation">
      <ul>
        {slidesNavigationDisplay}
      </ul>
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