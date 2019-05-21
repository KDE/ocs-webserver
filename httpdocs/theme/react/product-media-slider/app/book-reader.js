import React, { useState } from 'react';
import {
  EpubView, // Underlaying epub-canvas (wrapper for epub.js iframe)
  EpubViewStyle, // Styles for EpubView, you can pass it to the instance as a style prop for customize it
  ReactReader, // A simple epub-reader with left/right button and chapter navigation
  ReactReaderStyle // Styles for the epub-reader it you need to customize it
} from "react-reader";

class BookReaderWrapper extends React.Component {
  render() {
    return (
      <div style={{ position: "relative", height: "100%" }}>
        <ReactReader
          url={props.slide.url}
          title={props.slide.title}
          location={"epubcfi(/6/2[cover]!/6)"}
          locationChanged={epubcifi => console.log(epubcifi)}
        />
      </div>
    );
  }
}

export default BookReaderWrapper;