window.appHelpers = function () {

  function getEnv(domain) {

    let env;
    let lastDotSplit = this.splitByLastDot(domain);

    if (lastDotSplit.indexOf('/') > -1) {
      lastDotSplit = lastDotSplit.split('/')[0];
    }

    if (lastDotSplit === 'com' || lastDotSplit === 'org') {
      env = 'live';
    } else {
      env = 'test';
    }
    return env;
  }

  function splitByLastDot(text) {
    var index = text.lastIndexOf('.');
    return text.slice(index + 1);
  }

  return {
    getEnv,
    splitByLastDot
  };
}();
class SiteHeader extends React.Component {
  constructor(props) {
    super(props);
    this.state = {
      baseUrl: window.json_baseurl,
      cat_title: window.json_cat_title,
      hasIdentity: window.json_hasIdentity,
      is_show_title: window.json_is_show_title,
      redirectString: window.json_redirectString,
      serverUrl: window.json_serverUrl,
      serverUri: window.json_serverUri,
      store: {
        sName: window.json_sname,
        name: window.json_store_name,
        order: window.json_store_order,
        last_char_store_order: window.last_char_store_order
      },
      user: window.json_member,
      logo: window.json_logoWidth,
      cat_title_left: window.json_cat_title_left,
      tabs_left: window.tabs_left,
      template: window.json_template
    };
    this.updateDimensions = this.updateDimensions.bind(this);
  }

  componentWillMount() {
    this.updateDimensions();
  }

  componentWillUnmount() {
    window.removeEventListener("resize", this.updateDimensions);
    window.removeEventListener("orientationchange", this.updateDimensions);
  }

  initMetaHeader() {
    window.addEventListener("resize", this.updateDimensions);
    window.addEventListener("orientationchange", this.updateDimensions);
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

    let userMenuDisplay, loginMenuDisplay, siteHeaderTopRightCssClass;
    if (this.state.user) {
      userMenuDisplay = React.createElement(SiteHeaderUserMenu, {
        serverUrl: this.state.serverUrl,
        baseUrl: this.state.baseUrl,
        user: this.state.user
      });
      siteHeaderTopRightCssClass = "w-user";
    } else {
      loginMenuDisplay = React.createElement(SiteHeaderLoginMenu, {
        baseUrl: this.state.baseUrl,
        redirectString: this.state.redirectString,
        template: this.state.template
      });
    }

    let logoLink = this.state.serverUrl;
    if (this.state.serverUri.indexOf('/s/') > -1) {
      logoLink += "/s/" + this.state.store.name;
    }

    let siteHeaderStoreNameDisplay;
    if (this.state.is_show_title === "1") {
      siteHeaderStoreNameDisplay = React.createElement(
        "div",
        { id: "site-header-store-name-container" },
        React.createElement(
          "a",
          { href: logoLink },
          this.state.store.name
        )
      );
    }

    let HeaderDisplay;
    if (this.state.device !== "tablet") {
      HeaderDisplay = React.createElement(
        "section",
        { id: "site-header-wrapper", style: { "paddingLeft": this.state.template['header-logo']['width'] } },
        React.createElement(
          "div",
          { id: "siter-header-left" },
          React.createElement(
            "div",
            { id: "site-header-logo-container", style: this.state.template['header-logo'] },
            React.createElement(
              "a",
              { href: logoLink },
              React.createElement("img", { src: this.state.template['header-logo']['image-src'] })
            )
          ),
          siteHeaderStoreNameDisplay
        ),
        React.createElement(
          "div",
          { id: "site-header-right" },
          React.createElement(
            "div",
            { id: "site-header-right-top", className: siteHeaderTopRightCssClass },
            React.createElement(SiteHeaderSearchForm, {
              baseUrl: this.state.baseUrl
            }),
            userMenuDisplay
          ),
          React.createElement(
            "div",
            { id: "site-header-right-bottom" },
            loginMenuDisplay
          )
        )
      );
    } else {
      HeaderDisplay = React.createElement(
        "section",
        { id: "mobile-site-header" },
        React.createElement(
          "div",
          { id: "mobile-site-header-logo" },
          React.createElement(
            "a",
            { href: logoLink },
            React.createElement("img", { src: this.state.template['header-logo']['image-src'] })
          )
        ),
        React.createElement(
          "div",
          { id: "mobile-site-header-menus-container" },
          React.createElement(
            "div",
            { id: "switch-menu" },
            React.createElement(
              "a",
              { id: "search-menu-switch" },
              "more"
            ),
            React.createElement(
              "a",
              { id: "user-menu-switch" },
              "search"
            )
          )
        )
      );
    }

    return React.createElement(
      "section",
      { id: "site-header", style: this.state.template.header },
      HeaderDisplay
    );
  }
}

