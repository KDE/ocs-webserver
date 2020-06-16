import React, { useState, Suspense, lazy } from 'react';
import {isMobile} from 'react-device-detect';
const VideoPlayerWrapper = lazy(() => import('./components/video-player'));
const BookReaderWrapper = lazy(() => import('./components/book-reader'));
const MusicPlayerWrapper = lazy(() => import('./components/music-player'));
const ComicsReaderWrapper = lazy(() => import('./components/comics-reader'));

import {GenerateGalleryArray, CheckForMultipleAudioFiles, GroupAudioFilesInGallery} from './product-media-slider-helpers';

import './../style/product-media-slider.css';

function ProductMediaSlider(){ 

  /* Component */ 

  const [ product, setProduct ] = useState(window.product);
  console.log('product - ');
  console.log(product);
  let galleryArray = GenerateGalleryArray(product);
  console.log('gallery array - ');
  console.log(galleryArray);
  const audioFileIndex = galleryArray.findIndex(gf => gf.type === "audio");
  if (audioFileIndex > -1) galleryArray = GroupAudioFilesInGallery(galleryArray);
  const [ gallery, setGallery ] = useState(galleryArray);
  console.log('gallery - ');
  console.log(gallery);
  const [ disableGallery, setDisableGallery ] = useState(gallery.length > 1 ? false : true)
  const parentContainerElement = document.getElementById('product-title-div');
  const [ containerWidth, setContainerWidth ] = useState(parentContainerElement.offsetWidth);
  const [ currentSlide, setCurrentSlide ] = useState(0);
  const [ sliderHeight, setSliderHeight ] = useState(360);
  const [ cinemaMode, setCinemaMode ] = useState(false);
  const [ isFullScreen, setIsFullScreen] = useState(false)
  const [ showPlaylist, setShowPlaylist ] = useState(false);
  const [ showSliderArrows, setShowSliderArrows ] = useState(isMobile === true ? true : false);  
  const [ sliderFadeControlsMode, setSliderFadeControlsMode ] = useState(true);

  let sliderFadeControlTimeOut;

  // use effects
  React.useEffect(() => { 
    initProductMediaSlider(currentSlide);
    if (gallery[currentSlide].type === "book") setShowSliderArrows(false)
    else setShowSliderArrows(true)
  },[currentSlide])
  React.useEffect((event) => { updateDimensions(event,currentSlide) },[currentSlide, cinemaMode])
  React.useEffect(() => { handleMouseMovementEventListener(showPlaylist,isFullScreen) },[showPlaylist,isFullScreen])

  // init product media slider
  function initProductMediaSlider(currentSlide){
    window.addEventListener("resize", function(event){updateDimensions(event,currentSlide)});
    window.addEventListener("orientationchange",  function(event){updateDimensions(event,currentSlide)});
  }

  // handle mouse movement event listener
  function handleMouseMovementEventListener(showPlaylist,isFullScreen){
    window.removeEventListener("mousemove",function(event){ onMouseMovement(event,showPlaylist,isFullScreen)})
    window.removeEventListener("mousedown",function(event){ onMouseMovement(event,showPlaylist,isFullScreen)})
    window.addEventListener("mousemove",function(event){ onMouseMovement(event,showPlaylist,isFullScreen) });
    window.addEventListener("mousedown",function(event){ onMouseMovement(event,showPlaylist,isFullScreen) });   
  }

  // update dimensions
  function updateDimensions(){
    const newContainerWidth = parentContainerElement.offsetWidth;
    setContainerWidth(newContainerWidth)
    document.getElementById('product-page-content').removeEventListener("DOMNodeRemoved", updateDimensions);
    document.getElementById('product-page-content').removeEventListener("DOMNodeInserted", updateDimensions);
    if (cinemaMode === false) setSliderHeight(360)
  }

  // on mouse movement
  function onMouseMovement(event,showPlaylist,isFullScreen){
    const mediaSliderOffest = $('#media-slider').offset()
    const mediaSliderLeft = mediaSliderOffest.left;
    const mediaSliderRight = mediaSliderLeft + $('#media-slider').width();
    const mediaSliderTop = mediaSliderOffest.top - window.pageYOffset;
    let mediaSliderBottom = mediaSliderTop + $('#media-slider').height();   
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

  // toggle cinema mode
  function toggleCinemaMode(){
    document.getElementById('product-page-content').addEventListener("DOMNodeRemoved", updateDimensions);
    document.getElementById('product-page-content').addEventListener("DOMNodeInserted", updateDimensions);    
    const newCinemaMode = cinemaMode === true ? false : true;
    const targetParentElement = cinemaMode === true ? $('#product-main') : $('#product-page-content');
    const targetChildPrependedElement = cinemaMode === true ? $('#product-title-div') : $('#product-media-slider-container');
    $('#product-main-img-container').prependTo(targetParentElement);
    $(targetChildPrependedElement).prependTo('#product-main-img');
    $("#product-media-slider-container").toggleClass("imgsmall");
    $("#product-media-slider-container").toggleClass("imgfull");
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
    const parentContainerElement = document.getElementById('product-title-div');
    const newContainerWidth = val === true ? window.offsetWidth : parentContainerElement;
    setContainerWidth(newContainerWidth);
  }

  // on finish slides render
  function onFinishedSlidesRender(){
    let swiperHasComics = false;
    const comicsItem = gallery.find((g,index) => g.type === "comics");
    if (comicsItem) swiperHasComics = true
    if (!swiperHasComics){

    }
    
    $(document).ready(function() {
      window.mySwiper = new Swiper('.swiper-container', {
        speed: 400,
        initialSlide: 0,
        observer: true, 
        observeParents: true,
        preloadImages: true,
        updateOnImagesReady: true,
        pagination: '.swiper-pagination',
        paginationClickable: '.swiper-pagination',
        threshold:50,
        onSlideChangeStart: function(swiper){
          setCurrentSlide(swiper.activeIndex);
        }
      });
      window.mySwiper.update()
      // if (isMobile) setShowPlaylist(true)
    });
  }

  // on finished thumbs render
  function onfinishedThumbsRender(){
    $(document).ready(function() {
      let slidesPerView =  Math.ceil(containerWidth / 200);
      if (isMobile) slidesPerView = 2;
      window.galleryThumbs = new Swiper('.gallery-thumbs', {
        slidesPerView:slidesPerView,
        initialSlide: currentSlide,
        spaceBetween:10,
        freeMode: true,
        watchSlidesVisibility: true,
        watchSlidesProgress: true,
        activeIndex:currentSlide,
        scrollbar:'.swiper-scrollbar'
      });
      window.galleryThumbs.update()
    });
  }

  // on thumb item click
  function onThumbItemClick(slideIndex){
    window.mySwiper.slideTo(slideIndex)
  }

  // go next 
  function goNext(){
    let nextSlide = window.mySwiper.activeIndex + 1;
    if (nextSlide > gallery.length - 1) nextSlide = 0;
    window.mySwiper.slideTo(nextSlide)
  }

  // go prev
  function goPrev(){
    let prevSlide =  window.mySwiper.activeIndex  - 1;
    if (prevSlide < 0 ) prevSlide = gallery.length - 1;
    window.mySwiper.slideTo(prevSlide)
  }


  /* Render */

  // media slider css class
  let mediaSliderCssClass = "";
  if (disableGallery === true) mediaSliderCssClass += "disable-gallery ";
  if (cinemaMode === true) mediaSliderCssClass += "cinema-mode ";
  // if (showSliderArrows === false) mediaSliderCssClass += "hide-arrows ";
  if (showPlaylist === false) mediaSliderCssClass += "hide-playlist ";
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
    />
  ));

  let thumbnailNavigationDisplay;
  if (showPlaylist && gallery.length > 1){
  // thumbnail navigation
    const slidesThumbnailNavigationDisplay = gallery.map((g, index) => (
      <ThumbNavigationItem 
        key={index} 
        slideIndex={index}
        currentSlide={currentSlide}
        gallery={gallery}
        item={g}
        onFinishedSlidesRender={onFinishedSlidesRender}
        containerWidth={containerWidth}
        onfinishedThumbsRender={onfinishedThumbsRender}
        onThumbItemClick={(slideIndex) => onThumbItemClick(slideIndex)}
      />
    ))

    let thumbnailNavigationCss;
    if (containerWidth > (gallery.length * 200)){
      thumbnailNavigationCss = {
        paddingLeft: (containerWidth - (gallery.length * 200)) / 2
      }
    }

    thumbnailNavigationDisplay = (
      <div id="slide-navigation" className="swiper-container gallery-thumbs" >
        <div className="thumbnail-navigation swiper-wrapper" style={thumbnailNavigationCss}>{slidesThumbnailNavigationDisplay}</div>
        <div className="swiper-scrollbar"></div>
      </div>
    )
  }

  return (
    <main id="media-slider" 
      style={{height:sliderHeight}} 
      className={mediaSliderCssClass}
      >
      <div id="slider-container" className="swiper-container">
        <div className="swiper-wrapper">
          {slidesDisplay}
        </div>
        <div className="swiper-pagination"></div>
        <a className="carousel-control carousel-control-left left" onClick={goPrev}>
          <span className="visible-container">
            <span className="glyphicon glyphicon-chevron-left"></span>
          </span>
        </a>
        <a className="carousel-control carousel-control-right right" onClick={goNext}>
          <span className="visible-container">
            <span className="glyphicon glyphicon-chevron-right"></span>
          </span>
        </a>
      </div>
      {thumbnailNavigationDisplay}
      <a className="slider-navigation-toggle" onClick={toggleShowPlaylist} style={{top:(sliderHeight) - 75}}></a>
    </main>
  )
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
      <Suspense fallback={<span id="ajax-loder"></span>}>
      <VideoPlayerWrapper 
        height={props.sliderHeight}
        width={props.containerWidth}
        cinemaMode={props.cinemaMode} 
        onCinemaModeClick={onCinemaModeClick}
        slide={props.slide}
        playVideo={props.currentSlide === props.slideIndex}
        onUpdateDimensions={props.onUpdateDimensions}
        onFullScreenToggle={props.onFullScreenToggle}
        onNextSlideClick={props.onNextSlideClick}
      />
      </Suspense>
    )
  }
  else if (props.slide.type === "audio"){

    slideContentDisplay = (
      <Suspense fallback={<span id="ajax-loder"></span>}>
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
      />
      </Suspense>
    )    
  }
  else if (props.slide.type === "book"){
    slideContentDisplay = (
      <Suspense fallback={<span id="ajax-loder"></span>}>
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
    console.log(props.currentSlide);
    slideContentDisplay = (
      <Suspense fallback={<span id="ajax-loder"></span>}>
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