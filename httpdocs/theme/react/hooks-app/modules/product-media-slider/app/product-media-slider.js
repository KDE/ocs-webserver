import React, { useState, Suspense, lazy, useEffect, useRef } from 'react';
import {isMobile} from 'react-device-detect';

import Swiper from 'react-id-swiper';

// Import Swiper styles
const VideoPlayerWrapper = lazy(() => import('./components/video-player'));
const BookReaderWrapper = lazy(() => import('./components/book-reader'));
const MusicPlayerWrapper = lazy(() => import('./components/music-player'));
const ComicsReaderWrapper = lazy(() => import('./components/comics-reader'));

import {GenerateGalleryArray, CheckForMultipleAudioFiles, GroupAudioFilesInGallery} from './product-media-slider-helpers';

import './../../../../lib/swiper/swiper.min.css';
import './../style/product-media-slider.css';


function ProductMediaSlider(props){ 

  /* Component */ 
  let initSliderId = "-with-window"
  if (props.product) initSliderId = "-with-props";
  
  const [ product, setProduct ] = useState(props.product);
  let galleryArray = GenerateGalleryArray(props.product,props.filesJson,props.galleryPics);
  const audioFileIndex = galleryArray.findIndex(gf => gf.type === "audio");
  if (audioFileIndex > -1) galleryArray = GroupAudioFilesInGallery(galleryArray);
  const [ gallery, setGallery ] = useState(galleryArray);
  const [ disableGallery, setDisableGallery ] = useState(gallery.length > 1 ? false : true);
  const initContainerWidth = window.innerWidth - 520;
  const [ containerWidth, setContainerWidth ] = useState(initContainerWidth);
  const [ currentSlide, setCurrentSlide ] = useState(0);
  const [ sliderHeight, setSliderHeight ] = useState(360);
  const [ cinemaMode, setCinemaMode ] = useState(false);
  const [ isFullScreen, setIsFullScreen] = useState(false)
  const [ showPlaylist, setShowPlaylist ] = useState(false);
  const [ showSliderArrows, setShowSliderArrows ] = useState(isMobile === true ? true : false);  
  const [ sliderFadeControlsMode, setSliderFadeControlsMode ] = useState(true);
  
  let sliderFadeControlTimeOut;

  const [swiper, setSwiper] = useState(null);
  const swiperRef = useRef(null);

  // use effects

  useEffect(() => {
    let galleryArray = GenerateGalleryArray(props.product,props.filesJson,props.galleryPics);
    const audioFileIndex = galleryArray.findIndex(gf => gf.type === "audio");
    if (audioFileIndex > -1) galleryArray = GroupAudioFilesInGallery(galleryArray);
    setGallery(galleryArray);
    setDisableGallery(galleryArray.length > 1 ? false : true)
    setShowSliderArrows(galleryArray.length > 1 ? true : false)
  },[props.filesJson])

  React.useEffect(() => {
    // const newGalleryArray = GenerateGalleryArray(product,props.filesJson,props.galleryPics);
    // setGallery(newGalleryArray);
    return () => {
      window.removeEventListener("mousemove",onMouseMovement,true)
      window.removeEventListener("mousedown",onMouseMovement,true)
    }
  },[])
  
  React.useEffect(() => { 
    initProductMediaSlider(currentSlide);
    if (gallery[currentSlide] && gallery[currentSlide].type === "book") setShowSliderArrows(false)
    else setShowSliderArrows(true)
  },[currentSlide])
  
  React.useEffect((event) => { updateDimensions(event,currentSlide) },[currentSlide, cinemaMode]) 
  
  React.useEffect(() => { 
    handleMouseMovementEventListener();
  },[showPlaylist,isFullScreen])

  React.useEffect(() => {
    var mySwiper = document.querySelector(".swiper-container").swiper;
    setSwiper(mySwiper);
  }, [swiper]);

  React.useEffect(() => {
    if (swiper){
      swiper.update();
    }
  },[containerWidth])

  // init product media slider
  function initProductMediaSlider(currentSlide){
    window.addEventListener("resize", function(event){updateDimensions(event,currentSlide)});
    window.addEventListener("orientationchange",  function(event){updateDimensions(event,currentSlide)});
  }

  // handle mouse movement event listener
  function handleMouseMovementEventListener(){
    window.addEventListener("mousemove", onMouseMovement,true)
    window.addEventListener("mousedown", onMouseMovement,true) 
  }

  // update dimensions
  function updateDimensions(){
    setContainerWidth(window.innerWidth - 520)
    // document.getElementById('product-page-content').removeEventListener("DOMNodeRemoved", updateDimensions);
    // document.getElementById('product-page-content').removeEventListener("DOMNodeInserted", updateDimensions);
    if (cinemaMode === false) setSliderHeight(360)
  }

  // on mouse movement
  function onMouseMovement(event){
    const mediaSliderElement =  $('#media-slider');
    if (mediaSliderElement){
      const mediaSliderOffest = mediaSliderElement.offset()
      const mediaSliderLeft = mediaSliderOffest.left;
      const mediaSliderRight = mediaSliderLeft + mediaSliderElement.width();
      const mediaSliderTop = mediaSliderOffest.top - window.pageYOffset;
      let mediaSliderBottom = mediaSliderTop + mediaSliderElement.height();   
      if (showPlaylist) mediaSliderBottom += 110;
      else mediaSliderBottom += 30;    
      let mouseIn = false;
      if (event.clientX > mediaSliderLeft && event.clientX < mediaSliderRight && event.clientY > mediaSliderTop && event.clientY < mediaSliderBottom ){ mouseIn = true; }
      if (isFullScreen) mouseIn = true;
      if (mouseIn) {
        setSliderFadeControlsMode(false)
        clearTimeout(sliderFadeControlTimeOut);
        sliderFadeControlTimeOut = setTimeout(function(){
          setSliderFadeControlsMode(true)
        }, 2000);      
      } else {
        setSliderFadeControlsMode(true)
        clearTimeout(sliderFadeControlTimeOut);
      }
    }
  }

  // toggle cinema mode
  function toggleCinemaMode(){
    document.getElementById('product-view-container').addEventListener("DOMNodeRemoved", updateDimensions);
    document.getElementById('product-view-container').addEventListener("DOMNodeInserted", updateDimensions);
    const newCinemaMode = cinemaMode === true ? false : true;
    const targetParentElement = cinemaMode === true ? $('.pui-main') : $('#product-view-container');
    const targetChildPrependedElement = cinemaMode === true ? $('#product-title') : $('#product-media-slider-container');
    $('#product-main-image-container').prependTo(targetParentElement);
    $(targetChildPrependedElement).prependTo('#product-main-img');
    $("#product-media-slider-container").toggleClass("imgsmall");
    $("#product-media-slider-container").toggleClass("imgfull");
    window.scrollTo({
      top: cinemaMode === true ? targetParentElement.position().top : 170,
      left: targetParentElement.position().left,
      behavior: 'smooth'
    });
    setCinemaMode(newCinemaMode)
  }

  // toggle show playlist
  function toggleShowPlaylist(){
    const newShowPlaylistValue = showPlaylist === true ? false : true;
    setShowPlaylist(newShowPlaylistValue)
  }

  //handle full screen toggle
  function hanleFullScreenToggle(val){
    setIsFullScreen(val);
    const newSliderHeight = val === true ? window.innerHeight : 360;
    setSliderHeight(newSliderHeight);
    const newContainerWidth = val === true ? window.offsetWidth : window.innerWidth - 520;
    setContainerWidth(newContainerWidth);
  }

  // on finish slides render
  function onFinishedSlidesRender(){
    if (swiper) swiper.update();
  }

  // go next 
  function goNext(){
    if (swiperRef.current !== null && swiperRef.current.swiper !== null) {
      let nextSlide = swiper.activeIndex + 1;
      if (nextSlide > gallery.length - 1) nextSlide = 0;
      swiper.slideTo(nextSlide)
    }
  }

  // go prev
  function goPrev(){
    if (swiperRef.current !== null && swiperRef.current.swiper !== null) {
      let prevSlide =  swiper.activeIndex  - 1;
      if (prevSlide < 0 ) prevSlide = gallery.length - 1;
      swiper.slideTo(prevSlide)
    }
  }

  /* Render */

  // media slider css class
  let mediaSliderCssClass = "";
  if (disableGallery === true) mediaSliderCssClass += "disable-gallery ";
  if (cinemaMode === true) mediaSliderCssClass += "cinema-mode ";
  // if (showSliderArrows === false) mediaSliderCssClass += "hide-arrows ";
  if (sliderFadeControlsMode === true) mediaSliderCssClass += "fade-controls ";
  if (isMobile === true) mediaSliderCssClass += "is-mobile ";
  if (showSliderArrows === false) mediaSliderCssClass += "hide-controls ";
  if (isFullScreen === true) mediaSliderCssClass += "is-full-screen"

  // slides display
  const slidesDisplay = gallery.map((s,index) => (
    <SlideItem 
      key={index}
      slideIndex={index}
      slide={s}
      currentSlide={currentSlide}
      containerWidth={containerWidth}
      sliderHeight={sliderHeight}
      cinemaMode={cinemaMode}
      gallery={gallery}
      product={product}
      disableGallery={disableGallery}
      onFinishedSlidesRender={onFinishedSlidesRender}
      onCinemaModeClick={toggleCinemaMode}
      onSetSliderHeight={height => setSliderHeight(height)}
      onUpdateDimensions={updateDimensions}
      onFullScreenToggle={hanleFullScreenToggle}
      isFullScreen={isFullScreen}
      onNextSlideClick={goNext}
      onMediaItemUpdate={props.onMediaItemUpdate}
    />
  ));

  const params = {
    speed: 400,
    threshold:50,
    resizeObserver:true,
    useCSS3Transforms:false,
    pagination: {
      el:'.swiper-pagination', 
      clickable:true
    },
    paginationClickable: '.swiper-pagination',
    on:{
      slideChangeTransitionEnd:function() {
        setCurrentSlide(this.activeIndex);
      }
    }
  }

  return (
    <main id="media-slider" 
      style={{height:sliderHeight}} 
      className={mediaSliderCssClass + (showSliderArrows === false ? " hide-playlist" : "")} width={containerWidth}>
      <Swiper containerClass={"swiper-container backface-visibility"} ref={swiperRef} shouldSwiperUpdate={true} {...params}>
        {slidesDisplay}
      </Swiper>
      <div className="swiper-pagination"></div>
      <a className="carousel-control carousel-control-left left" onClick={goPrev}>
        <span className="visible-container">
          <svg xmlns="http://www.w3.org/2000/svg" width="50" height="50" fill="currentColor" className="bi bi-chevron-compact-left" viewBox="0 0 16 16">
            <path fill-rule="evenodd" d="M9.224 1.553a.5.5 0 0 1 .223.67L6.56 8l2.888 5.776a.5.5 0 1 1-.894.448l-3-6a.5.5 0 0 1 0-.448l3-6a.5.5 0 0 1 .67-.223z"/>
          </svg>
        </span>
      </a>
      <a className="carousel-control carousel-control-right right" onClick={goNext}>
          <span className="visible-container">
            <svg xmlns="http://www.w3.org/2000/svg" width="50" height="50" fill="currentColor" className="bi bi-chevron-compact-right" viewBox="0 0 16 16">
              <path fill-rule="evenodd" d="M6.776 1.553a.5.5 0 0 1 .671.223l3 6a.5.5 0 0 1 0 .448l-3 6a.5.5 0 1 1-.894-.448L9.44 8 6.553 2.224a.5.5 0 0 1 .223-.671z"/>
            </svg>
          </span>
        </a>
    </main>
  )

  /*

      <div id="slider-container" className={"swiper-container"+initSliderId}>
        <div className="swiper-wrapper">
          {slidesDisplay}
        </div>

      </div>

  */
}

