import React, { useState } from 'react';
import ReactDOM from 'react-dom';
import {isMobile} from 'react-device-detect';
import VideoPlayerWrapper from './video-player';
import BookReaderWrapper from './book-reader';

function ProductMediaSlider(){ 

  /* Component */

  const [ product, setProduct ] = useState(window.product);

  let galleryArray = []
  if (window.galleryPicturesJson) window.galleryPicturesJson.forEach(function(gp,index){ galleryArray.push({url:gp,type:'image'}); });
  else galleryArray = [{url:product.image_small,type:'image'} ];
  if (product.embed_code !== null && product.embed_code.length > 0) galleryArray = [{url:product.embed_code,type:'embed'}, ... galleryArray ];
  if (window.filesJson) {
    window.filesJson.forEach(function(f,index){
      if (f.type.indexOf('video') > -1 || f.type.indexOf('audio') > -1 || f.type.indexOf('epub') > -1){
        
        let type;
        if (f.type.indexOf('video') > -1 || f.type.indexOf('audio') > -1 ) type = f.type.split('/')[0]
        else if (f.type.indexOf('epub') > -1 ) type = "book";
        
        let url_preview, url_thumb;
        if (f.url_thumb) url_thumb = f.url_thumb.replace(/%2F/g,'/').replace(/%3A/g,':');
        if (f.url_preview) url_preview = f.url_preview.replace(/%2F/g,'/').replace(/%3A/g,':');

          const gItem = {
            url:f.url.replace(/%2F/g,'/').replace(/%3A/g,':'),
            collection_id:f.collection_id,
            type:type,
            file_id:f.id,
            title:f.title,
            url_thumb:url_thumb,
            url_preview:url_preview
          }
          galleryArray = [gItem, ... galleryArray] 
        }
    })
  }

  const [ gallery, setGallery ] = useState(galleryArray);
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
  React.useEffect(() => { initProductMediaSlider(currentSlide) },[currentSlide])
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
      }, 1700);      
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
  }

  // on finish slides render
  function onFinishedSlidesRender(){
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
  if (showSliderArrows === false) mediaSliderCssClass += "hide-arrows ";
  if (showPlaylist === false) mediaSliderCssClass += "hide-playlist ";
  if (sliderFadeControlsMode === true) mediaSliderCssClass += "fade-controls ";
  if (isMobile === true) mediaSliderCssClass += "is-mobile ";

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
      onFinishedSlidesRender={onFinishedSlidesRender}
      onCinemaModeClick={toggleCinemaMode}
      onSetSliderHeight={height => setSliderHeight(height)}
      onUpdateDimensions={updateDimensions}
      onFullScreenToggle={hanleFullScreenToggle}
    />
  ));

  let thumbnailNavigationDisplay;
  if (showPlaylist){
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
    thumbnailNavigationDisplay = (
      <div id="slide-navigation" className="swiper-container gallery-thumbs">
        <div className="thumbnail-navigation swiper-wrapper">{slidesThumbnailNavigationDisplay}</div>
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
          <span className="glyphicon glyphicon-chevron-left"></span>
        </a>
        <a className="carousel-control carousel-control-right right" onClick={goNext}>
          <span className="glyphicon glyphicon-chevron-right"></span>
        </a>
      </div>
      {thumbnailNavigationDisplay}
      <a className="slider-navigation-toggle" onClick={toggleShowPlaylist} style={{top:(sliderHeight) - 65}}></a>
    </main>
  )
}

function SlideItem(props){

  const [ mediaStyle, setMediaStyle ] = useState();

  React.useEffect(() => {
    if (props.gallery && props.gallery.length === props.slideIndex + 1){
      props.onFinishedSlidesRender();
    }
  }, [props.gallery])
  React.useEffect(() => { getSlideContentHeight() },[props.currentSlide, props.cinemaMode]);

  function getSlideContentHeight(){
    
    if (props.slide.type === "image"){
      const imageEl = document.getElementById('slide-img-'+props.slideIndex);
      if (props.currentSlide === props.slideIndex){
        if ( props.cinemaMode === true ){
          let imageHeight = imageEl.naturalHeight;
          if (imageEl.naturalWidth > window.innerWidth){
            let dimensionsPercentage = window.innerWidth / imageEl.naturalWidth;
            imageHeight = imageEl.naturalHeight * dimensionsPercentage;
          }
          setMediaStyle({height:imageHeight})
          props.onSetSliderHeight(imageHeight);
        }
        else if (imageEl.offsetHeight > 0) setMediaStyle({marginTop:(props.sliderHeight - imageEl.offsetHeight) / 2})
      }
    } 
    
    else if (props.slide.type === "embed"){ 
      if (props.currentSlide === props.slideIndex && props.cinemaMode === true) props.onSetSliderHeight(315)
    } 
    
    else if (props.slide.type === "video" || props.slide.type === "audio"){ 
      if (props.currentSlide === props.slideIndex && props.cinemaMode === true) props.onSetSliderHeight(440); 
    }

  }
  
  let slideContentDisplay;
  if (props.slide.type === "embed"){
    slideContentDisplay = <div dangerouslySetInnerHTML={{__html: props.slide.url}} />;
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
      <VideoPlayerWrapper 
        height={props.sliderHeight}
        width={props.containerWidth} 
        onCinemaModeClick={props.onCinemaModeClick}
        slide={props.slide}
        playVideo={props.currentSlide === props.slideIndex}
        onUpdateDimensions={props.onUpdateDimensions}
        onFullScreenToggle={props.onFullScreenToggle}
      />
    )
  }
  else if (props.slide.type === "book"){
    slideContentDisplay = (
      <BookReaderWrapper 
        height={props.sliderHeight}
        width={props.containerWidth}
        onCinemaModeClick={props.onCinemaModeClick}
        slide={props.slide}
        playVideo={props.currentSlide === props.slideIndex}
        onUpdateDimensions={props.onUpdateDimensions}
        onFullScreenToggle={props.onFullScreenToggle}
      />
    )    
  }

  return(
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
    if (window.galleryThumbs){
      window.galleryThumbs.slideTo(props.currentSlide);
    }
  },[props.currentSlide])

  let bgImage;
  if (props.item.type === "image"){
    bgImage = props.item.url.split('/img')[0] + "/cache/120x80-1/img" + props.item.url.split('/img')[1];
  } else if (props.item.type === "video"){
    bgImage = props.item.url_thumb;
  }

  return (
    <div className={props.currentSlide === (props.slideIndex) ? " swiper-slide active " : " swiper-slide " }
      onClick={() => props.onThumbItemClick(props.slideIndex)}
      onTouchEnd={() => props.onThumbItemClick(props.slideIndex)}>
        <div className="preview-image" style={{"backgroundImage":"url("+bgImage+")"}}></div>
    </div>
  )
}

const rootElement = document.getElementById("product-media-slider-container");
ReactDOM.render(<ProductMediaSlider />, rootElement);