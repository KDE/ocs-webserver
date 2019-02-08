window.hpHelpers = function () {

  function dechex(number) {
    //  discuss at: http://locutus.io/php/dechex/
    // original by: Philippe Baumann
    // bugfixed by: Onno Marsman (https://twitter.com/onnomarsman)
    // improved by: http://stackoverflow.com/questions/57803/how-to-convert-decimal-to-hex-in-javascript
    //    input by: pilus
    //   example 1: dechex(10)
    //   returns 1: 'a'
    //   example 2: dechex(47)
    //   returns 2: '2f'
    //   example 3: dechex(-1415723993)
    //   returns 3: 'ab9dc427'

    if (number < 0) {
      number = 0xFFFFFFFF + number + 1;
    }
    return parseInt(number, 10).toString(16);
  }

  function calculateScoreColor(score) {
    let blue,
        red,
        green,
        defaultColor = 200;
    if (score > 50) {
      red = defaultColor - (score - 50) * 4;
      green = defaultColor;
      blue = defaultColor - (score - 50) * 4;
    } else if (score < 51) {
      red = defaultColor;
      green = defaultColor - (score - 50) * 4;
      blue = defaultColor - (score - 50) * 4;
    }

    return "rgb(" + red + "," + green + "," + blue + ")";
  }

  return {
    dechex,
    calculateScoreColor
  };
}();

class CarouselsModule extends React.Component {
  constructor(props) {
    super(props);
    this.state = {};
    this.initCarouselModule = this.initCarouselModule.bind(this);
    this.updateDimensions = this.updateDimensions.bind(this);
    this.convertDataObject = this.convertDataObject.bind(this);
  }

  componentWillMount() {
    this.updateDimensions();
  }

  componentWillUnmount() {
    window.removeEventListener("resize", this.updateDimensions);
    window.removeEventListener("orientationchange", this.updateDimensions);
  }

  componentDidMount() {
    console.log('component did mount');
    this.initCarouselModule();
  }

  initCarouselModule() {

    window.addEventListener("resize", this.updateDimensions);
    window.addEventListener("orientationchange", this.updateDimensions);

    let env = "live";
    if (location.hostname.endsWith('cc')) {
      env = "test";
    } else if (location.hostname.endsWith('localhost')) {
      env = "test";
    }

    this.setState({ env: env }, function () {
      this.convertDataObject();
    });
  }

  updateDimensions() {

    const width = window.innerWidth;
    let device;
    if (width >= 910) {
      device = "large";
    } else if (width < 910 && width >= 610) {
      device = "mid";
    } else if (width < 610) {
      device = "tablet";
    }

    this.setState({ device: device });
  }

  convertDataObject() {
    let productGroupsArray = [];
    for (var i in window.data) {
      if (i !== "comments" && i !== "featureProducts") {
        const productGroup = {
          title: window.data[i].title,
          catIds: window.data[i].catIds,
          products: JSON.parse(window.data[i].products)
        };
        productGroupsArray.push(productGroup);
      }
    }
    this.setState({ productGroupsArray: productGroupsArray, loading: false });
  }

  render() {

    let productCarouselsContainer;
    if (this.state.loading === false) {
      productCarouselsContainer = this.state.productGroupsArray.map((pgc, index) => {
        if (pgc.catIds) {
          return React.createElement(
            "div",
            { key: index, className: "section" },
            React.createElement(
              "div",
              { className: "container" },
              React.createElement(ProductCarousel, {
                products: pgc.products,
                device: this.state.device,
                title: pgc.title,
                catIds: pgc.catIds,
                link: '/',
                env: this.state.env
              })
            )
          );
        }
      });
    }

    return React.createElement(
      "div",
      { id: "carousels-module" },
      productCarouselsContainer
    );
  }
}

class ProductCarousel extends React.Component {
  constructor(props) {
    super(props);
    this.state = {
      products: this.props.products,
      disableleftArrow: true
    };
    this.updateDimensions = this.updateDimensions.bind(this);
    this.animateProductCarousel = this.animateProductCarousel.bind(this);
    this.getNextProductsBatch = this.getNextProductsBatch.bind(this);
  }

  componentWillMount() {
    window.addEventListener("resize", this.updateDimensions);
  }

  componentDidMount() {
    console.log(this.props);
    this.updateDimensions();
  }

