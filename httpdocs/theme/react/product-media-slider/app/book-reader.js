import React, { useState } from 'react';
import {isMobile} from 'react-device-detect';

function BookReaderWrapper(props){

  const [ loading, setLoading ] = useState(true);
  const [ renditionState , setRenditionState ] = useState()
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

        console.log('rendition.currentLocation():', rendition.currentLocation());
        
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
    bookNavigation = (
      <div id="book-pager">
        <div>
          <span><a onClick={() => onStartClick()}>First Page</a></span>
          <span>
            <input type="number" className="form-control" placeholder={currentPage} min="0" max={totalPages} onChange={(e) => onPageNumberInput(e.target.value)}/>
            {" / " + totalPages}
          </span>
          <span><a onClick={() => onEndClick()}>Last Page</a></span>
        </div>
      </div>
    )
  }

  let bookMenuDisplay, tocMenuToggleDisplay, prevButtonDisplay, nextButtonDisplay;
  if (renditionState){
    if (showPrevButton === true){
      prevButtonDisplay = (
        <div id="prev" className="arrow" onClick={goPrev}>
          <span className="glyphicon glyphicon-chevron-left"></span>  
        </div>
      )
    }
    if (showNextButton === true){
      nextButtonDisplay = (
        <div id="next" className="arrow" onClick={goNext}>
          <span className="glyphicon glyphicon-chevron-right"></span>  
        </div>
      )
    }
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
      {prevButtonDisplay}
      {nextButtonDisplay}
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