function SlideItem(props){

  const [ mediaStyle, setMediaStyle ] = useState();
  const [ itemSetHeight, setItemSetHeight ] = useState();

  React.useEffect(() => {
    if (props.gallery && props.gallery.length === props.slideIndex + 1) props.onFinishedSlidesRender();
  }, [props.gallery])
  React.useEffect(() => { getSlideContentHeight(props.cinemaMode) },[props.currentSlide, props.cinemaMode]);
  React.useEffect(() => {
    const newItemSetHeight = props.sliderHeight;
    setItemSetHeight(newItemSetHeight);
  },[props.isFullScreen,props.sliderHeight]);

  function getSlideContentHeight(cinemaMode){
    if (props.currentSlide === props.slideIndex){    
      if (props.isFullScreen === false){
        if (props.slide.type === "image"){
          const imageEl = document.getElementById('slide-img-'+props.slideIndex);
          if ( cinemaMode === true ){
            let imageHeight = imageEl.naturalHeight;
            if (imageEl.naturalWidth > window.innerWidth){
              let dimensionsPercentage = window.innerWidth / imageEl.naturalWidth;
              imageHeight = imageEl.naturalHeight * dimensionsPercentage;
            }
            setMediaStyle({height:imageHeight})
            props.onSetSliderHeight(imageHeight);
          } else {
              if (props.disableGallery) setMediaStyle({maxHeight:360})
          }
        }
        else if (props.slide.type === "embed"){ 
          if (cinemaMode === true) props.onSetSliderHeight(315)
        }
        else if (props.slide.type === "video"){
          if (cinemaMode === true) props.onSetSliderHeight(screen.height * 0.7); 
          else props.onSetSliderHeight(360)
        } else if (props.slide.type === "book" || "comics"){
          props.onSetSliderHeight(360)
        } else if ( props.slide.type === "audio"){
          props.onSetSliderHeight(360)
        }
      }
    }
  }

  function onCinemaModeClick(){
    let cinemaMode = props.cinemaMode === true ? false : true;
    getSlideContentHeight(cinemaMode);
    props.onCinemaModeClick()
  }
  
  let slideContentDisplay;
  
  if (props.slide.type === "embed"){
    slideContentDisplay = (
      <div id="iframe-container">
        <div dangerouslySetInnerHTML={{__html: props.slide.url}} />
      </div>
    )
  }
  else if (props.slide.type === "image") {
    slideContentDisplay = (
      <img 
        onClick={props.onCinemaModeClick} 
        id={"slide-img-"+props.slideIndex} 
        src={props.slide.url}
        style={mediaStyle}
      />
    )
  }
  else if (props.slide.type === "video") {
    slideContentDisplay = (
      <Suspense fallback={''}>
      <VideoPlayerWrapper 
        height={props.sliderHeight}
        width={props.containerWidth}
        cinemaMode={props.cinemaMode} 
        onCinemaModeClick={onCinemaModeClick}
        slide={props.slide}
        playVideo={props.currentSlide === props.slideIndex ? true : false}
        onUpdateDimensions={props.onUpdateDimensions}
        onFullScreenToggle={props.onFullScreenToggle}
        onNextSlideClick={props.onNextSlideClick}
        onMediaItemUpdate={props.onMediaItemUpdate}
      />
      </Suspense>
    )
  }
  else if (props.slide.type === "audio"){

    slideContentDisplay = (
      <Suspense fallback={''}>
      <MusicPlayerWrapper 
        height={props.sliderHeight}
        width={props.containerWidth}
        cinemaMode={props.cinemaMode} 
        onCinemaModeClick={onCinemaModeClick}
        slide={props.slide}
        playAudio={props.currentSlide === props.slideIndex}
        product={props.product}
        onUpdateDimensions={props.onUpdateDimensions}
        onFullScreenToggle={props.onFullScreenToggle}
        onMediaItemUpdate={props.onMediaItemUpdate}
      />
      </Suspense>
    )    
  }
  else if (props.slide.type === "book"){
    slideContentDisplay = (
      <Suspense fallback={''}>
      <BookReaderWrapper 
        height={props.sliderHeight}
        width={props.containerWidth}
        onCinemaModeClick={props.onCinemaModeClick}
        slide={props.slide}
        product={props.product}
        cinemaMode={props.cinemaMode}
        playVideo={props.currentSlide === props.slideIndex}
        onUpdateDimensions={props.onUpdateDimensions}
        onFullScreenToggle={props.onFullScreenToggle}
      />
      </Suspense>            
    )    
  }
  else if (props.slide.type === "comics"){
    slideContentDisplay = (
      <Suspense fallback={''}>
      <ComicsReaderWrapper 
        height={props.sliderHeight}
        width={props.containerWidth}
        onCinemaModeClick={props.onCinemaModeClick}
        slide={props.slide}
        product={props.product}
        slideIndex={props.slideIndex}
        currentSlide={props.currentSlide}
        cinemaMode={props.cinemaMode}
        containerWidth={props.containerWidth}
        sliderHeight={props.sliderHeight}
        playVideo={props.currentSlide === props.slideIndex}
        onUpdateDimensions={props.onUpdateDimensions}
        onFullScreenToggle={props.onFullScreenToggle}
        isFullScreen={props.isFullScreen}
      />
      </Suspense>
    )

  }

  return (
    <div 
      id={"slide-"+props.slideIndex}
      className={props.currentSlide === props.slideIndex ? "active slide-item swiper-slide " + props.slide.type : "slide-item swiper-slide " + props.slide.type } 
      style={ { width:props.containerWidth, height:props.sliderHeight }}>
        {slideContentDisplay}
    </div>
  )
}

