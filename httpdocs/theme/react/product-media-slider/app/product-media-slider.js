import React, { useState } from 'react';
import ReactDOM from 'react-dom';
import VideoPlayerWrapper from './video-player';

function ProductMediaSlider(){ 

  /* Component */

  const [ product, setProduct ] = useState(window.product);

  let galleryArray = []
  if (window.galleryPicturesJson) window.galleryPicturesJson.forEach(function(gp,index){ galleryArray.push({url:gp,type:'image'}); });
  else galleryArray = [{url:product.image_small,type:'image'} ];
  if (product.embed_code !== null && product.embed_code.length > 0) galleryArray = [{url:product.embed_code,type:'embed'}, ... galleryArray ];
  if (window.filesJson) {
    window.filesJson.forEach(function(f,index){  
      if (f.type.indexOf('video') > -1 || f.type.indexOf('audio') > -1) galleryArray = [ {url:f.url,type:f.type.split('/')[0]}, ... galleryArray] 
    })
  }

  const [ gallery, setGallery ] = useState(galleryArray);
  const parentContainerElement = document.getElementById('product-title-div');
  const [ containerWidth, setContainerWidth ] = useState(parentContainerElement.offsetWidth);
  const [ currentSlide, setCurrentSlide ] = useState(0)
  const [ sliderWidth, setSliderWidth ] = useState(containerWidth * gallery.length);
  const [ sliderHeight, setSliderHeight ] = useState(360);
  const [ sliderPosition, setSliderPosition ] = useState(containerWidth * currentSlide);
  const [ cinemaMode, setCinemaMode ] = useState(false);
  const [ loading, setLoading ] = useState(true);

  console.log(containerWidth);
  console.log(sliderWidth);
  console.log('-'+sliderPosition);
  console.log(sliderHeight);

  React.useEffect(() => { initProductMediaSlider() },[])
  React.useEffect(() => { updateDimensions() },[currentSlide, cinemaMode])

  // init product media slider
  function initProductMediaSlider(){
    window.addEventListener("resize", updateDimensions);
    window.addEventListener("orientationchange", updateDimensions);
    setLoading(false);
  }

  // update dimensions
  function updateDimensions(){
    const newContainerWidth = parentContainerElement.offsetWidth;
    setContainerWidth(newContainerWidth)
    setSliderWidth(containerWidth * gallery.length);
    setSliderPosition(containerWidth * currentSlide);
    setLoading(false);
  }

  // toggle cinema mode
  function toggleCinemaMode(){
    setLoading(true);
    const newCinemaMode = cinemaMode === true ? false : true;
    const targetParentElement = cinemaMode === true ? $('#product-main') : $('#product-page-content');
    const targetChildPrependedElement = cinemaMode === true ? $('#product-title-div') : $('#product-media-slider-container');
    $('#product-main-img-container').prependTo(targetParentElement);
    $(targetChildPrependedElement).prependTo('#product-main-img-container');
    $("#product-media-slider-container").toggleClass("imgsmall");
    $("#product-media-slider-container").toggleClass("imgfull");
    setCinemaMode(newCinemaMode);
  }

  /* Render */

  let productMediaSliderDisplay;
  if (!loading){
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
    const slidesDisplay = gallery.map((s,index) => (
      <SlideItem 
        key={index}
        slideIndex={index}
        slide={s}
        currentSlide={currentSlide}
        containerWidth={containerWidth}
        cinemaMode={cinemaMode}
        onSlideItemClick={toggleCinemaMode}
      />
    ));
    productMediaSliderDisplay = (
      <div>
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
      </div>
    )
  }
  return (
    <main id="media-slider">
      {productMediaSliderDisplay}
    </main>
  )
}

function SlideItem(props){
  let slideContentDisplay, fullScreenModeButtonDisplay;
  if (props.slide.type === "embed") slideContentDisplay = <div dangerouslySetInnerHTML={{__html: props.slide.url}} />;
  else if (props.slide.type === "image") {
    slideContentDisplay = <img onClick={props.onSlideItemClick} id={"slide-img-"+props.slideIndex} src={props.slide.url}/>
    fullScreenModeButtonDisplay = <a className="full-screen">toggle full screen</a>
  }
  else if (props.slide.type === "video") slideContentDisplay = <VideoPlayerWrapper width={(props.containerWidth * 0.7)} source={props.slide.url} onCinemaModeClick={props.onSlideItemClick}/>
  const slideItemStyle = { width:props.containerWidth }
  return(
    <div className={props.currentSlide === props.slideIndex ? "active slide-item" : "slide-item" } id={"slide-"+props.slideIndex} style={slideItemStyle}>
      {slideContentDisplay}
      {fullScreenModeButtonDisplay}
    </div>
  )
}

function SlidesNavigation(props){

  const [ thumbSliderWidth, setThumbSliderWidth ] = useState(140 * props.gallery.length);

  const slidesThumbnailNavigationDisplay = props.gallery.map((g, index) => {
    let image;
    if (g.type === "image") image = <img src={g.url.split('/img')[0] + "/cache/120x80-1/img" + g.url.split('/img')[1]}/>
    else if (g.type === "video") image = <span className="glyphicon glyphicon-play"></span>
    return (
      <li key={index}  className={ props.currentSlide === index ? "active" : ""}>
        <a onClick={e => props.onChangeCurrentSlide(index)}>{image}</a>
      </li>
    )
  })

  return (
    <div id="slide-navigation">
      <ul className="thumbnail-navigation" style={{width:thumbSliderWidth+"px"}}>
        {slidesThumbnailNavigationDisplay}
      </ul>
    </div>
  )
}

const rootElement = document.getElementById("product-media-slider-container");
ReactDOM.render(<ProductMediaSlider />, rootElement);