import React, { useState } from 'react';
import ReactDOM from 'react-dom';
import VideoPlayerWrapper from './video-player';

function ProductMediaSlider(){

  /* Component */

  const [ product, setProduct ] = useState(window.product);
  let galleryArray;
  if (product.embed_code !== null || product.image_small !== null){
    const productMainSlide = product.embed_code !== null ? product.embed_code : product.image_small;
    galleryArray = [ productMainSlide, ... window.galleryPicturesJson ];
  }
  const [ gallery, setGallery ] = useState(galleryArray);
  const parentContainerElement = document.getElementById('product-title-div');
  const [ containerWidth, setContainerWidth ] = useState(parentContainerElement.offsetWidth);
  const [ currentSlide, setCurrentSlide ] = useState(0)
  const [ sliderWidth, setSliderWidth ] = useState(containerWidth * gallery.length);
  const [ sliderHeight, setSliderHeight ] = useState()
  const [ sliderPosition, setSliderPosition ] = useState(containerWidth * currentSlide)
  const [ loading, setLoading ] = useState(true);

  console.log(sliderHeight);

  React.useEffect(() => { initProductMediaSlider() },[])
  React.useEffect(() => { updateDimensions() },[currentSlide])

  // init product media slider
  function initProductMediaSlider(){
    window.addEventListener("resize", updateDimensions);
    window.addEventListener("orientationchange", updateDimensions);
    if (window.filesJson){
      checkForMediaFiles();
    } else {
      setLoading(false);
    }
  }

  function checkForMediaFiles(){
    window.filesJson.forEach(function(f,index){
      console.log(f);
    })
    setLoading(false);
  }

  // update dimensions
  function updateDimensions(){
    const newContainerWidth = parentContainerElement.offsetWidth;
    setContainerWidth(newContainerWidth)
    setSliderWidth(containerWidth * gallery.length);
    setSliderPosition(containerWidth * currentSlide);
    const currentSlideDom = document.getElementById('slide-'+currentSlide);
    if (currentSlideDom){
      const newGalleryHeight = currentSlide.offsetHeight;
      setSliderHeight(newGalleryHeight)
    }
  }

  /* Render */

  // slider container style
  const sliderContainerStyle = {
    width:sliderWidth+'px',
    height:sliderHeight+'px'
  }

  // slider wrapper style
  const sliderWrapperStyle = {
    width:sliderWidth+'px',
    left:'-'+sliderPosition+'px',
    height:sliderHeight+'px'
  }

  // prev / next slide arrow values
  const prevCurrentSlide = currentSlide > 0 ? (currentSlide - 1) : gallery.length + 1;
  const nextCurrentSlide = currentSlide < (gallery.length - 1) ? ( currentSlide + 1 ) : 0;

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
        onSetSlideHeight={(height) => setSliderHeight(height)}
      />
    ));
  }

  return (
    <main id="media-slider">
      <div id="slider-container" style={sliderContainerStyle}>
        <a className="left carousel-control" id="arrow-left" onClick={() => setCurrentSlide(prevCurrentSlide)}>
          <span className="glyphicon glyphicon-chevron-left"></span>
        </a>
        <div id="slider-wrapper" style={sliderWrapperStyle}>
          {slidesDisplay}    
        </div>
        <a className="right carousel-control" id="arrow-right" onClick={() => setCurrentSlide(nextCurrentSlide)}>
          <span className="glyphicon glyphicon-chevron-right"></span>
        </a>      
      </div>
      <SlidesNavigation
        gallery={gallery}
        currentSlide={currentSlide}
        onChangeCurrentSlide={e => setCurrentSlide(e)}
      />
    </main>
  )
}

function SlideItem(props){
  
  const [mediaType, setMediaType ] = useState(props.slideUrl.indexOf('<iframe') > -1 ? "embed" : "image");

  React.useEffect(() => {
    if (props.slideIndex === props.currentSlide) onSetParentSliderHeight()
  },[props.currentSlide])

  function onSetParentSliderHeight(){
    let slideHeight;
    if (mediaType === "embed") slideHeight = props.slideUrl.split('height="')[1].split('"')[0];
    else if (mediaType === "image") slideHeight = document.getElementById('slide-img-'+props.slideIndex).offsetHeight;
    props.onSetSlideHeight(slideHeight);
  }

  let slideContentDisplay;
  if (mediaType === "embed") slideContentDisplay = <div dangerouslySetInnerHTML={{__html: props.slideUrl}} />;
  else if (mediaType === "image") slideContentDisplay = <img id={"slide-img-"+props.currentSlide} src={props.slideUrl}/>
  else console.log('whot');

  const slideItemStyle = { width:props.containerWidth }

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

const rootElement = document.getElementById("product-media-slider-container");
ReactDOM.render(<ProductMediaSlider />, rootElement);