function ThumbNavigationItem(props){

  React.useEffect(() => { if (props.gallery && props.gallery.length === props.slideIndex + 1){ props.onfinishedThumbsRender() } }, [props.gallery])
  React.useEffect(() => {
    if (window.galleryThumbs) window.galleryThumbs.slideTo(props.currentSlide);
  },[props.currentSlide])

  let previewImageContainer;
  if (props.item.type === "book"){
    previewImageContainer = (
      <div className="pages preview-image">
        <div className="page">
          {props.item.title}
        </div>
      </div>
    )
  } else {
    let bgImage;
    if (props.item.type === "image"){
      bgImage = props.item.url.split('/img')[0] + "/cache/120x80-1/img" + props.item.url.split('/img')[1];
    } else if (props.item.type === "video"){
      bgImage = props.item.url_thumb;
    }
    previewImageContainer = <div className="preview-image" style={{"backgroundImage":"url("+bgImage+")"}}></div>
  }

  return (
    <div className={props.currentSlide === (props.slideIndex) ? " swiper-slide active " + props.item.type : " swiper-slide " + props.item.type }
      onClick={() => props.onThumbItemClick(props.slideIndex)}
      onTouchEnd={() => props.onThumbItemClick(props.slideIndex)}>
        {previewImageContainer}
    </div>
  )
}

export default ProductMediaSlider;