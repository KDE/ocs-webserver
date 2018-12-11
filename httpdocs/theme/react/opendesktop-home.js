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
    console.log(window.featuredProduct);
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
      if (i !== "comments") {
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
        if (pgc.products.length > 0) {
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
        }
      });
    }

    return React.createElement(
      "main",
      { id: "opendesktop-homepage" },
      React.createElement(
        "div",
        { id: "featured-product" },
        React.createElement("div", { className: "container" })
      ),
      productCarouselsContainer
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
    const containerWidth = $('#main-content').width();
    console.log(containerWidth);
    const containerNumber = Math.ceil(this.props.products / 5);
    console.log(containerNumber);
    const sliderWidth = containerWidth * containerNumber;
    console.log(sliderWidth);
    const itemWidth = containerWidth / 5;
    console.log(itemWidth);
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
      newSliderPosition = this.state.sliderPosition - this.state.containerWidth;
    } else {
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
            this.props.title.split(/(?=[A-Z])/),
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
      imageBaseUrl = 'cn.pling.it';
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
