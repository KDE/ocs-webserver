import React, { useState } from 'react';

function BookReaderWrapper(props){
  
  const [ renditionState , setRenditionState ] = useState()

  React.useEffect(() => {
    var book = ePub(props.slide.url);
    var rendition = book.renderTo("book-container", { flow: "paginated", width: props.width - 40, height: props.height - 40});
    var displayed = rendition.display();
    setRenditionState(rendition)
  },[])

  function goPrev(){
    console.log('go prev')
    renditionState.prev();
  }

  function goNext(){
    console.log('go next');
    renditionState.next();
  }

  return (
    <div id="book-reader-wrapper">
      <div id="prev" className="arrow" onClick={goPrev}>
        <span className="glyphicon glyphicon-chevron-left"></span>  
      </div>
      <div id="book-container"></div>
      <div id="next" className="arrow" onClick={goNext}>
        <span className="glyphicon glyphicon-chevron-right"></span>  
      </div>
    </div>
  )
}

export default BookReaderWrapper;