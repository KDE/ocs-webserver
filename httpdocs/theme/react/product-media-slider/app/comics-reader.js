import React, { useState } from 'react';

function ComicsReaderWrapper(props){

    const [ loadingState, setLoadingState ] = useState('Loading...');
    const [ pages, setPages ] = useState([]);
    const [ currentPage, setCurrentPage ] = useState(0);
    const [ displayType, setDisplayType ] = useState("single")

    React.useEffect(() => {
        if (props.currentSlide === props.slideIndex) fetchArchive();
    },[props.currentSlide]);

    /* INIT */

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

    /* /INIT */

    /* COMPONENT */

    function onPrevPageBtnClick(){
      const newCurrentPage = currentPage - 1;
      setCurrentPage(newCurrentPage);
    }


    function onNextPageBtnClick(){
      const newCurrentPage = currentPage + 1;
      setCurrentPage(newCurrentPage);
    }

    let comicsReaderDisplay = loadingState
    if (pages.length > 0){
      comicsReaderDisplay = (
        <ComicBookReader 
          pages={pages}
          currentSlide={props.currentSlide}
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

  React.useEffect(() => (
    $(function() {

      var config = {
        $bookBlock : $( '#bb-bookblock-'+props.currentSlide ),
        $navNext : $( '#bb-nav-next' ),
        $navPrev : $( '#bb-nav-prev' ),
        $navFirst : $( '#bb-nav-first' ),
        $navLast : $( '#bb-nav-last' )
      }

      config.$bookBlock.bookblock( {
        speed : 800,
        shadowSides : 0.8,
        shadowFlip : 0.7
      } );

						
      var $slides = config.$bookBlock.children();

      // add navigation events
      config.$navNext.on( 'click touchstart', function() {
        config.$bookBlock.bookblock( 'next' );
        return false;
      } );

      config.$navPrev.on( 'click touchstart', function() {
        config.$bookBlock.bookblock( 'prev' );
        return false;
      } );

      config.$navFirst.on( 'click touchstart', function() {
        config.$bookBlock.bookblock( 'first' );
        return false;
      } );

      config.$navLast.on( 'click touchstart', function() {
        config.$bookBlock.bookblock( 'last' );
        return false;
      } );
      

    })
  ),[])

  const comicPages = props.pages.map((p,index) => (
    <div className="bb-item">
      <img key={index} src={p}/>
    </div>
  ));

  return (
    <div className="comic-book-reader">
      <div id={"bb-bookblock-" + props.currentSlide} className="bb-bookblock">
        {comicPages}
      </div>
      <nav>
        <a id="bb-nav-first" href="#" class="bb-custom-icon bb-custom-icon-first">First page</a>
        <a id="bb-nav-prev" href="#" class="bb-custom-icon bb-custom-icon-arrow-left">Previous</a>
        <a id="bb-nav-next" href="#" class="bb-custom-icon bb-custom-icon-arrow-right">Next</a>
        <a id="bb-nav-last" href="#" class="bb-custom-icon bb-custom-icon-last">Last page</a>
      </nav>
    </div>
  )
}

function ComicBookReaderNavigation(props){

  return (
    <div className="comic-book-reader-navigation">
      <div className="scroll-bar"></div>
      <div className="actions-menu">
        <a className="page-counter"> {props.currentPage + "/" + props.totalPages} </a>
        <a onClick={props.onPrevPageBtnClick} className="prev-page"></a> 
        <a onClick={props.onNextPageBtnClick} className="next-page"></a>
        <a className="one-page-view"></a>
        <a className="two-page-view"></a>
        <a className="full-screen"></a>
      </div>
    </div>
  )
}
/*
			var Page = (function() {
				
				var config = {
						$bookBlock : $( '#bb-bookblock' ),
						$navNext : $( '#bb-nav-next' ),
						$navPrev : $( '#bb-nav-prev' ),
						$navFirst : $( '#bb-nav-first' ),
						$navLast : $( '#bb-nav-last' )
					},
					init = function() {
						config.$bookBlock.bookblock( {
							speed : 800,
							shadowSides : 0.8,
							shadowFlip : 0.7
						} );
						initEvents();
					},
					initEvents = function() {

						// add swipe events
						$slides.on( {
							'swipeleft' : function( event ) {
								config.$bookBlock.bookblock( 'next' );
								return false;
							},
							'swiperight' : function( event ) {
								config.$bookBlock.bookblock( 'prev' );
								return false;
							}
						} );

						// add keyboard events
						$( document ).keydown( function(e) {
							var keyCode = e.keyCode || e.which,
								arrow = {
									left : 37,
									up : 38,
									right : 39,
									down : 40
								};

							switch (keyCode) {
								case arrow.left:
									config.$bookBlock.bookblock( 'prev' );
									break;
								case arrow.right:
									config.$bookBlock.bookblock( 'next' );
									break;
							}
						} );
					};

					return { init : init };

      })();
      
      */
export default ComicsReaderWrapper;