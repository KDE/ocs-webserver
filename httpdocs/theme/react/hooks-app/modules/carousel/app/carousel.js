import TimeAgo from 'javascript-time-ago';
import en from 'javascript-time-ago/locale/en';
TimeAgo.addLocale(en);

function CarouselsModule(props){

  const [ loading, setLoading ] = React.useState(true);
  const [ device, setDevice ] = React.useState();
  const [ env, setEnv ] = React.useState();
  const [ productGroupsArray, setProductGroupsArray ] = React.useState();

  
  React.useEffect(() => {
    updateDimensions();
    initCarouselModule();
    return () => {
      window.removeEventListener("resize", updateDimensions);
      window.removeEventListener("orientationchange", updateDimensions);
    }
  },[])

  function initCarouselModule(){
    window.addEventListener("resize", updateDimensions);
    window.addEventListener("orientationchange", updateDimensions);
    let initEnv = "live";
    if (location.hostname.endsWith('cc')) initEnv = "test";
    else if (location.hostname.endsWith('localhost')) initEnv = "test";
    setEnv(initEnv)
    convertDataObject();
  }

  function updateDimensions(){
    const width = window.innerWidth;
    let initDevice;
    if (width >= 910) initDevice = "large"
    else if (width < 910 && width >= 610) initDevice = "mid";
    else if (width < 610) initDevice = "tablet";
    setDevice(initDevice)
  }

  function convertDataObject() {
    let initProductGroupsArray = [];
    for (var i in window.data) {
      if (i !== "comments" && i !== "featureProducts"){
        const productGroup = {
          title:window.data[i].title,
          catIds:window.data[i].catIds,
          products:JSON.parse(window.data[i].products)
        }
        initProductGroupsArray.push(productGroup);
      }
    }
    setProductGroupsArray(initProductGroupsArray);
    setLoading(false);
  }


    let productCarouselsContainer;
    if (loading === false){
      productCarouselsContainer = productGroupsArray.map((pgc,index) => (
          <div key={index} className="section">
            <div className="container">
              <Carousel
                products={pgc.products}
                device={device}
                title={pgc.title}
                catIds={pgc.catIds}
                link={'/'}
                env={env}
              />
            </div>
          </div>
        )
      );
    }

  return (
    <div id="carousels-module">
      {productCarouselsContainer}
    </div>
  )
}

