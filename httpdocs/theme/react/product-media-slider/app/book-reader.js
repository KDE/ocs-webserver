import React, { useState } from 'react';

function BookReaderWrapper(props){
  
  const [ renditionState , setRenditionState ] = useState()
  const [ currentPage, setCurrentPage ] = useState();
  const [ totalPages, setTotalPages ] = useState();

  React.useEffect(() => {initBookReader()},[])
  React.useEffect(() => {
    initBookReader()
  },[props.cinemaMode])

  function initBookReader(){
    // remove book dom element if exists
    const element = $("#book-container").find(".epub-container");
    if (element) element.remove()

    // Initialize the book
    let book = ePub(props.slide.url, {});
    let rendition = book.renderTo('book-container', {
        flow: 'paginated',
        manager: 'continuous',
        spread: 'always',
        width: (props.width - 40),
        height: (props.height - 40)
    });
    setRenditionState(rendition)

    // Display the book
    let displayed = rendition.display(window.location.hash.substr(1) || undefined);
    displayed.then(function() {
        // console.log('rendition.currentLocation():', rendition.currentLocation());
    });

    // Generate location and pagination
    book.ready.then(function() {
        const stored = localStorage.getItem(book.key() + '-locations');
        // console.log('metadata:', book.package.metadata);
        if (stored) {
            return book.locations.load(stored);
        } else {
            return book.locations.generate(1024); // Generates CFI for every X characters (Characters per/page)
        }
    }).then(function(location) { // This promise will take a little while to return (About 20 seconds or so for Moby Dick)
        localStorage.setItem(book.key() + '-locations', book.locations.save());
    });

    // When navigating to the next/previous page
    rendition.on('relocated', function(locations) {
        setCurrentPage(book.locations.locationFromCfi(locations.start.cfi));
        setTotalPages(book.locations.total)
    })
  }

  function goPrev(){
    renditionState.prev();
  }

  function goNext(){
    renditionState.next();
  }

  let pageCountDisplay;
  if (totalPages) pageCountDisplay = <span>{currentPage + "/" + totalPages}</span>

  return (
    <div id="book-reader-wrapper">
      <div id="prev" className="arrow" onClick={goPrev}>
        <span className="glyphicon glyphicon-chevron-left"></span>  
      </div>
      <div id="book-container"></div>
      <div id="book-pager">
        {pageCountDisplay}
      </div>
      <div id="next" className="arrow" onClick={goNext}>
        <span className="glyphicon glyphicon-chevron-right"></span>  
      </div>
    </div>
  )
}

export default BookReaderWrapper;