class SiteHeaderSearchForm extends React.Component {
  constructor(props) {
    super(props);
    this.state = {
      searchText: ''
    };
    this.onSearchTextChange = this.onSearchTextChange.bind(this);
    this.onSearchFormSubmit = this.onSearchFormSubmit.bind(this);
  }

  onSearchTextChange(e) {
    this.setState({ searchText: e.target.value });
  }

  onSearchFormSubmit(e) {
    e.preventDefault();
    window.location.href = this.props.baseUrl + "/search?projectSearchText=" + this.state.searchText;
  }

  render() {
    return React.createElement(
      "div",
      { id: "site-header-search-form" },
      React.createElement(
        "form",
        { id: "search-form", onSubmit: this.onSearchFormSubmit },
        React.createElement("input", { onChange: this.onSearchTextChange, value: this.state.searchText, type: "text", name: "projectSearchText" }),
        React.createElement("a", { onClick: this.onSearchFormSubmit })
      )
    );
  }
}

class SiteHeaderLoginMenu extends React.Component {
  constructor(props) {
    super(props);
    this.state = {};
  }
  render() {

    let registerButtonCssClass, loginButtonCssClass;

    if (window.location.href.indexOf('/register') > -1) {
      registerButtonCssClass = "active";
    }

    if (window.location.href.indexOf('/login') > -1) {
      loginButtonCssClass = "active";
    }

    const menuItemCssClass = {
      "borderColor": this.props.template['header-nav-tabs']['border-color'],
      "backgroundColor": this.props.template['header-nav-tabs']['background-color']
    };

    return React.createElement(
      "div",
      { id: "site-header-login-menu" },
      React.createElement(
        "ul",
        null,
        React.createElement(
          "li",
          { style: menuItemCssClass, className: registerButtonCssClass },
          React.createElement(
            "a",
            { href: this.props.baseUrl + "/register" },
            "Register"
          )
        ),
        React.createElement(
          "li",
          { style: menuItemCssClass, className: loginButtonCssClass },
          React.createElement(
            "a",
            { href: this.props.baseUrl + "/login" + this.props.redirectString },
            "Login"
          )
        )
      )
    );
  }
}

class SiteHeaderUserMenu extends React.Component {
  constructor(props) {
    super(props);
    this.state = {};
    this.handleClick = this.handleClick.bind(this);
  }

  componentWillMount() {
    document.addEventListener('mousedown', this.handleClick, false);
  }

  componentWillUnmount() {
    document.removeEventListener('mousedown', this.handleClick, false);
  }

  handleClick(e) {
    let dropdownClass = "";
    if (this.node.contains(e.target)) {
      if (this.state.dropdownClass === "open") {
        if (e.target.className === "profile-menu-toggle" || e.target.className === "profile-menu-image" || e.target.className === "profile-menu-username") {
          dropdownClass = "";
        } else {
          dropdownClass = "open";
        }
      } else {
        dropdownClass = "open";
      }
    }
    this.setState({ dropdownClass: dropdownClass });
  }

  render() {

    return React.createElement(
      "ul",
      { id: "site-header-user-menu-container" },
      React.createElement(
        "li",
        { ref: node => this.node = node, id: "user-menu-toggle", className: this.state.dropdownClass },
        React.createElement(
          "a",
          { className: "profile-menu-toggle" },
          React.createElement("img", { className: "profile-menu-image", src: window.json_member_avatar }),
          React.createElement(
            "span",
            { className: "profile-menu-username" },
            this.props.user.username
          )
        ),
        React.createElement(
          "ul",
          { id: "user-profile-menu" },
          React.createElement("div", { className: "dropdown-header" }),
          React.createElement(
            "li",
            null,
            React.createElement(
              "a",
              { href: "/product/add" },
              "Add Product"
            )
          ),
          React.createElement(
            "li",
            null,
            React.createElement(
              "a",
              { href: this.props.baseUrl + "/u/" + this.props.user.username + "/products" },
              "Products"
            )
          ),
          React.createElement(
            "li",
            null,
            React.createElement(
              "a",
              { href: this.props.baseUrl + "/u/" + this.props.user.username + "/plings" },
              "Plings"
            )
          ),
          React.createElement(
            "li",
            null,
            React.createElement(
              "a",
              { href: "/settings" },
              "Settings"
            )
          ),
          React.createElement(
            "li",
            null,
            React.createElement(
              "a",
              { href: "/logout" },
              "Logout"
            )
          )
        )
      )
    );
  }
}

ReactDOM.render(React.createElement(SiteHeader, null), document.getElementById('site-header-container'));
