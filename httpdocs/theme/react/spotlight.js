class SpotlightUser extends React.Component {
  constructor(props) {
    super(props);
    this.state = {
      page: 1,
      loading: true,
      version: 2
    };
    this.initSpotLight = this.initSpotLight.bind(this);
    this.updateDimensions = this.updateDimensions.bind(this);
    this.getSpotlightUser = this.getSpotlightUser.bind(this);
    this.getNextSpotLightUser = this.getNextSpotLightUser.bind(this);
  }

  componentDidMount() {
    this.initSpotLight();
  }

  initSpotLight() {
    window.addEventListener("resize", this.updateDimensions);
    window.addEventListener("orientationchange", this.updateDimensions);
    this.getSpotlightUser();
  }

  updateDimensions() {
    console.log($('.content').width());
    console.log($('.sidebar-left').width());
  }

  getSpotlightUser() {
    const self = this;
    $.ajax({ url: "/home/showspotlightjson?page=" + this.state.page, cache: false }).done(function (response) {
      self.setState({ user: response, loading: false });
    });
  }

  getNextSpotLightUser() {
    const page = this.state.page + 1;
    this.setState({ page: page, loading: true }, function () {
      this.getSpotlightUser();
    });
  }

  render() {
    let spotlightUserDisplay;
    if (this.state.loading) {
      spotlightUserDisplay = React.createElement(
        "div",
        { id: "spotlight-user", className: "loading" },
        React.createElement("div", { className: "ajax-loader" })
      );
    } else {
      const users = this.state.user.products.map((p, index) => React.createElement(
        "div",
        { key: index, className: "plinged-product" },
        React.createElement(
          "div",
          { className: "product-wrapper" },
          React.createElement(
            "figure",
            null,
            React.createElement("img", { src: p.image_small })
          ),
          React.createElement(
            "div",
            { className: "product-info" },
            React.createElement(
              "span",
              { className: "title" },
              React.createElement(
                "a",
                { href: "/p/" + p.project_id },
                p.title
              )
            )
          )
        )
      ));

      let productsContainerCssClass;
      if (this.state.version === 1) {
        if (this.state.user.products.length === 2) {
          productsContainerCssClass = "one-row";
        } else if (this.state.user.products.length === 1) {
          productsContainerCssClass = "one-row single-product";
        }
      }

      spotlightUserDisplay = React.createElement(
        "div",
        { id: "spotlight-user" },
        React.createElement(
          "div",
          { className: "spotlight-user-image" },
          React.createElement(
            "figure",
            null,
            React.createElement("img", { src: this.state.user.profile_image_url })
          ),
          React.createElement(
            "div",
            { className: "user-info" },
            React.createElement(
              "span",
              { className: "username" },
              React.createElement(
                "a",
                { href: "/u/" + this.state.user.username },
                this.state.user.username
              )
            ),
            React.createElement(
              "span",
              { className: "user-plings" },
              React.createElement("img", { src: "/images/system/pling-btn-active.png" }),
              this.state.user.cnt
            )
          )
        ),
        React.createElement(
          "div",
          { className: "spotlight-user-plinged-products" + " " + productsContainerCssClass },
          users,
          React.createElement(
            "a",
            { className: "next-button", onClick: this.getNextSpotLightUser },
            "next"
          )
        )
      );
    }

    let versionClassCss;
    if (this.state.version === 2) {
      versionClassCss = "version-two";
    } else if (this.state.version === 3) {
      versionClassCss = "version-three";
    }

    return React.createElement(
      "div",
      { id: "spotlight-user-container", className: versionClassCss },
      React.createElement(
        "h2",
        null,
        "In the spotlight"
      ),
      spotlightUserDisplay
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
    if (this.state.featuredProduct.featured === "1") {
      featuredLabelDisplay = React.createElement(
        "span",
        { className: "featured-label" },
        "featured"
      );
    }

    const cDate = new Date(this.props.featuredProduct.changed_at);
    const createdDate = jQuery.timeago(cDate);
    const productScoreColor = window.hpHelpers.calculateScoreColor(this.props.featuredProduct.laplace_score);

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
          React.createElement("img", { src: this.state.featuredProduct.image_small })
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
                  React.createElement("div", { className: "score-bar", style: { "width": this.state.featuredProduct.laplace_score + "%", "backgroundColor": productScoreColor } })
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
            )
          )
        )
      )
    );
  }
}

ReactDOM.render(React.createElement(SpotlightUser, null), document.getElementById('spotlight-container'));
