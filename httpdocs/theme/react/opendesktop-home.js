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

class App extends React.Component {
  constructor(props) {
    super(props);
    this.state = {
      loading: true,
      hpVersion: window.hpVersion
    };
    this.initHomePage = this.initHomePage.bind(this);
    this.updateDimensions = this.updateDimensions.bind(this);
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

    this.setState({ env: env });
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

  render() {
    const featuredProduct = JSON.parse(window.data['featureProducts']);
    return React.createElement(
      "main",
      { id: "opendesktop-homepage" },
      React.createElement(SpotlightProduct, {
        env: this.state.env,
        device: this.state.device,
        featuredProduct: featuredProduct
      })
    );
  }
}

class SpotlightProduct extends React.Component {
  constructor(props) {
    super(props);
    this.state = {
      featuredProduct: this.props.featuredProduct,
      type: "featured",
      page: 0
    };
    this.onSpotlightMenuClick = this.onSpotlightMenuClick.bind(this);
  }

  onSpotlightMenuClick(val) {
    this.setState({ loading: true, type: val }, function () {
      let url = "/home/showfeaturejson/page/";
      if (this.state.type === "plinged") {
        url = "/home/getnewactiveplingedproductjson/page/";
        page = this.state.page + 1;
      }
      if (val === "random") {
        url += "0";
      } else {
        url += "1";
      }
      const self = this;
      $.ajax({ url: url, cache: false }).done(function (response) {
        console.log(response);
        self.setState({ featuredProduct: response, page: page });
      });
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
    if (this.state.type === "featured") {
      featuredLabelDisplay = React.createElement(
        "span",
        { className: "featured-label" },
        "featured"
      );
    } else if (this.state.type === "plinged") {
      featuredLabelDisplay = React.createElement(
        "span",
        { className: "featured-label plinged" },
        "plinged"
      );
    }

    let cDate = new Date(this.state.featuredProduct.created_at);
    cDate = cDate.toString();
    const createdDate = cDate.split(' ')[1] + " " + cDate.split(' ')[2] + " " + cDate.split(' ')[3];
    // const productScoreColor = window.hpHelpers.calculateScoreColor(this.state.featuredProduct.laplace_score);

    let loadingContainerDisplay;
    if (this.state.loading) {
      loadingContainerDisplay = React.createElement(
        "div",
        { className: "loading-container" },
        React.createElement("div", { className: "ajax-loader" })
      );
    }

    let commentCount;
    if (this.state.featuredProduct.comment_count) {
      commentCount = this.state.featuredProduct.comment_count;
    }
    commentCount = "0";

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
          React.createElement("img", { className: "product-image", src: "https://" + imageBaseUrl + "/cache/300x230-1/img/" + this.state.featuredProduct.image_small }),
          React.createElement(
            "figure",
            { className: "user-avatar" },
            React.createElement("img", { src: this.state.featuredProduct.profile_image_url })
          )
        ),
        React.createElement(
          "div",
          { className: "spotlight-info" },
          React.createElement(
            "div",
            { className: "spotlight-info-wrapper" },
            featuredLabelDisplay,
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
                "span",
                null,
                this.state.featuredProduct.comment_count,
                " comments"
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
                React.createElement(
                  "div",
                  { className: "score-bar-date" },
                  createdDate
                )
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
            ),
            React.createElement(
              "a",
              { onClick: () => this.onSpotlightMenuClick('plinged') },
              "plinged"
            )
          )
        )
      )
    );
  }
}

class SpotlightUser extends React.Component {
  constructor(props) {
    super(props);
    this.state = {
      loading: true,
      version: 2
    };
    this.updateDimensions = this.updateDimensions.bind(this);
    this.getSpotlightUser = this.getSpotlightUser.bind(this);
  }

  componentWillMount() {
    window.addEventListener("resize", this.updateDimensions);
  }

  componentDidMount() {
    this.updateDimensions();
    this.getSpotlightUser();
  }

