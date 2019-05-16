import React, { useState } from 'react';
import ReactDOM from 'react-dom';
import VideoPlayerWrapper from './video-player';
import { Scrollbars } from 'react-custom-scrollbars';


function ProductMediaSlider(){ 

  /* Component */

  const [ product, setProduct ] = useState(window.product);

  let galleryArray = []
  if (window.galleryPicturesJson) window.galleryPicturesJson.forEach(function(gp,index){ galleryArray.push({url:gp,type:'image'}); });
  else galleryArray = [{url:product.image_small,type:'image'} ];
  if (product.embed_code !== null && product.embed_code.length > 0) galleryArray = [{url:product.embed_code,type:'embed'}, ... galleryArray ];
  if (window.filesJson) {
    window.filesJson.forEach(function(f,index){
      function splitByLastDot(string){
        const array = string.split(/\.(?=[^\.]+$)/);
        return array[array.length - 1]
      }
      if (f.type.indexOf('video') > -1 || f.type.indexOf('audio') > -1){
        if ( splitByLastDot(f.name) !== '3gp' && splitByLastDot(f.name) !== '3g2' && splitByLastDot(f.name) !== 'm2v' 
          && splitByLastDot(f.name) !== 'mov' && splitByLastDot(f.name) !== 'flv' && splitByLastDot(f.name) !== 'wmv' ){
          const gItem = {
            url:f.url,
            type:f.type.split('/')[0],
            collection_id:f.collection_id,
            file_id:f.id
          }
          galleryArray = [gItem, ... galleryArray] 
        }
      }
    })
  }

  const [ gallery, setGallery ] = useState(galleryArray);
  const [ disableGallery, setDisableGallery ] = useState(gallery.length > 1 ? false : true)
  const parentContainerElement = document.getElementById('product-title-div');
  const [ containerWidth, setContainerWidth ] = useState(parentContainerElement.offsetWidth);
  const [ currentSlide, setCurrentSlide ] = useState(0)
  const [ sliderWidth, setSliderWidth ] = useState(containerWidth * gallery.length);
  const [ sliderHeight, setSliderHeight ] = useState(360);
  const [ sliderPosition, setSliderPosition ] = useState(containerWidth * currentSlide);
  const [ cinemaMode, setCinemaMode ] = useState(false);
  const [ showPlaylist, setShowPlaylist ] = useState(false);
  const [ showSliderArrows, setShowSliderArrows ] = useState(true);  
  const [ sliderFadeControlsMode, setSliderFadeControlsMode ] = useState(false);

  let sliderFadeControlTimeOut;

  React.useEffect(() => { initProductMediaSlider(currentSlide) },[currentSlide])
  React.useEffect((event) => { updateDimensions(event,currentSlide) },[currentSlide, cinemaMode])
  React.useEffect(() => { handleMouseMovementEventListener(showPlaylist) },[showPlaylist])

  // init product media slider
  function initProductMediaSlider(currentSlide){
    window.addEventListener("resize", function(event){updateDimensions(event,currentSlide)});
    window.addEventListener("orientationchange",  function(event){updateDimensions(event,currentSlide)});
  }

  // handle mouse movement event listener
  function handleMouseMovementEventListener(showPlaylist){
    window.removeEventListener("mousemove",function(event){ onMouseMovement(event,showPlaylist)})
    window.removeEventListener("mousedown",function(event){ onMouseMovement(event,showPlaylist)})
    window.addEventListener("mousemove",function(event){ onMouseMovement(event,showPlaylist) });
    window.addEventListener("mousedown",function(event){ onMouseMovement(event,showPlaylist) });   
  }

  // update dimensions
  function updateDimensions(event,currentSlide){
    const newContainerWidth = parentContainerElement.offsetWidth;
    setContainerWidth(newContainerWidth)
    setSliderWidth(newContainerWidth * gallery.length);
    setSliderPosition(newContainerWidth * currentSlide);
    document.getElementById('product-page-content').removeEventListener("DOMNodeRemoved", updateDimensions);
    document.getElementById('product-page-content').removeEventListener("DOMNodeInserted", updateDimensions);
    if (cinemaMode === false) setSliderHeight(360)
  }

  // on mouse movement
  function onMouseMovement(event,showPlaylist){
    
    const mediaSliderOffest = $('#media-slider').offset()
    const mediaSliderLeft = mediaSliderOffest.left;
    const mediaSliderRight = mediaSliderLeft + $('#media-slider').width();
    const mediaSliderTop = mediaSliderOffest.top - window.pageYOffset;
    let mediaSliderBottom = mediaSliderTop + $('#media-slider').height();   
    if (showPlaylist) mediaSliderBottom += 110;
    else mediaSliderBottom += 30;
    
    let mouseIn = false;
    if (event.clientX > mediaSliderLeft && event.clientX < mediaSliderRight && event.clientY > mediaSliderTop && event.clientY < mediaSliderBottom ){ mouseIn = true; }
    
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
    
    setCinemaMode(newCinemaMode);

  }

  // toggle show playlist
  function toggleShowPlaylist(){
    const newShowPlaylistValue = showPlaylist === true ? false : true;
    setShowPlaylist(newShowPlaylistValue)
  }

  /* Render */

  // media slider css class
  let mediaSliderCssClass = "";
  if (disableGallery === true) mediaSliderCssClass += "disable-gallery ";
  if (cinemaMode === true) mediaSliderCssClass += "cinema-mode ";
  if (showSliderArrows === false) mediaSliderCssClass += "hide-arrows ";
  if (showPlaylist === false) mediaSliderCssClass += "hide-playlist ";
  if (sliderFadeControlsMode === true) mediaSliderCssClass += "fade-controls "

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

  // slider arrows css
  const sliderArrowCss = { top:((sliderHeight / 2 ) - 40)+'px' }

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
      onCinemaModeClick={toggleCinemaMode}
      onSetSliderHeight={height => setSliderHeight(height)}
      onUpdateDimensions={updateDimensions}
    />
  ));

  return (
    <main id="media-slider" 
      style={{height:sliderHeight}} 
      className={mediaSliderCssClass}
      >

      <div id="slider-container" style={sliderContainerStyle}>
        <a className="left carousel-control" id="arrow-left" style={sliderArrowCss} onClick={() => setCurrentSlide(prevCurrentSlide)}>
          <span className="glyphicon glyphicon-chevron-left"></span>
        </a>
        <div id="slider-wrapper" style={sliderWrapperStyle}>
          {slidesDisplay}
        </div>
        <a className="right carousel-control" id="arrow-right" style={sliderArrowCss} onClick={() => setCurrentSlide(nextCurrentSlide)}>
          <span className="glyphicon glyphicon-chevron-right"></span>
        </a>      
      </div>
      <a className="slider-navigation-toggle" onClick={toggleShowPlaylist} style={{top:(sliderHeight) - 35}}></a>
      <SlidesNavigation
        gallery={gallery}
        currentSlide={currentSlide}
        containerWidth={containerWidth}
        showPlaylist={showPlaylist}
        onChangeCurrentSlide={e => setCurrentSlide(e)}
      />
    </main>
  )
}

