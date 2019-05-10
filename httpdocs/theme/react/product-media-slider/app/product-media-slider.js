import React, { useState } from 'react';
import ReactDOM from 'react-dom';
import VideoPlayerWrapper from './video-player';

function ProductMediaSlider(){ 

  /* Component */

  const [ product, setProduct ] = useState(window.product);
  let galleryArray = window.galleryPicturesJson;
  if (product.embed_code !== null && product.embed_code.length > 0) galleryArray = [  product.embed_code, ... window.galleryPicturesJson ];
  else if (!window.galleryPicturesJson) galleryArray = [ product.image_small ]
  const [ gallery, setGallery ] = useState(galleryArray);
  const parentContainerElement = document.getElementById('product-title-div');
  const [ containerWidth, setContainerWidth ] = useState(parentContainerElement.offsetWidth);
  const [ currentSlide, setCurrentSlide ] = useState(0)
  const [ sliderWidth, setSliderWidth ] = useState(containerWidth * gallery.length);
  const [ sliderHeight, setSliderHeight ] = useState(315);
  const [ sliderPosition, setSliderPosition ] = useState(containerWidth * currentSlide);
  const [ cinemaMode, setCinemaMode ] = useState(false);
  const [ loading, setLoading ] = useState(true);

  React.useEffect(() => { initProductMediaSlider() },[])
  React.useEffect(() => { updateDimensions() },[currentSlide])

  // init product media slider
  function initProductMediaSlider(){
    window.addEventListener("resize", updateDimensions);
    window.addEventListener("orientationchange", updateDimensions);
    if (window.filesJson) checkForMediaFiles();
    else setLoading(false);
  }

  // check for media files
  function checkForMediaFiles(){
    let newGallery = gallery;
    window.filesJson.forEach(function(f,index){ if (f.type.indexOf('video') > -1 || f.type.indexOf('audio') > -1) newGallery = [ f.url, ... newGallery] })
    setGallery(newGallery);
    setLoading(false);
  }

  // update dimensions
  function updateDimensions(){
    const newContainerWidth = parentContainerElement.offsetWidth;
    setContainerWidth(newContainerWidth)
    setSliderWidth(containerWidth * gallery.length);
    setSliderPosition(containerWidth * currentSlide);
  }

  function toggleCinemaMode(){
    console.log(cinemaMode);
    const newCinemaMode = cinemaMode === true ? false : true;
    const targetParentElement = cinemaMode === true ? $('#product-main') : $('#product-page-content');
    $('#product-main-img-container').prependTo(targetParentElement);
    $("#product-media-slider-container").toggleClass("imgsmall");
    $("#product-media-slider-container").toggleClass("imgfull");
    setCinemaMode(newCinemaMode);
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
  const prevCurrentSlide = currentSlide > 0 ? currentSlide - 1 : gallery.length - 1;
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
        onSetSlideHeight={height => setSliderHeight(height)}
        onSlideItemClick={toggleCinemaMode}
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
  
  const [mediaType, setMediaType ] = useState('');

  React.useEffect(() => { determineMediaType() },[])
  React.useEffect(() => { if (props.slideIndex === props.currentSlide) onSetParentSliderHeight() },[])
  React.useEffect(() => { if (props.slideIndex === props.currentSlide) onSetParentSliderHeight() },[props.currentSlide])

  function determineMediaType(){
    let initialMediaType;
    if (props.slideUrl.indexOf('<iframe') > -1) initialMediaType = "embed";
    else if (props.slideUrl.indexOf('.png') > -1 || props.slideUrl.indexOf('.jpg') > -1 || props.slideUrl.indexOf('.jpeg') > -1) initialMediaType = "image";
    else if (props.slideUrl.indexOf('.mp4') > -1) initialMediaType = "video";
    setMediaType(initialMediaType);
  }

  function onSetParentSliderHeight(){
    let slideHeight;
    if (mediaType === "embed") slideHeight = 315;
    else if (mediaType === "image") slideHeight = document.getElementById('slide-img-'+props.slideIndex).offsetHeight;
    else if (mediaType === "video") slideHeight = 360;
    props.onSetSlideHeight(slideHeight);
  }

  function appendToBody(){
    const productMediaSliderContainerDom = document.getElementById('product-media-slider-container');
    document.getElementById('product-page-content').prepend(productMediaSliderContainerDom);
  }

  let slideContentDisplay;
  if (mediaType === "embed") slideContentDisplay = <div dangerouslySetInnerHTML={{__html: props.slideUrl}} />;
  else if (mediaType === "image") slideContentDisplay = <img id={"slide-img-"+props.slideIndex} src={props.slideUrl}/>
  else if (mediaType === "video") slideContentDisplay = <VideoPlayerWrapper width={(props.containerWidth * 0.7)} source={props.slideUrl}/>
  const slideItemStyle = { width:props.containerWidth }


  return(
    <div onClick={props.onSlideItemClick} className={props.currentSlide === props.slideIndex ? "active slide-item" : "slide-item" } id={"slide-"+props.slideIndex} style={slideItemStyle}>
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