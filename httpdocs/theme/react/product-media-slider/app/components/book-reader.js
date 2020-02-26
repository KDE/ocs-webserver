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
  const [ viewedPagesCount, setViewedPagesCount ] = useState(0);

  React.useEffect(() => {initBookReader()},[])
  React.useEffect(() => { 
    if (window.book) window.book.destroy()
    initBookReader()
  },[props.cinemaMode,props.width])

  React.useEffect(() => {
    if (totalPages === 0){
        hackBookPageCount();
    }
  },[totalPages,window.book])

  React.useEffect(() => {
    if (viewedPagesCount > 3){
      reportBookRead();
    }
  },[viewedPagesCount])

  function hackBookPageCount(){
    const newTotalPageCount = window.book.locations.total;
    if (newTotalPageCount === 0){
      setTimeout(() => {
        hackBookPageCount();
      }, 200);
    } else {
      setTotalPages(newTotalPageCount)
    }
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
    const newViewedPagesCountValue = viewedPagesCount + 1;
    setViewedPagesCount(newViewedPagesCountValue);
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

  function reportBookRead(){
    console.log('report book reading')
    // console.log('https://www.pling.cc/p/1304363/startmediaviewajax?collection_id=1304363&file_id=34905&type_id=3');
    console.log(props);
    // const audioStartUrl = "https://" + window.location.hostname + "/p/" + props.product.project_id + '/startmediaviewajax?collection_id='+audioItem.collection_id+'&file_id='+audioItem.file_id+'&type_id=3';
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
    if (showNextButton === true && totalPages !== 0){
      nextButtonDisplay = (
        <span><a id="next-page-button" onClick={() => goNext()}>{"next >"}</a></span>
      )
    }
    let bookNavigationMidDisplay;
    if (totalPages !== 0){
      bookNavigationMidDisplay = (
        <span>
          <input type="number" className="form-control" placeholder={currentPage} min="0" max={totalPages} onChange={(e) => onPageNumberInput(e.target.value)}/>
          {" / " + totalPages}
        </span>
      )
    }
    bookNavigation = (
      <div id="book-pager">
        <div>
          {prevButtonDisplay}
          {bookNavigationMidDisplay}
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