class App extends React.Component {
  constructor(props) {
    super(props);
    this.state = {
      loading: true
    };
    this.convertDataObject = this.convertDataObject.bind(this);
  }

  componentDidMount() {
    console.log('opendesktop app homepage');
    this.convertDataObject();
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
    console.log(productGroupsArray);
  }

  render() {
    let productCarouselsContainer;
    if (this.state.loading === false) {
      productCarouselsContainer = React.createElement(
        "div",
        { id: "product-carousels-container" },
        React.createElement(
          "div",
          { className: "section" },
          React.createElement(
            "div",
            { className: "container" },
            React.createElement(ProductCarousel, {
              products: this.state.products.LatestProducts,
              device: this.state.device,
              title: 'New',
              link: '/browse/ord/latest/'
            })
          )
        ),
        React.createElement(
          "div",
          { className: "section" },
          React.createElement(
            "div",
            { className: "container" },
            React.createElement(ProductCarousel, {
              products: this.state.products.LatestProducts,
              device: this.state.device,
              title: 'New',
              link: '/browse/ord/latest/'
            })
          )
        )
      );
    }

    return React.createElement("main", { id: "opendesktop-homepage" });
  }
}

class ProductCarousel extends React.Component {
  constructor(props) {
    super(props);
    this.state = {
      showRightArrow: true,
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
    const containerWidth = $('#introduction').find('.container').width();
    const sliderWidth = containerWidth * 3;
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
        itemWidth: this.state.itemWidth
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
          React.createElement(
            "i",
            { className: "material-icons" },
            "chevron_left"
          )
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
          React.createElement(
            "i",
            { className: "material-icons" },
            "chevron_right"
          )
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
            this.props.title,
            React.createElement(
              "i",
              { className: "material-icons" },
              "chevron_right"
            )
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
    if (store.getState().env === 'live') {
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
