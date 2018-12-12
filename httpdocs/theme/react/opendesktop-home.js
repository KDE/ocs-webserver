class App extends React.Component {
  constructor(props) {
    super(props);
    this.state = {
      loading: true
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
          title: i,
          products: JSON.parse(window.data[i])
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
        pgc.products = pgc.products.concat(pgc.products);
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
              link: '/',
              env: this.state.env
            })
          )
        );
      });
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
    this.state = {};
  }

  componentDidMount() {
    console.log(this.props.featuredProduct);
  }

  render() {

    let imageBaseUrl;
    if (this.props.env === 'live') {
      imageBaseUrl = 'cn.opendesktop.org';
    } else {
      imageBaseUrl = 'cn.opendesktop.cc';
    }

    let description = this.props.featuredProduct.description;
    if (description.length > 295) {
      description = this.props.featuredProduct.description.substring(0, 295) + "...";
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
          React.createElement("img", { src: "https://" + imageBaseUrl + "/cache/300x230-1/img/" + this.props.featuredProduct.image_small })
        ),
        React.createElement(
          "div",
          { className: "spotlight-info" },
          React.createElement(
            "span",
            { className: "featured-label" },
            "Featured"
          ),
          React.createElement(
            "div",
            { className: "info-top" },
            React.createElement(
              "h2",
              null,
              React.createElement(
                "a",
                { href: "/p/" + this.props.featuredProduct.project_id },
                this.props.featuredProduct.title
              )
            ),
            React.createElement(
              "h3",
              null,
              this.props.featuredProduct.category
            ),
            React.createElement(
              "div",
              { className: "user-info" },
              React.createElement("img", { src: this.props.featuredProduct.profile_image_url }),
              this.props.featuredProduct.username
            ),
            React.createElement(
              "div",
              { className: "score-info" },
              React.createElement(
                "div",
                { className: "score-number" },
                "score ",
                this.props.featuredProduct.laplace_score + "%"
              ),
              React.createElement(
                "div",
                { className: "score-bar-container" },
                React.createElement("div", { className: "score-bar", style: { "width": this.props.featuredProduct.laplace_score + "%" } })
              ),
              React.createElement("div", { className: "score-bar-date" })
            )
          ),
          React.createElement(
            "div",
            { className: "info-description" },
            description
          )
        )
      )
    );
  }
}

class ProductCarousel extends React.Component {
  constructor(props) {
    super(props);
    let showRightArrow = false;
    if (this.props.products.length > 5) {
      showRightArrow = true;
    }
    this.state = {
      showRightArrow: showRightArrow,
      showLeftArrow: false
    };
    this.updateDimensions = this.updateDimensions.bind(this);
    this.animateProductCarousel = this.animateProductCarousel.bind(this);
  }

  componentWillMount() {
    window.addEventListener("resize", this.updateDimensions);
  }

  componentDidMount() {
    this.updateDimensions();
  }

  updateDimensions() {

    let itemsPerRow;
    if (this.props.device === 'large') {
      itemsPerRow = 5;
    } else if (this.props.device === 'mid') {
      itemsPerRow = 4;
    } else if (this.props.device === 'tablet') {
      itemsPerRow = 3;
    }

    const containerWidth = $('#main-content').width();
    const containerNumber = Math.ceil(this.props.products / 5);
    const sliderWidth = containerWidth * containerNumber;
    const itemWidth = containerWidth / 5;
    this.setState({
      sliderPosition: 0,
      containerWidth: containerWidth,
      sliderWidth: sliderWidth,
      itemWidth: itemWidth
    });
  }

  animateProductCarousel(dir) {

    let newSliderPosition = this.state.sliderPosition;
    if (dir === 'left') {
      console.log(this.state.sliderPosition);
      newSliderPosition = this.state.sliderPosition - this.state.containerWidth;
    } else {
      console.log(this.state.sliderPosition);
      newSliderPosition = this.state.sliderPosition + this.state.containerWidth;
    }

    this.setState({ sliderPosition: newSliderPosition }, function () {

      let showLeftArrow = true,
          showRightArrow = true;
      const endPoint = this.state.sliderWidth - this.state.containerWidth;
      if (this.state.sliderPosition <= 0) {
        showLeftArrow = false;
      }
      if (this.state.sliderPosition >= endPoint) {
        showRightArrow = false;
      }

      this.setState({
        showLeftArrow: showLeftArrow,
        showRightArrow: showRightArrow
      });
    });
  }

  render() {

    let carouselItemsDisplay;
    if (this.props.products && this.props.products.length > 0) {
      carouselItemsDisplay = this.props.products.map((product, index) => React.createElement(ProductCarouselItem, {
        key: index,
        product: product,
        itemWidth: this.state.itemWidth,
        env: this.props.env
      }));
    }

    let rightArrowDisplay, leftArrowDisplay;
    if (this.state.showLeftArrow) {
      leftArrowDisplay = React.createElement(
        "div",
        { className: "product-carousel-left" },
        React.createElement(
          "a",
          { onClick: () => this.animateProductCarousel('left'), className: "carousel-arrow arrow-left" },
          React.createElement("span", { className: "glyphicon glyphicon-chevron-left" })
        )
      );
    }
    if (this.state.showRightArrow) {
      rightArrowDisplay = React.createElement(
        "div",
        { className: "product-carousel-right" },
        React.createElement(
          "a",
          { onClick: () => this.animateProductCarousel('right'), className: "carousel-arrow arrow-right" },
          React.createElement("span", { className: "glyphicon glyphicon-chevron-right" })
        )
      );
    }

    return React.createElement(
      "div",
      { className: "product-carousel" },
      React.createElement(
        "div",
        { className: "product-carousel-header" },
        React.createElement(
          "h2",
          null,
          React.createElement(
            "a",
            { href: this.props.link },
            this.props.title.match(/[A-Z][a-z]+/g).join(' '),
            " ",
            React.createElement("span", { className: "glyphicon glyphicon-chevron-right" })
          )
        )
      ),
      React.createElement(
        "div",
        { className: "product-carousel-wrapper" },
        leftArrowDisplay,
        React.createElement(
          "div",
          { className: "product-carousel-container" },
          React.createElement(
            "div",
            { className: "product-carousel-slider", style: { "width": this.state.sliderWidth, "left": "-" + this.state.sliderPosition + "px" } },
            carouselItemsDisplay
          )
        ),
        rightArrowDisplay
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
    let imageBaseUrl;
    if (this.props.env === 'live') {
      imageBaseUrl = 'cn.opendesktop.org';
    } else {
      imageBaseUrl = 'cn.opendesktop.cc';
    }
    return React.createElement(
      "div",
      { className: "product-carousel-item", style: { "width": this.props.itemWidth } },
      React.createElement(
        "a",
        { href: "/p/" + this.props.product.project_id },
        React.createElement(
          "figure",
          null,
          React.createElement("img", { className: "very-rounded-corners", src: 'https://' + imageBaseUrl + '/cache/200x171/img/' + this.props.product.image_small })
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
    );
  }
}

ReactDOM.render(React.createElement(App, null), document.getElementById('main-content'));