function Carousel(props){

  const products = props.products;
  const [ loading, setLoading ] = React.useState(true);
  const [ disableLeftArrow, setDisableLeftArrow ] = React.useState(true);
  const [ disableRightArrow, setDisableRightArrow ] = React.useState(false);
  const [ sliderWidth, setSliderWidth ] = React.useState();
  const [ sliderPosition, setSliderPosition ] = React.useState();
  const [ containerWidth, setContainerWidth ] = React.useState();
  const [ containerNumber, setContainerNumber ] = React.useState();
  const [ itemsPerRow, setItemsPerRow ] = React.useState();
  const [ itemWidth, setItemWidth ] = React.useState();


  React.useEffect(() => {
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

    console.log(device);

    let newItemsPerRow = 6;
    if (window.hpVersion === 2){
      if (device === 'large') newItemsPerRow = 6;
      else if (device === 'mid') newItemsPerRow = 4;
      else if (device === 'tablet') newItemsPerRow = 2;
    }

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
    if (products && products.length > 0){
      let plingedProduct = false;
      if (!props.catIds) plingedProduct = true;
      carouselItemsDisplay = products.map((product,index) => (
        <CarouselItem
          key={index}
          product={product}
          itemWidth={itemWidth}
          env={props.env}
          plingedProduct={plingedProduct}
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
      let itemHeightMultiplier;
      // if (itemWidth > 150){
        itemHeightMultiplier = 1.35;
      /*} else {
        itemHeightMultiplier = 1.85;
      }*/
      carouselWrapperStyling = {
        "paddingLeft":itemWidth / 2,
        "paddingRight":itemWidth / 2,
        "height":itemWidth * itemHeightMultiplier
      }
      carouselArrowsMargin = itemWidth / 4;
    }

    let urlSuffix='';
    if (window.page === "libreoffice"){
      urlSuffix = "/s/LibreOffice";
    }
    let titleLink = urlSuffix + "/browse/cat/" + props.catIds + "/";
    if (!props.catIds){
      titleLink = "/community#plingedproductsPanel";
    }else if(props.catIds.indexOf(',')>0){
      titleLink  = urlSuffix + "/browse/";
    }


    return (
      <div className={"product-carousel " + hpVersionClass}>
        <div className="product-carousel-header">
          <h2><a href={titleLink}>{props.title} <span className="glyphicon glyphicon-chevron-right"></span></a></h2>
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
    )
  
}

function CarouselItem(props){
    let paddingTop;
    let productInfoDisplay = (
      <div className="product-info">
        <span className="product-info-title">{props.product.title}</span>
        <span className="product-info-user">{props.product.username}</span>
      </div>
    );

    if (window.hpVersion === 2){

      if (props.itemWidth){
        paddingTop = ((props.itemWidth * 1.35) / 2) - 10;
      }

      let lastDate;
      if (props.product.changed_at){
        lastDate = props.product.changed_at;
      } else {
        lastDate = props.product.created_at;
      }

      let cDate = new Date(lastDate);
      // cDate = cDate.toString();
      // const createdDate = cDate.split(' ')[1] + " " + cDate.split(' ')[2] + " " + cDate.split(' ')[3];
      const timeAgo = new TimeAgo('en-US')
      const createdDate = timeAgo.format(cDate);
      // const productScoreColor = window.hpHelpers.calculateScoreColor(props.product.laplace_score);

      let infoDisplay;
      let scoreDisplay=(
          <div className="score-info">
            <div className="score-number">
              Score {(props.product.laplace_score/10).toFixed(1)}%
            </div>
            <div className="score-bar-container">
              <div className={"score-bar"} style={{"width":props.product.laplace_score/10 + "%"}}></div>
            </div>
          </div>
        );
      infoDisplay = scoreDisplay;


      if (props.plingedProduct){
        let plingDisplay = (
          <div className="plings">
            <img src="/images/system/pling-btn-active.png" />
            {props.product.sum_plings}
          </div>
        );

        infoDisplay=(
          <div>
            {plingDisplay}
            {scoreDisplay}
          </div>
        );
      }

      /*let scoreDisplay;
      if (props.plingedProduct){
        scoreDisplay = (
          <div className="score-info plings">
            <img src="/images/system/pling-btn-active.png" />
            {props.product.sum_plings}
          </div>
        );
      } else {
        scoreDisplay = (
          <div className="score-info">
            <div className="score-number">
              score {props.product.laplace_score + "%"}
            </div>
            <div className="score-bar-container">
              <div className={"score-bar"} style={{"width":props.product.laplace_score + "%"}}></div>
            </div>
          </div>
        );
      }*/

      productInfoDisplay = (
        <div className="product-info">
          <span className="product-info-title">{props.product.title}</span>
          <span className="product-info-category">{props.product.cat_title}</span>
          <span className="product-info-date">{createdDate}</span>
          {infoDisplay}
        </div>
      );
    }

    let projectUrl ="";
    if (window.page === "libreoffice") projectUrl = window.baseUrl +"p/"+props.product.project_id;
    else projectUrl = "/p/"+props.product.project_id;

    return (
      <div className="product-carousel-item" style={{"width":props.itemWidth}}>
        <div className="product-carousel-item-wrapper">
          <a href={projectUrl} style={{"paddingTop":paddingTop}}>
            <figure style={{"height":paddingTop}}>
              <img className="very-rounded-corners" src={props.product.image_small} />
            </figure>
            {productInfoDisplay}
          </a>
        </div>
      </div>
    )
}

export default CarouselsModule;