import React from 'react';

function BookReaderWrapper(props){
  
  var book = ePub(props.slide.url);
  var rendition = book.renderTo("book-container", { flow: "scrolled-doc", width: props.width, height: props.height});
  var displayed = rendition.display();

  return (
    <div id="book-reader-wrapper">
      <div id="prev" onClick={goPrev}></div>
      <div id="book-container"></div>
      <div id="next" onClick={goNext}></div>
    </div>
  )
}

export default BookReaderWrapper;