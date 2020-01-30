import React, { useState } from 'react';
import {isMobile} from 'react-device-detect';

function BookReaderWrapper(props){

  const [ loading, setLoading ] = useState(true);
  const [ renditionState , setRenditionState ] = useState();
  const [ currentPage, setCurrentPage ] = useState();
  const [ totalPages, setTotalPages ] = useState();
  const [ showBookMenu, setShowBookMenu ] = useState(false);
  const [ showPrevButton, setShowPrevButton ] = useState(false);
  const [ showNextButton, setShowNextButton ] = useState(false);

  React.useEffect(() => {initBookReader()},[])
  React.useEffect(() => { 
    if (window.book) window.book.destroy()
    initBookReader()
  },[props.cinemaMode,props.width])

  React.useEffect(() => {
    console.log(totalPages + ' - ' + window.book.locations.total);
    if (totalPages === 0){
      setTimeout(() => {
        hackBookPageCount();
      }, 500);
    }
  },[totalPages,window.book])

  function hackBookPageCount(){
    console.log(window.book)
    console.log(window.book.locations);
    console.log(window.book.locations.total);
    const newTotalPageCount = window.book.locations.total;
    setTotalPages(newTotalPageCount)
  }

  function initBookReader(){
    // Initialize the book
    window.book = ePub(props.slide.url, {});
    window.rendition = book.renderTo('viewer', {
        flow: 'paginated',
        manager: 'default',
        spread: 'always',
        width: (props.width - 20),
        height: (props.height - 35)
    });

    setRenditionState(rendition)

    // Display the book
    window.displayed = window.rendition.display(window.location.hash.substr(1) || undefined);
    displayed.then(function() {
        // console.log('rendition.currentLocation():', rendition.currentLocation());
    });

    // Generate location and pagination
    window.book.ready.then(function() {
        const stored = localStorage.getItem(book.key() + '-locations');
        // console.log('metadata:', book.package.metadata);
        if (stored) {
            return window.book.locations.load(stored);
        } else {
            return window.book.locations.generate(1024); // Generates CFI for every X characters (Characters per/page)
        }

    }).then(function(location) { // This promise will take a little while to return (About 20 seconds or so for Moby Dick)
        localStorage.setItem(book.key() + '-locations', book.locations.save());
    });

    // When navigating to the next/previous page
    window.rendition.on('relocated', function(locations) {

        setCurrentPage(book.locations.locationFromCfi(locations.start.cfi));
        setTotalPages(book.locations.total)
        
        if (loading === true) setLoading(false);

        if (rendition.currentLocation().atStart === true) setShowPrevButton(false)
        else setShowPrevButton(true)

        if (rendition.currentLocation().atEnd === true) setShowNextButton(false)
        else setShowNextButton(true)
    })
  }

  function goPrev(){
    renditionState.prev();
  }

  function goNext(){
    renditionState.next();
  }

  function onStartClick(){
    const lastPageCfi = renditionState.book.locations._locations[0];
    renditionState.display(lastPageCfi);
  }

  function onEndClick(){
    const lastPageCfi = renditionState.book.locations._locations[renditionState.book.locations._locations.length - 1];
    renditionState.display(lastPageCfi);
  } 
  
  function onPageNumberInput(val){
    const cfiFromNumber = renditionState.book.locations._locations[val];
    renditionState.display(cfiFromNumber);
  }

  function toggleMenu(){
    const newShowBookMenu = showBookMenu === true ? false : true;
    setShowBookMenu(newShowBookMenu)
  }

  function goToTocItem(item){
    renditionState.display(item.href);
    toggleMenu();
  }


  let loadingDisplay = <div id="ajax-loader"></div>
  let bookNavigation;
  if (loading === false){
    loadingDisplay = "";
    let prevButtonDisplay;
    if (showPrevButton === true){
      prevButtonDisplay = (
        <span><a onClick={() => goPrev()}>{"< previous"}</a></span>
      )
    }
    let nextButtonDisplay;
    if (showNextButton === true){
      nextButtonDisplay = (
        <span><a id="next-page-button" onClick={() => goNext()}>{"next >"}</a></span>
      )
    }
    bookNavigation = (
      <div id="book-pager">
        <div>
          {prevButtonDisplay}
          <span>
            <input type="number" className="form-control" placeholder={currentPage} min="0" max={totalPages} onChange={(e) => onPageNumberInput(e.target.value)}/>
            {" / " + totalPages}
          </span>
          {nextButtonDisplay}
        </div>
      </div>
    )
  }

  let bookMenuDisplay, tocMenuToggleDisplay;
  if (renditionState){
    if (renditionState.book.navigation){
      tocMenuToggleDisplay = (
        <div id="toc-menu-toggle" onClick={toggleMenu}>
          <span className="glyphicon glyphicon-menu-hamburger"></span>
        </div>
      )
    }    
    if (showBookMenu === true){
      const items = renditionState.book.navigation.toc.map((item,index) => (
        <BookMenuItem key={index} goToTocItem={goToTocItem} item={item}/>
      ));
      bookMenuDisplay = <ul id="book-menu">{items}</ul>
    }
  }

  let bookReaderWrapperCssClass = isMobile === true ? "is-mobile" : "is-desktop";

  return (
    <div id="book-reader-wrapper" className={bookReaderWrapperCssClass}>
      {loadingDisplay}
      {tocMenuToggleDisplay}
      <div id="viewer" className="spreads">
      </div>
      {bookNavigation}
      {bookMenuDisplay}
    </div>
  )
}

function BookMenuItem(props){

  function onGoToTocItem(){
    props.goToTocItem(props.item);
  }

  let subItemsDisplay;
  if (props.item.subitems && props.item.subitems.length > 0){
    const items = props.item.subitems.map((subitem,index) => (
      <BookMenuItem goToTocItem={props.goToTocItem} key={index} item={subitem}/>
    ));
    subItemsDisplay = <ul> {items} </ul>
  }

  return (
    <li>
      <a onClick={() => onGoToTocItem()}>{props.item.label}</a>
      {subItemsDisplay}
    </li>
  )
}

export default BookReaderWrapper;