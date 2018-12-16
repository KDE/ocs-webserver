class App extends React.Component {
  constructor(props) {
    super(props);
    this.state = {
      loading: true,
      hpVersion: window.hpVersion
    };
    this.initHomePage = this.initHomePage.bind(this);
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
    this.initHomePage();
    console.log(window.data);
  }

  initHomePage() {

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
      productCarouselsContainer = this.state.productGroupsArray.map((pgc, index) => React.createElement(
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
      ));
    }

    const featuredProduct = JSON.parse(window.data['featureProducts']);

    return React.createElement(
      "main",
      { id: "opendesktop-homepage" },
      React.createElement(SpotlightProduct, {
        env: this.state.env,
        featuredProduct: featuredProduct
      }),
      productCarouselsContainer
    );
  }
}

class SpotlightProduct extends React.Component {
  constructor(props) {
    super(props);
    this.state = {
      featuredProduct: this.props.featuredProduct
    };
    this.onSpotlightMenuClick = this.onSpotlightMenuClick.bind(this);
  }

  onSpotlightMenuClick(val) {
    let url = "/home/showfeaturejson/page/";
    if (val === "random") {
      url += "0";
    } else {
      url += "1";
    }
    const self = this;
    $.ajax({ url: url, cache: false }).done(function (response) {
      self.setState({ featuredProduct: response });
    });
  }

  render() {

    let imageBaseUrl;
    if (this.props.env === 'live') {
      imageBaseUrl = 'cn.opendesktop.org';
    } else {
      imageBaseUrl = 'cn.opendesktop.cc';
    }

    let description = this.state.featuredProduct.description;
    if (description && description.length > 295) {
      description = this.state.featuredProduct.description.substring(0, 295) + "...";
    }

    let featuredLabelDisplay;
    if (this.state.featuredProduct.feature === "1") {
      featuredLabelDisplay = "featured";
    }

    return React.createElement(
      "div",
      { id: "spotlight-product" },
      React.createElement(
        "h2",
        null,
        "In the Spotlight"
      ),
      React.createElement(
        "div",
        { className: "container" },
        React.createElement(
          "div",
          { className: "spotlight-image" },
          React.createElement("img", { src: "https://" + imageBaseUrl + "/cache/300x230-1/img/" + this.state.featuredProduct.image_small })
        ),
        React.createElement(
          "div",
          { className: "spotlight-info" },
          React.createElement(
            "div",
            { className: "spotlight-info-wrapper" },
            React.createElement(
              "span",
              { className: "featured-label" },
              featuredLabelDisplay
            ),
            React.createElement(
              "div",
              { className: "info-top" },
              React.createElement(
                "h2",
                null,
                React.createElement(
                  "a",
                  { href: "/p/" + this.state.featuredProduct.project_id },
                  this.state.featuredProduct.title
                )
              ),
              React.createElement(
                "h3",
                null,
                this.state.featuredProduct.category
              ),
              React.createElement(
                "div",
                { className: "user-info" },
                React.createElement("img", { src: this.state.featuredProduct.profile_image_url }),
                this.state.featuredProduct.username
              ),
              React.createElement(
                "div",
                { className: "score-info" },
                React.createElement(
                  "div",
                  { className: "score-number" },
                  "score ",
                  this.state.featuredProduct.laplace_score + "%"
                ),
                React.createElement(
                  "div",
                  { className: "score-bar-container" },
                  React.createElement("div", { className: "score-bar", style: { "width": this.state.featuredProduct.laplace_score + "%" } })
                ),
                React.createElement("div", { className: "score-bar-date" })
              )
            ),
            React.createElement(
              "div",
              { className: "info-description" },
              description
            )
          ),
          React.createElement(
            "div",
            { className: "spotlight-menu" },
            React.createElement(
              "a",
              { onClick: () => this.onSpotlightMenuClick('random') },
              "random"
            ),
            React.createElement(
              "a",
              { onClick: () => this.onSpotlightMenuClick('featured') },
              "featured"
            )
          )
        )
      )
    );
  }
}

