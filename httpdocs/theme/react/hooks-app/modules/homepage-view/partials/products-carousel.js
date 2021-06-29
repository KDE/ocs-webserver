import { useState, useEffect } from 'react';
import {MiniCardListItem} from '../../common/mini-card';
import '../../../../assets/css/carousel.css';
import '../style/product-carousel.css';

function ProductsCarousel(props){

    const products = props.data.products;
    const [ loading, setLoading ] = useState(true);
    const [ disableLeftArrow, setDisableLeftArrow ] = useState(true);
    const [ disableRightArrow, setDisableRightArrow ] = useState(false);
    const [ sliderWidth, setSliderWidth ] = useState();
    const [ sliderPosition, setSliderPosition ] = useState();
    const [ containerWidth, setContainerWidth ] = useState();
    const [ containerNumber, setContainerNumber ] = useState();
    const [ itemsPerRow, setItemsPerRow ] = useState();
    const [ itemWidth, setItemWidth ] = useState();
  
  
    useEffect(() => {
      window.addEventListener("resize", () => updateDimensions(true));
      window.addEventListener("orientationchange",  () => updateDimensions(true));
      updateDimensions(true);
    },[])
  
    function updateDimensions(animateCarousel){
  
      const width = window.innerWidth;
      let device;
      if (width >= 910) device = "large"
      else if (width < 910 && width >= 610) device = "mid";
      else if (width < 610) device = "tablet";
  
      let newItemsPerRow = 6;
        if (device === 'large') newItemsPerRow = 6;
        else if (device === 'mid') newItemsPerRow = 4;
        else if (device === 'tablet') newItemsPerRow = 2;
      
  
      let newContainerWidth;
      if (window.page === "opendesktop") newContainerWidth = $('#main-content').width();
      else if (window.page === "appimages" || window.page === "libreoffice") newContainerWidth = $('#introduction').find('.container').width();
  
      const newContainerNumber = Math.ceil(products.length / (newItemsPerRow - 1));
      const newItemwidth = newContainerWidth / newItemsPerRow;
      const newSliderWidth = (newContainerWidth - newItemwidth) * newContainerNumber;
      let newsliderPosition = 0;
      if (sliderPosition) newsliderPosition = sliderPosition;
  
      $('#carousel-module-container').width(newContainerWidth);
  
      setSliderPosition(newsliderPosition);
      setContainerWidth(newContainerWidth);
      setItemsPerRow(newItemsPerRow - 1);
      setSliderWidth(newSliderWidth);
      setItemWidth(newItemwidth);
      setContainerNumber(newContainerNumber);
  
      if (animateCarousel) animateProductCarousel('right',animateCarousel);
      else if (finishedProducts === true) setDisableRightArrow(true);
    }
  
    function animateProductCarousel(dir,animateCarousel){
      let newSliderPosition = sliderPosition;
      const endPoint = sliderWidth - (containerWidth - itemWidth);
      if (dir === 'left'){
        if (sliderPosition > 0){
          //newSliderPosition = sliderPosition - (containerWidth - itemWidth);
          if (containerWidth<(itemWidth*3)) newSliderPosition = sliderPosition - itemWidth;
          else newSliderPosition = sliderPosition - itemWidth *2;
        }
      } else {
        if (Math.trunc(sliderPosition) < Math.trunc(endPoint)){
          //newSliderPosition = sliderPosition + (containerWidth - itemWidth);
          if(containerWidth<(itemWidth*3)) newSliderPosition = sliderPosition + itemWidth;
          else newSliderPosition = sliderPosition + itemWidth *2 ;
        } else {
          newSliderPosition = 0
          /*if (!animateCarousel){
          if (products.length >= 15 || this.state.finishedProducts){
              newSliderPosition = 0;
            } else {
              this.getNextProductsBatch();
            }
          }*/
        }
      }
      setSliderPosition(newSliderPosition);
      
      let newdisableLeftArrow = false;
      if (sliderPosition <= 0) newdisableLeftArrow = true;
      let newdisableRightArrow = false;
      /*if (sliderPosition >= endPoint && this.state.finishedProducts === true){
        disableRightArrow = true;
      }*/
      setDisableLeftArrow(newdisableLeftArrow);
      setDisableRightArrow(newdisableRightArrow)
    }
  
    /*function getNextProductsBatch(){
  
      this.setState({disableRightArrow:true},function(){
        let limit = (this.state.itemsPerRow * (this.state.containerNumber + 1)) - products.length;
        if (limit <= 0){
          limit = this.state.itemsPerRow;
        }
  
        let url;
        if (!props.catIds){
          url = "/home/getnewactiveplingedproductjson/?limit="+limit+"&offset="+this.state.offset;
        } else {
          url = "/home/showlastproductsjson/?page=1&limit="+limit+"&offset="+this.state.offset+"&catIDs="+props.catIds+"&isoriginal=0";
        }
  
        const self = this;
        $.ajax({url: url,cache: false}).done(function(response){
            let products = self.state.products,
                finishedProducts = false,
                animateCarousel = true;
  
            if (response.length > 0){
              products = products.concat(response);
            } else {
              finishedProducts = true;
              animateCarousel = false;
            }
  
            if (response.length < limit){
              finishedProducts = true;
            }
  
            self.setState({
              products:products,
              offset:self.state.offset + response.length,
              finishedProducts:finishedProducts},function(){
                self.updateDimensions(animateCarousel);
            });
        });
      });
    }*/
  
    let carouselItemsDisplay;
    if (itemWidth){
      let plingedProduct = false;
      if (!props.catIds) plingedProduct = true;

        carouselItemsDisplay = products.map((product,index) => (
            <MiniCardListItem
                key={index}
                item={product}
                plingedProduct={plingedProduct}
                onChangeUrl={props.onChangeUrl}
                showPlings={true}
                scoreCircleSize={32}
                showUser={false}
                dateDisplay={"timeAgo"}
                itemStyle={{width:itemWidth,padding:"10px",height:itemWidth * 1.35}}
                itemCssClass={"product-carousel-mini-card-item"}
            />
        ));
    }

      let carouselArrowLeftDisplay;
      if (disableLeftArrow){
        carouselArrowLeftDisplay = (
          <a className="carousel-arrow arrow-left disabled">
            <span className="glyphicon glyphicon-chevron-left"></span>
          </a>
        )
      } else {
        carouselArrowLeftDisplay = (
          <a onClick={() => animateProductCarousel('left')} className="carousel-arrow arrow-left">
            <span className="glyphicon glyphicon-chevron-left"></span>
          </a>
        );
      }
  
      let carouselArrowRightDisplay;
      if (disableRightArrow){
        carouselArrowRightDisplay = (
          <a className="carousel-arrow arrow-right disabled">
            <span className="glyphicon glyphicon-chevron-right"></span>
          </a>
        )
      } else {
        carouselArrowRightDisplay = (
          <a onClick={() => animateProductCarousel('right')} className="carousel-arrow arrow-right">
            <span className="glyphicon glyphicon-chevron-right"></span>
          </a>
        );
      }
  
      let hpVersionClass = "two";
      let carouselWrapperStyling = {};
      let carouselArrowsMargin;
      if (window.hpVersion === 2 && itemWidth){
        hpVersionClass = "two";
        carouselWrapperStyling = {
          "paddingLeft":itemWidth / 2,
          "paddingRight":itemWidth / 2,
          "height":itemWidth * 1.35
        }
        carouselArrowsMargin = itemWidth / 4;
      }
  
      let urlSuffix='';
      if (window.page === "libreoffice") urlSuffix = "/s/LibreOffice";

      let titleLink = urlSuffix + "/browse/cat/" + props.catIds + "/";
      if (!props.catIds) titleLink = "/community#plingedproductsPanel";
      else if (props.catIds.indexOf(',') > -1) titleLink  = urlSuffix + "/browse/";  

    return (
        <div id="carousel-module-container">
            <div id="carousels-module">
                <div className="section">
                    <div className="container">
                        <div className="product-carousel two">
                            <div className="product-carousel-header">
                                <h2>
                                    <a href={titleLink}>
                                        {props.data.title} <span className="glyphicon glyphicon-chevron-right"></span>
                                    </a>
                                </h2>
                            </div>
                            <div className="product-carousel-wrapper" style={carouselWrapperStyling}>
                                <div className="product-carousel-left" style={{"left":carouselArrowsMargin}}>
                                    {carouselArrowLeftDisplay}
                                </div>
                                <div className="product-carousel-container">
                                    <div className="product-carousel-slider" style={{"width":sliderWidth,"left":"-"+sliderPosition + "px"}}>
                                    {carouselItemsDisplay}
                                    </div>
                                </div>
                                <div className="product-carousel-right" style={{"right":carouselArrowsMargin}}>
                                    {carouselArrowRightDisplay}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    )
}

export default ProductsCarousel;