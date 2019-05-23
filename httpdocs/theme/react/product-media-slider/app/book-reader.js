import React, { useState } from 'react';

function BookReaderWrapper(props){
  
  const [ renditionState , setRenditionState ] = useState()

  React.useEffect(() => {
    var book = ePub(props.slide.url);
    var rendition = book.renderTo("book-container", { flow: "paginated", width: props.width - 40, height: props.height - 40});
    setRenditionState(rendition)
    var displayed = rendition.display();
    book.ready.then((book) => {
      console.log(book);
      /*let meta = book.package.metadata; // Metadata from the package json
      let toc = book.navigation.toc; // Table of Contents
      let landmarks = book.navigation.landmarks; // landmarks
      let spine = book.spine; // landmarks
      let cover = book.cover; // landmarks
      let resources = book.resources; // landmarks
      let pageList = book.pageList; // page list (if present)
      console.log(meta);
      console.log(toc);
      console.log(landmarks);
      console.log(spine);
      console.log(cover);
      console.log(resources);
      console.log(pageList);*/
    })
  },[])

  function goPrev(){
    renditionState.prev();
  }

  function goNext(){
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