  updateDimensions() {
    const containerWidth = $('#main-content').width();
    const userProductsPerRow = 4;
    const userProductsDimensions = containerWidth / userProductsPerRow;
    this.setState({ itemWidth: userProductsDimensions, itemHeight: userProductsDimensions });
  }

  getSpotlightUser(page) {
    if (!page) {
      page = 0;
    }
    this.setState({ loading: true, page: page }, function () {
      let url = "/home/showspotlightjson?page=" + this.state.page;
      const self = this;
      $.ajax({ url: url, cache: false }).done(function (response) {
        self.setState({ user: response, loading: false }, function () {
          const height = $('#user-container').height();
          if (height > 0) {
            this.setState({ containerHeight: height });
          }
        });
      });
    });
  }

  render() {

    let spotlightUserDisplay;
    if (this.state.loading) {
      let loadingStyle;
      if (this.state.containerHeight) {
        loadingStyle = {
          "height": this.state.containerHeight
        };
      }
      spotlightUserDisplay = React.createElement(
        "div",
        { className: "loading-container", style: loadingStyle },
        React.createElement("div", { className: "ajax-loader" })
      );
    } else {
      let userProducts;
      if (this.state.itemWidth) {
        userProducts = this.state.user.products.map((p, index) => React.createElement(SpotlightUserProduct, {
          key: index,
          itemHeight: this.state.itemHeight,
          itemWidth: this.state.itemWidth,
          product: p
        }));
      }
      spotlightUserDisplay = React.createElement(
        "div",
        { id: "spotlight-user" },
        React.createElement(
          "div",
          { className: "user-container" },
          React.createElement(
            "figure",
            null,
            React.createElement("img", { src: this.state.user.profile_image_url })
          ),
          React.createElement(
            "h2",
            null,
            React.createElement(
              "a",
              { href: "/u/" + this.state.user.username },
              this.state.user.username
            )
          )
        ),
        React.createElement(
          "div",
          { className: "products-container" },
          userProducts
        )
      );
    }

    let prevButtonDisplay;
    if (this.state.page > 0) {
      prevButtonDisplay = React.createElement(
        "a",
        { onClick: () => this.getSpotlightUser(this.state.page - 1), className: "spotlight-user-next" },
        "< Prev"
      );
    }

    let nextButtonDisplay;
    if (this.state.page < 8) {
      nextButtonDisplay = React.createElement(
        "a",
        { onClick: () => this.getSpotlightUser(this.state.page + 1), className: "spotlight-user-next" },
        "Next >"
      );
    }

    let versionCssClass;
    if (this.state.version === 2) {
      versionCssClass = "v-two";
    }

    return React.createElement(
      "div",
      { id: "spotlight-user-container", className: versionCssClass },
      React.createElement(
        "h2",
        null,
        "In the Spotlight"
      ),
      spotlightUserDisplay,
      React.createElement(
        "div",
        { className: "spotlight-user-buttons" },
        prevButtonDisplay,
        nextButtonDisplay
      )
    );
  }
}

class SpotlightUserProduct extends React.Component {
  constructor(props) {
    super(props);
    this.state = {};
  }

  componentDidMount() {
    console.log(this.props);
  }

  render() {
    let userProductStyle;
    if (this.props.itemWidth) {
      userProductStyle = {
        "height": this.props.itemHeight,
        "width": this.props.itemWidth
      };
    }
    return React.createElement(
      "div",
      { style: userProductStyle, className: "spotlight-user-product" },
      React.createElement(
        "figure",
        null,
        React.createElement("img", { src: this.props.product.image_small })
      ),
      React.createElement(
        "div",
        { className: "product-title-overlay" },
        React.createElement(
          "div",
          { className: "product-title" },
          this.props.product.title
        )
      ),
      React.createElement(
        "div",
        { className: "product-plings-counter" },
        React.createElement("img", { src: "/images/system/pling-btn-active.png" }),
        React.createElement(
          "span",
          null,
          this.props.product.sum_plings
        )
      )
    );
  }
}

ReactDOM.render(React.createElement(App, null), document.getElementById('main-content'));