  updateDimensions(animateCarousel) {
    let itemsPerRow = 5;
    if (window.hpVersion === 2) {
      if (this.props.device === 'large') {
        itemsPerRow = 6;
      } else if (this.props.device === 'mid') {
        itemsPerRow = 5;
      } else if (this.props.device === 'tablet') {
        itemsPerRow = 2;
      }
    }

    const containerWidth = $('#main-content').width();
    const containerNumber = Math.ceil(this.state.products.length / (itemsPerRow - 1));
    const itemWidth = containerWidth / itemsPerRow;
    const sliderWidth = (containerWidth - itemWidth) * containerNumber;
    let sliderPosition = 0;
    if (this.state.sliderPosition) {
      sliderPosition = this.state.sliderPosition;
    }
    this.setState({
      sliderPosition: sliderPosition,
      containerWidth: containerWidth,
      containerNumber: containerNumber,
      sliderWidth: sliderWidth,
      itemWidth: itemWidth,
      itemsPerRow: itemsPerRow - 1
    }, function () {
      if (animateCarousel) {
        this.animateProductCarousel('right', animateCarousel);
      } else if (this.state.finishedProducts) {
        this.setState({ disableRightArrow: true });
      }
    });
  }

  animateProductCarousel(dir, animateCarousel) {
    let newSliderPosition = this.state.sliderPosition;
    const endPoint = this.state.sliderWidth - (this.state.containerWidth - this.state.itemWidth);

    if (dir === 'left') {
      if (this.state.sliderPosition > 0) {
        newSliderPosition = this.state.sliderPosition - (this.state.containerWidth - this.state.itemWidth);
      }
    } else {
      if (Math.trunc(this.state.sliderPosition) < Math.trunc(endPoint)) {
        newSliderPosition = this.state.sliderPosition + (this.state.containerWidth - this.state.itemWidth);
      } else {
        newSliderPosition = 0;
        /*if (!animateCarousel){
        if (this.state.products.length >= 15 || this.state.finishedProducts){
            newSliderPosition = 0;
          } else {
            this.getNextProductsBatch();
          }
        }*/
      }
    }

    this.setState({ sliderPosition: newSliderPosition }, function () {

      let disableleftArrow = false;
      if (this.state.sliderPosition <= 0) {
        disableleftArrow = true;
      }

      let disableRightArrow = false;
      /*if (this.state.sliderPosition >= endPoint && this.state.finishedProducts === true){
        disableRightArrow = true;
      }*/

      this.setState({ disableRightArrow: disableRightArrow, disableleftArrow: disableleftArrow });
    });
  }

  getNextProductsBatch() {

    this.setState({ disableRightArrow: true }, function () {
      let limit = this.state.itemsPerRow * (this.state.containerNumber + 1) - this.state.products.length;
      if (limit <= 0) {
        limit = this.state.itemsPerRow;
      }

      let url;
      if (!this.props.catIds) {
        url = "/home/getnewactiveplingedproductjson/?limit=" + limit + "&offset=" + this.state.offset;
      } else {
        url = "/home/showlastproductsjson/?page=1&limit=" + limit + "&offset=" + this.state.offset + "&catIDs=" + this.props.catIds + "&isoriginal=0";
      }

      const self = this;
      $.ajax({ url: url, cache: false }).done(function (response) {
        let products = self.state.products,
            finishedProducts = false,
            animateCarousel = true;

        if (response.length > 0) {
          products = products.concat(response);
        } else {
          finishedProducts = true;
          animateCarousel = false;
        }

        if (response.length < limit) {
          finishedProducts = true;
        }

        self.setState({
          products: products,
          offset: self.state.offset + response.length,
          finishedProducts: finishedProducts }, function () {
          self.updateDimensions(animateCarousel);
        });
      });
    });
  }

