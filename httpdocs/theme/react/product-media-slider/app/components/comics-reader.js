import React, { useState } from 'react';
import {generatePagesArray, renderPages} from '../product-media-slider-helpers';

function ComicsReaderWrapper(props){
    const [ loading, setLoading ] = useState('Loading...');
    const [ comicBookInitiated, setComicBookInitiated ] = useState(false);
    const [ pages, setPages ] = useState([]);
    const [ viewedPages, setViewedPages ] = useState([]);

    /* INIT */
 
    React.useEffect(() => {
      if (props.slideIndex === props.currentSlide){
        setComicBookInitiated(true);
        initComicBook();
      }
    },[props.slideIndex,props.currentSlide]);

    React.useEffect(() => {
      console.log(viewedPages);
      if (viewedPages.length > 3){
        console.log('now report on reading if its not already reported');
      }
    },[viewedPages])

    function initComicBook(){
      const url = json_server_comics + "/api/files/toc?id="+props.slide.file_id+"&format=json";
      $.ajax({url:url}).done(function(res){
          const pages = renderPages(res.files,props.slide.file_id);
          setPages(pages);
          const newViewedPagedArray = [...viewedPages, pages[0] ];
          setViewedPages(newViewedPagedArray);
      });
    }

    function onViewPage(pageIndex){
      const newViewedPagedArray = [...viewedPages, pages[pageIndex] ];
      setViewedPages(newViewedPagedArray);
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
          onViewPage={(page) => onViewPage(page)}
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
  const [ displayType, setDisplayType ] = useState("single")
  const [ pages, setPages ] = useState(generatePagesArray(props.pages,displayType));
  const [ currentPage, setCurrentPage ] = useState(0)
  const [ totalPages, setTotalPages ] = useState(pages.length)
  const [ viewMode, setViewMode ] = useState('normal');

  React.useEffect(() => { 
    initComicReader()
  },[])

  function initComicReader(){
    const bookBlockElement = document.getElementById('bb-bookblock');

    if (bookBlockElement){
      $(document).ready(function() {
        window.comicSwiper = new Swiper('.comic-book-reader' , {
          speed: 400,
          initialSlide: 0,
          observer: true, 
          observeParents: true,
          preloadImages: true,
          updateOnImagesReady: true,
          nested:true,
          threshold:0,
          onSlideChangeStart: function(swiper){
            setCurrentPage(swiper.activeIndex);
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
    props.onViewPage(nextPage);
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
      <div className="swiper-slide" key={index}>
        <img src={p}/>
      </div>      
    ))

    comicBookDisplay = (
      <div id="bb-bookblock" className="swiper-wrapper">
        {comicPages}
      </div>
    )
  }

  return (
    <div className={"comic-book-reader swiper-container " + viewMode}>
      {comicBookDisplay}
      <div className="nav-container">
        <nav>
          <a id="bb-nav-counter">{ ( currentPage + 1 ) + "/" + totalPages }</a>
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