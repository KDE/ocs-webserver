import React, { useState } from 'react';
import {generatePagesArray, renderPages} from './product-media-slider-helpers';

function ComicsReaderWrapper(props){
    const [ loadingState, setLoadingState ] = useState('Loading...');
    const [ comicBookInitiated, setComicBookInitiated ] = useState(false);
    const [ pages, setPages ] = useState([]);

    /* INIT */

    React.useEffect(() => {
      console.log('init comic book');
      console.log(comicBookInitiated);
      if (pages.length === 0){
        setComicBookInitiated(true);
        initComicBook();
      }
    },[]);

    function initComicBook(){
      const url = json_server_comics + "/api/files/toc?id="+props.slide.file_id+"&format=json";
      $.ajax({url:url}).done(function(res){
        //if (res.files.length > 1){
          const pages = renderPages(res.files,props.slide.file_id);
          if (pages.length > 1) setPages(pages);
        //}
      });
    }

    /* COMPONENT */
    console.log(pages);
    let comicsReaderDisplay = loadingState;
    if (pages.length > 0){
      comicsReaderDisplay = (
        <ComicBookReader 
          pages={pages}
          currentSlide={props.currentSlide}
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
  const [ pages, setPages ] = useState(generatePagesArray(props.pages,displayType))
  const [ currentPage, setCurrentPage ] = useState(1)
  const [ totalPages, setTotalPages ] = useState(pages.length)
  const [ viewMode, setViewMode ] = useState('normal');

  React.useEffect(() => { 
    console.log('another init comic reader');
    initComicReader()
  },[])

  function initComicReader(){
    $(function() {
      $( '#bb-bookblock-'+props.currentSlide ).bookblock( {
        speed : 800,
        shadowSides : 0.8,
        shadowFlip : 0.7,
        onBeforeFlip: function( page ) { onBeforeFlip(page) },
        onEndFlip	: function( page, isLimit ) {  readerOnEndFlip(page,isLimit) },
      } );
    })
  }

  function onComicReaderNavClick(val){
    $( '#bb-bookblock-'+props.currentSlide).bookblock(val);
  }

  function onBeforeFlip(page){
    return false;
  }

  function readerOnEndFlip(page,isLimit){
    setCurrentPage(isLimit + 1);
    return false;
  }

  let comicBookDisplay;
  if (loading) comicPages = "loading...";
  else {
    const comicPages = pages.map((p,index) => (
      <div key={index} className="bb-item">
        <ComicBookPage url={p[0]}/>
        <ComicBookPage url={p[1]}/>
      </div>      
    ))

    comicBookDisplay = (
      <div id={"bb-bookblock-" + props.currentSlide} className="bb-bookblock">
        {comicPages}
      </div>
    )
  }

  return (
    <div className={"comic-book-reader " + viewMode}>
      {comicBookDisplay}
      <div className="nav-container">
        <nav>
          <a id="bb-nav-counter" href="#">{currentPage + "/" + totalPages}</a>
          <a id="bb-nav-first" href="#" onClick={() => onComicReaderNavClick('first')}><span className="glyphicon glyphicon-step-backward"></span></a>
          <a id="bb-nav-prev" href="#" onClick={() => onComicReaderNavClick('prev')}><span className="glyphicon glyphicon-triangle-left"></span></a>
          <a id="bb-nav-next" href="#" onClick={() => onComicReaderNavClick('next')}><span className="glyphicon glyphicon-triangle-right"></span></a>
          <a id="bb-nav-last" href="#" onClick={() => onComicReaderNavClick('last')}><span className="glyphicon glyphicon-step-forward"></span></a>
          <a id="bb-nav-last" href="#" onClick={() => onComicReaderNavClick('last')}><span className="glyphicon glyphicon-step-forward"></span></a>
          <a id="bb-nav-viewmode" href="#" onClick={() => setViewMode('fullscreen')}><span className="glyphicon glyphicon-fullscreen"></span></a>
        </nav>
      </div>
    </div>
  )
}

function ComicBookPage(props){
  const [ image, setImage ] = useState();

  React.useEffect(() => {
    fetchPageImage();
  });

  function fetchPageImage(){
    $.ajax({url:props.url}).done(function(res){
      console.log(res);
      setImage(res);
    });

    let comicBookPageDisplay = null;
    if (image) comicBookPageDisplay = <img src={image}/>


    return (
      {comicBookDisplay}
    )
  }
}

function ComicBookReaderNavigation(props){

  return (
    <div className="comic-book-reader-navigation">
      <div className="scroll-bar"></div>
      <div className="actions-menu">
        <a className="page-counter"> {props.currentPage + "/" + props.totalPages} </a>
        <a onClick={props.onPrevPageBtnClick} className="prev-page"></a> 
        <a onClick={props.onNextPageBtnClick} className="next-page"></a>
        <a className="one-page-view"></a>
        <a className="two-page-view"></a>
        <a className="full-screen"></a>
      </div>
    </div>
  )
}

export default ComicsReaderWrapper;