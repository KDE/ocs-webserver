import React, { useState } from 'react';

function ComicsReaderWrapper(props){

    const [ loadingState, setLoadingState ] = useState('Loading...');
    const [ pages, setPages ] = useState([]);

    React.useEffect(() => {
        if (props.currentSlide === props.slideIndex) fetchArchive();
    },[props.currentSlide]);

    function fetchArchive(){
        var request = new XMLHttpRequest();
        request.open("GET", props.slide.url);
        request.responseType = "blob";
        request.onload = function() {
            var response = this.response;
            openArchive(response)
        }
        request.send()
    }

    function openArchive(res){
        if (props.slide.file_type === "cbz") openZipArchive(res);
        else if (props.slide.file_type === "cbr") openRarArchive(res);
    }

    function openZipArchive(res){
        setLoadingState('reading archive...');
        var zip = new JSZip()
        zip.loadAsync(res).then(function (data) {
            let pagesArray = [];
            let zipFileIndex = 0;
            for ( var i in data.files ){
                zip.files[data.files[i].name].async('blob').then(function(blob) {
                    zipFileIndex += 1;
                    const pageArrayItem = {
                        name:data.files[i].name,
                        blob:blob
                    }
                    pagesArray.push(pageArrayItem);
                    if (Object.keys(data.files).length === zipFileIndex) generateImageGallery(pagesArray);
                });
            }
        });
    }

    function generateImageGallery(pagesArray){
        setLoadingState('extracting images...');
        pagesArray.forEach(function(page,index){
            var reader = new FileReader();
            reader.onload = function() {
                const imgSrc = "data:image/" + page.name.split('.')[1] + ";base64," + reader.result.split(';base64,')[1];
                let newPages = pages;
                newPages.push(imgSrc);
                setPages(newPages);
            }; 
            reader.onerror = function(event) {
                console.error("File could not be read! Code " + event.target.error.code);
            };
            reader.readAsDataURL(page.blob);
        });
    }


    let comicsReaderDisplay = loadingState
    if (pages.length > 0){
        const options = {
            width: props.containerWidth,
            height: props.sliderHeight,
            autoCenter: true,
            display: "single",
            acceleration: true,
            elevation: 50,
            gradients: !$.isTouch,
            when: {
              turned: function(e, page) {
                console.log("Current view: ", $(this).turn("view"));
              }
            }
        };

        comicsReaderDisplay = (
            <Turn options={options} className="magazine" currentSlide={props.currentSlide} slideIndex={props.slideIndex}>
            {pages.map((page, index) => (
              <div key={index} className="page">
                <img src={page} alt="" />
              </div>
            ))}
          </Turn>
        )
    }

    return (
        <div id="comics-reader-wrapper">
            {comicsReaderDisplay}
        </div>
    )
}


/*function ComicBookSlider(props){

    const [ currentPage, setCurrentPage ] = useState(0)
    const [ sliderWidth, setSliderWidth ] = useState(props.containerWidth * props.pages.length);
    const [ sliderPosition, setSliderPosition ] = useState(currentPage * props.containerWidth);

    console.log(currentPage,sliderPosition);

    function goPrev(){
        console.log('goPrev');
    }

    function goNext(){
        const newCurrentPage = currentPage + 1 <= pages.length ? currentPage + 1 : 0;
        setCurrentPage(newCurrentPage);
        const newSliderPosition = props.containerWidth * newCurrentPage;
        setSliderPosition(newSliderPosition);
    }

    let comicBookSliderStyle = {
        width:sliderWidth,
        left:'-' + sliderPosition + 'px'
    }

    const comicBookPagesDisplay = props.pages.map((p,index) => (
        <div className="comic-book-page" style={{"width":props.containerWidth}} key={index}>
            <img src={p} key={index}/>
        </div>
    ))

    return (
        <div id="comic-book-container">
            <div className="comic-book-navigation">
                <a onClick={goPrev}>Prev</a>
                <a onClick={goNext}>NExt</a>
            </div>
            <div id="comic-book-slider" style={comicBookSliderStyle}>
                {comicBookPagesDisplay}
            </div>
        </div>
    )

}*/


class Turn extends React.Component {
    
    constructor(props){
      super(props);
      this.handleKeyDown = this.handleKeyDown.bind(this);
    }

    componentDidMount() {
      if (this.el) {
        $(this.el).turn(Object.assign({}, this.props.options));
      }
      document.addEventListener("keydown", this.handleKeyDown, false);
    }
  
    componentWillUnmount() {
      if (this.el) {
        $(this.el)
          .turn("destroy")
          .remove();
      }
      document.removeEventListener("keydown", this.handleKeyDown, false);
    }
  
    handleKeyDown(event){
      if (this.props.slideIndex === this.props.currentSlide){
        if (event.keyCode === 37) {
          $(this.el).turn("previous");
        }
        if (event.keyCode === 39) {
          $(this.el).turn("next");
        }
      }
    };
  
    render() {
      return (
        <div
          className={this.props.className}
          style={Object.assign({}, this.props.style)}
          ref={el => (this.el = el)}
        >
          {this.props.children}
        </div>
      );
    }
  }

export default ComicsReaderWrapper;