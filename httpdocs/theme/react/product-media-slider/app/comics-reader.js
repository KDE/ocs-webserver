import React, { useState } from 'react';
import {generatePagesArray, renderPages} from './product-media-slider-helpers';

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
    initComicReader()
  },[])

  function initComicReader(){
    $(function() {
      $( '#bb-bookblock-'+props.slideIndex ).bookblock( {
        speed : 800,
        shadowSides : 0.8,
        shadowFlip : 0.7,
        onBeforeFlip: function( page ) { onBeforeFlip(page) },
        onEndFlip	: function( page, isLimit ) {  readerOnEndFlip(page,isLimit) },
      } );
    })
  }

  function onComicReaderNavClick(val){
    $( '#bb-bookblock-'+props.slideIndex).bookblock(val);
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
      <div key={index} className="bb-item">
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
          <a id="bb-nav-viewmode" onClick={() => props.onFullScreenToggle(true)}><span className="glyphicon glyphicon-fullscreen"></span></a>
        </nav>
        <span className="title">{props.comicsFileName}</span>
      </div>
    </div>
  )
}

export default ComicsReaderWrapper;