function SlideItem(props){

  const [ mediaStyle, setMediaStyle ] = useState();

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
      if (props.currentSlide === props.slideIndex && props.cinemaMode === true) props.onSetSliderHeight(360); 
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
        width={(props.containerWidth - 100)} 
        onCinemaModeClick={props.onCinemaModeClick}
        slide={props.slide}
        playVideo={props.currentSlide === props.slideIndex}
        onUpdateDimensions={props.onUpdateDimensions}
      />
    )
  }

  return(
    <div 
      id={"slide-"+props.slideIndex}     
      className={props.currentSlide === props.slideIndex ? "active slide-item " + props.slide.type : "slide-item " + props.slide.type } 
      style={ { width:props.containerWidth, height:props.sliderHeight }}>
        {slideContentDisplay}
    </div>
  )
}

function SlidesNavigation(props){

  /* COMPONENT */

  const thumbElementWidth = 140;
  const [ thumbSliderWidth, setThumbSliderWidth ] = useState((thumbElementWidth * props.gallery.length) +10);
  let thumbSliderPosition = 0;
  const currentThumbPosition = (props.currentSlide * thumbElementWidth) + thumbElementWidth;
  if (currentThumbPosition > props.containerWidth) thumbSliderPosition = (currentThumbPosition - props.containerWidth) + 10;

  React.useEffect(() => { if (props.showPlaylist) scrollSlider() },[])
  React.useEffect(() => { if (props.showPlaylist) scrollSlider() },[props.currentSlide])

  function scrollSlider(){
    $('#slide-navigation').children().attr('id','slider-scroll-wrapper');
    $('#slider-scroll-wrapper').children().attr('id','slider-scroll');
    $('#slider-scroll').scrollLeft(thumbSliderPosition)
  }

  /* RENDER */

  let navigationSliderDisplay;
  if (props.showPlaylist){
    const slidesThumbnailNavigationDisplay = props.gallery.map((g, index) => {
      let image;
      if (g.type === "image") image = <img src={g.url.split('/img')[0] + "/cache/120x80-1/img" + g.url.split('/img')[1]}/>
      else if (g.type === "video") image = <span className="glyphicon glyphicon-play"></span>
      return (
        <li key={index}  className={ props.currentSlide === index ? "active " + g.type : g.type}>
          <a onClick={e => props.onChangeCurrentSlide(index)}>{image}</a>
        </li>
      )
    })

    let thumbSliderStyle = {  width:thumbSliderWidth+'px' }
    if (thumbSliderWidth > props.containerWidth){
      thumbSliderStyle.position = 'absolute';
      thumbSliderStyle.top = '0';
    }

    navigationSliderDisplay = (
      <Scrollbars style={{ width: props.containerWidth, height: 110 }}>
        <ul className="thumbnail-navigation" style={thumbSliderStyle}>{slidesThumbnailNavigationDisplay}</ul>
    </Scrollbars>
    )
  } else {
    const slidesThumbnailNavigationDisplay = props.gallery.map((g, index) => (
        <li key={index}  className={ props.currentSlide === index ? "active " : ""}>
          <a onClick={e => props.onChangeCurrentSlide(index)}></a>
        </li>
    ))
    navigationSliderDisplay = (
      <ul>{slidesThumbnailNavigationDisplay}</ul>
    )
  }

  return (
    <div id="slide-navigation">
      {navigationSliderDisplay}
    </div>
  )
}

const rootElement = document.getElementById("product-media-slider-container");
ReactDOM.render(<ProductMediaSlider />, rootElement);