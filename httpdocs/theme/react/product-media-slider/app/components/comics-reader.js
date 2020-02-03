import React, { useState } from 'react';
import {generatePagesArray, renderPages} from '../product-media-slider-helpers';

function ComicsReaderWrapper(props){
    const [ loading, setLoading ] = useState('Loading...');
    const [ comicBookInitiated, setComicBookInitiated ] = useState(false);
    const [ pages, setPages ] = useState([]);

    /* INIT */

    React.useEffect(() => {
      if (props.slideIndex === props.currentSlide){
        setComicBookInitiated(true);
        initComicBook();
      }
    },[props.slideIndex,props.currentSlide]);

    function initComicBook(){
      const url = json_server_comics + "/api/files/toc?id="+props.slide.file_id+"&format=json";
      $.ajax({url:url}).done(function(res){
          const pages = renderPages(res.files,props.slide.file_id);
          setPages(pages);
      });
    }

    /* COMPONENT */
    let comicsReaderDisplay = <span id="ajax-loader"></span>  
    if (pages.length > 0){
      comicsReaderDisplay = (
        <ComicBookReader 
          pages={pages}
          slideIndex={props.slideIndex}
          comicsFileName={props.slide.title}
          onFullScreenToggle={props.onFullScreenToggle}
          isFullScreen={props.isFullScreen}
        />
      )
    }

    return (
        <div id="comics-reader-wrapper">
            {comicsReaderDisplay}
        </div>
    )
}

function ComicBookReader(props){

  const [ loading, setLoading ] = useState(false);
  const [ displayType, setDisplayType ] = useState("double")
  const [ pages, setPages ] = useState(generatePagesArray(props.pages,displayType));
  const [ currentPage, setCurrentPage ] = useState(1)
  const [ totalPages, setTotalPages ] = useState(pages.length)
  const [ viewMode, setViewMode ] = useState('normal');

  React.useEffect(() => { 
    initComicReader()
  },[])

  function initComicReader(){
    const bookBlockElement = document.getElementById('#bb-bookblock-'+props.slideIndex);
    if (bookBlockElement){
      $(document).ready(function() {
        window.comicSwiper = new Swiper('#bb-bookblock-'+props.slideIndex , {
          speed: 400,
          initialSlide: 0,
          observer: true, 
          observeParents: true,
          preloadImages: true,
          updateOnImagesReady: true,
          pagination: '.swiper-pagination',
          paginationClickable: '.swiper-pagination',
          nested:true,
          threshold:0,
          onSlideChangeStart: function(swiper){
            setCurrentSlide(swiper.activeIndex);
          }
        });
        window.comicSwiper.update()
      });
    } else {
      setTimeout(() => {
        initComicReader();
      }, 500);
    }
  }

  function onComicReaderNavClick(val){
    let nextPage;
    if (val === "first") nextPage = 0;
    else if (val === "last") nextPage = totalPages;
    else if (val === "prev") nextPage = currentPage === 0 ? 0 : currentPage - 1;
    else if (val === "next") nextPage = currentPage === totalPages ? totalPages : currentPage + 1;
    window.comicSwiper.slideTo(nextPage) 
  }

  function onBeforeFlip(page){
    return false;
  }

  function readerOnEndFlip(page,isLimit){
    setCurrentPage(isLimit + 1);
    return false;
  }

  let comicBookDisplay;
  if (loading) comicPages = <img src="../../flatui/img/ajax-loader.gif"/>
  else {
    const comicPages = pages.map((p,index) => (
      <div key={index}>
        <img src={p[0]}/>
        <img src={p[1]}/>
      </div>      
    ))

    comicBookDisplay = (
      <div id={"bb-bookblock-" + props.slideIndex} className="bb-bookblock">
        {comicPages}
      </div>
    )
  }

  return (
    <div className={"comic-book-reader " + viewMode}>
      {comicBookDisplay}
      <div className="nav-container">
        <nav>
          <a id="bb-nav-counter">{currentPage + "/" + totalPages}</a>
          <a id="bb-nav-first" onClick={() => onComicReaderNavClick('first')}><span className="glyphicon glyphicon-step-backward"></span></a>
          <a id="bb-nav-prev" onClick={() => onComicReaderNavClick('prev')}><span className="glyphicon glyphicon-triangle-left"></span></a>
          <a id="bb-nav-next" onClick={() => onComicReaderNavClick('next')}><span className="glyphicon glyphicon-triangle-right"></span></a>
          <a id="bb-nav-last" onClick={() => onComicReaderNavClick('last')}><span className="glyphicon glyphicon-step-forward"></span></a>
          <a id="bb-nav-viewmode" onClick={() => props.onFullScreenToggle(props.isFullScreen === true ? false : true)}><span className="glyphicon glyphicon-fullscreen"></span></a>
        </nav>
        <span className="title">{props.comicsFileName}</span>
      </div>
    </div>
  )
}

export default ComicsReaderWrapper;