  render() {
    let carouselItemsDisplay;
    if (this.state.products && this.state.products.length > 0) {
      let plingedProduct = false;
      if (!this.props.catIds) plingedProduct = true;
      carouselItemsDisplay = this.state.products.map((product, index) => React.createElement(ProductCarouselItem, {
        key: index,
        product: product,
        itemWidth: this.state.itemWidth,
        env: this.props.env,
        plingedProduct: plingedProduct
      }));
    }

    let carouselArrowLeftDisplay;
    if (this.state.disableleftArrow) {
      carouselArrowLeftDisplay = React.createElement(
        "a",
        { className: "carousel-arrow arrow-left disabled" },
        React.createElement("span", { className: "glyphicon glyphicon-chevron-left" })
      );
    } else {
      carouselArrowLeftDisplay = React.createElement(
        "a",
        { onClick: () => this.animateProductCarousel('left'), className: "carousel-arrow arrow-left" },
        React.createElement("span", { className: "glyphicon glyphicon-chevron-left" })
      );
    }

    let carouselArrowRightDisplay;
    if (this.state.disableRightArrow) {
      carouselArrowRightDisplay = React.createElement(
        "a",
        { className: "carousel-arrow arrow-right disabled" },
        React.createElement("span", { className: "glyphicon glyphicon-chevron-right" })
      );
    } else {
      carouselArrowRightDisplay = React.createElement(
        "a",
        { onClick: () => this.animateProductCarousel('right'), className: "carousel-arrow arrow-right" },
        React.createElement("span", { className: "glyphicon glyphicon-chevron-right" })
      );
    }

    let hpVersionClass = "one";
    let carouselWrapperStyling = {};
    let carouselArrowsMargin;
    if (window.hpVersion === 2 && this.state.itemWidth) {
      hpVersionClass = "two";
      let itemHeightMultiplier;
      // if (this.state.itemWidth > 150){
      itemHeightMultiplier = 1.35;
      /*} else {
        itemHeightMultiplier = 1.85;
      }*/
      carouselWrapperStyling = {
        "paddingLeft": this.state.itemWidth / 2,
        "paddingRight": this.state.itemWidth / 2,
        "height": this.state.itemWidth * itemHeightMultiplier
      };
      carouselArrowsMargin = this.state.itemWidth / 4;
    }

    let titleLink = "/browse/cat/" + this.props.catIds + "/";
    if (!this.props.catIds) {
      titleLink = "/community#plingedproductsPanel";
    }

    return React.createElement(
      "div",
      { className: "product-carousel " + hpVersionClass },
      React.createElement(
        "div",
        { className: "product-carousel-header" },
        React.createElement(
          "h2",
          null,
          React.createElement(
            "a",
            { href: titleLink },
            this.props.title,
            " ",
            React.createElement("span", { className: "glyphicon glyphicon-chevron-right" })
          )
        )
      ),
      React.createElement(
        "div",
        { className: "product-carousel-wrapper", style: carouselWrapperStyling },
        React.createElement(
          "div",
          { className: "product-carousel-left", style: { "width": carouselArrowsMargin, "left": "0" } },
          carouselArrowLeftDisplay
        ),
        React.createElement(
          "div",
          { className: "product-carousel-container" },
          React.createElement(
            "div",
            { className: "product-carousel-slider", style: { "width": this.state.sliderWidth, "left": "-" + this.state.sliderPosition + "px" } },
            carouselItemsDisplay
          )
        ),
        React.createElement(
          "div",
          { className: "product-carousel-right", style: { "width": carouselArrowsMargin, "right": "0" } },
          carouselArrowRightDisplay
        )
      )
    );
  }
}

class ProductCarouselItem extends React.Component {
  constructor(props) {
    super(props);
    this.state = {};
  }

  render() {

    let paddingTop;
    let productInfoDisplay = React.createElement(
      "div",
      { className: "product-info" },
      React.createElement(
        "span",
        { className: "product-info-title" },
        this.props.product.title
      ),
      React.createElement(
        "span",
        { className: "product-info-user" },
        this.props.product.username
      )
    );

    if (window.hpVersion === 2) {

      paddingTop = this.props.itemWidth * 1.35 / 2 - 10;
      let lastDate;
      if (this.props.product.changed_at) {
        lastDate = this.props.product.changed_at;
      } else {
        lastDate = this.props.product.created_at;
      }

      let cDate = new Date(lastDate);
      cDate = cDate.toString();
      const createdDate = cDate.split(' ')[1] + " " + cDate.split(' ')[2] + " " + cDate.split(' ')[3];
      // const createdDate = jQuery.timeago(cDate)
      const productScoreColor = window.hpHelpers.calculateScoreColor(this.props.product.laplace_score);

      let scoreDisplay;
      if (this.props.plingedProduct) {
        scoreDisplay = React.createElement(
          "div",
          { className: "score-info plings" },
          React.createElement("img", { src: "/images/system/pling-btn-active.png" }),
          this.props.product.sum_plings
        );
      } else {
        scoreDisplay = React.createElement(
          "div",
          { className: "score-info" },
          React.createElement(
            "div",
            { className: "score-number" },
            "score ",
            this.props.product.laplace_score + "%"
          ),
          React.createElement(
            "div",
            { className: "score-bar-container" },
            React.createElement("div", { className: "score-bar", style: { "width": this.props.product.laplace_score + "%", "backgroundColor": productScoreColor } })
          )
        );
      }

      productInfoDisplay = React.createElement(
        "div",
        { className: "product-info" },
        React.createElement(
          "span",
          { className: "product-info-title" },
          this.props.product.title
        ),
        React.createElement(
          "span",
          { className: "product-info-category" },
          this.props.product.cat_title
        ),
        React.createElement(
          "span",
          { className: "product-info-date" },
          createdDate
        ),
        React.createElement(
          "span",
          { className: "product-info-comments" },
          this.props.comment_count,
          " comments"
        ),
        scoreDisplay
      );
    }

    return React.createElement(
      "div",
      { className: "product-carousel-item", style: { "width": this.props.itemWidth } },
      React.createElement(
        "div",
        { className: "product-carousel-item-wrapper" },
        React.createElement(
          "a",
          { href: "/p/" + this.props.product.project_id, style: { "paddingTop": paddingTop } },
          React.createElement(
            "figure",
            { style: { "height": paddingTop } },
            React.createElement("img", { className: "very-rounded-corners", src: this.props.product.image_small })
          ),
          productInfoDisplay
        )
      )
    );
  }
}

ReactDOM.render(React.createElement(CarouselsModule, null), document.getElementById('carousel-module-container'));