class ProductCarousel extends React.Component {
  constructor(props) {
    super(props);
    this.state = {
      products: this.props.products,
      offset: 5,
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
    this.updateDimensions();
  }

  updateDimensions(animateCarousel) {

    /*let itemsPerRow;
    if (this.props.device === 'large'){
      itemsPerRow = 5;
    } else if (this.props.device === 'mid'){
      itemsPerRow = 4;
    } else if (this.props.device === 'tablet'){
      itemsPerRow = 3;
    }*/

    const containerWidth = $('#main-content').width();
    const containerNumber = Math.ceil(this.state.products.length / 5);
    const sliderWidth = containerWidth * containerNumber;
    const itemWidth = containerWidth / 5;
    let sliderPosition = 0;
    if (this.state.sliderPosition) {
      sliderPosition = this.state.sliderPosition;
    }
    this.setState({
      sliderPosition: sliderPosition,
      containerWidth: containerWidth,
      sliderWidth: sliderWidth,
      itemWidth: itemWidth
    }, function () {
      if (animateCarousel) {
        this.animateProductCarousel('right', animateCarousel);
      }
    });
  }

  animateProductCarousel(dir, animateCarousel) {

    let newSliderPosition = this.state.sliderPosition;
    const endPoint = this.state.sliderWidth - this.state.containerWidth;

    if (dir === 'left') {
      if (this.state.sliderPosition > 0) {
        newSliderPosition = this.state.sliderPosition - this.state.containerWidth;
      }
    } else {
      if (this.state.sliderPosition < endPoint) {
        newSliderPosition = this.state.sliderPosition + this.state.containerWidth;
      } else {
        if (!animateCarousel) {
          this.getNextProductsBatch();
        }
      }
    }

    this.setState({ sliderPosition: newSliderPosition }, function () {

      let disableleftArrow = false;
      if (this.state.sliderPosition <= 0) {
        disableleftArrow = true;
      }

      let disableRightArrow = false;
      if (this.state.sliderPosition >= endPoint && this.state.finishedProducts === true) {
        disableRightArrow = true;
      }

      this.setState({ disableRightArrow: disableRightArrow, disableleftArrow: disableleftArrow });
    });
  }

  getNextProductsBatch() {
    let url = "/home/showlastproductsjson/?page=1&limit=5&offset=" + this.state.offset + "&catIDs=" + this.props.catIds + "&isoriginal=0";
    const self = this;
    $.ajax({ url: url, cache: false }).done(function (response) {
      const products = self.state.products.concat(response);
      const offset = self.state.offset + 5;
      let finishedProducts = false;
      if (response.length < 5) {
        finishedProducts = true;
      }
      self.setState({ products: products, offset: offset, finishedProducts: finishedProducts }, function () {
        const animateCarousel = true;
        self.updateDimensions(animateCarousel);
      });
    });
  }

  render() {
    console.log(window.hpVersion);
    let carouselItemsDisplay;
    if (this.state.products && this.state.products.length > 0) {
      carouselItemsDisplay = this.state.products.map((product, index) => React.createElement(ProductCarouselItem, {
        key: index,
        product: product,
        itemWidth: this.state.itemWidth,
        env: this.props.env
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
    if (window.hpVersion === 2) {
      hpVersion = "two";
    }

    return React.createElement(
      "div",
      { className: "product-carousel " + hpVersion },
      React.createElement(
        "div",
        { className: "product-carousel-header" },
        React.createElement(
          "h2",
          null,
          React.createElement(
            "a",
            { href: this.props.link },
            this.props.title,
            " ",
            React.createElement("span", { className: "glyphicon glyphicon-chevron-right" })
          )
        )
      ),
      React.createElement(
        "div",
        { className: "product-carousel-wrapper" },
        React.createElement(
          "div",
          { className: "product-carousel-left" },
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
          { className: "product-carousel-right" },
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
    console.log(this.props.product.image_small);
    let imageUrl = this.props.product.image_small;
    if (imageUrl && this.props.product.image_small.indexOf('https://') === -1 && this.props.product.image_small.indexOf('http://') === -1) {
      let imageBaseUrl;
      if (this.props.env === 'live') {
        imageBaseUrl = 'cn.opendesktop.org';
      } else {
        imageBaseUrl = 'cn.opendesktop.cc';
      }
      imageUrl = 'https://' + imageBaseUrl + '/cache/200x171/img/' + this.props.product.image_small;
    }

    return React.createElement(
      "div",
      { className: "product-carousel-item", style: { "width": this.props.itemWidth } },
      React.createElement(
        "div",
        { className: "product-carousel-item-wrapper" },
        React.createElement(
          "a",
          { href: "/p/" + this.props.product.project_id },
          React.createElement(
            "figure",
            null,
            React.createElement("img", { className: "very-rounded-corners", src: imageUrl })
          ),
          React.createElement(
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
          )
        )
      )
    );
  }
}

ReactDOM.render(React.createElement(App, null), document.getElementById('main-content'));
