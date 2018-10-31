window.appHelpers = function () {

  function getEnv(domain) {
    let env;
    if (this.splitByLastDot(domain) === 'com' || this.splitByLastDot(domain) === 'org') {
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
  }

  componentDidMount() {
    console.log(this.state);
  }

  render() {

    let userMenuDisplay, loginMenuDisplay;
    if (this.state.user) {
      userMenuDisplay = React.createElement(SiteHeaderUserMenu, {
        baseUrl: this.state.baseUrl,
        user: this.state.user
      });
    } else {
      loginMenuDisplay = React.createElement(SiteHeaderLoginMenu, {
        baseUrl: this.state.baseUrl,
        redirectString: this.state.redirectString
      });
    }

    let siteHeaderStoreNameDisplay;
    if (this.state.is_show_title === "1") {
      siteHeaderStoreNameDisplay = React.createElement(
        "div",
        { id: "site-header-store-name-container" },
        React.createElement(
          "a",
          { href: this.state.serverUrl + this.state.serverUri },
          this.state.store.name
        )
      );
    }

    return React.createElement(
      "section",
      { id: "site-header", style: this.state.template.header },
      React.createElement(
        "section",
        { id: "site-header-wrapper", style: { "paddingLeft": this.state.template['header-logo']['width'] } },
        React.createElement(
          "div",
          { id: "site-header-logo-container", style: this.state.template['header-logo'] },
          React.createElement(
            "a",
            { href: this.state.serverUrl + this.state.serverUri },
            React.createElement("img", { src: this.state.template['header-logo']['image-src'] })
          )
        ),
        siteHeaderStoreNameDisplay,
        React.createElement(
          "div",
          { id: "site-header-right" },
          React.createElement(
            "div",
            { id: "site-header-right-top" },
            React.createElement(SiteHeaderSearchForm, null),
            userMenuDisplay
          ),
          React.createElement(
            "div",
            { id: "site-header-right-bottom" },
            loginMenuDisplay
          )
        )
      )
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

  onSearchFormSubmit() {
    console.log(this.state.searchText);
  }

  render() {
    return React.createElement(
      "div",
      { id: "site-header-search-form" },
      React.createElement(
        "div",
        { id: "search-form" },
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

    return React.createElement(
      "div",
      { id: "site-header-login-menu" },
      React.createElement(
        "ul",
        null,
        React.createElement(
          "li",
          { className: registerButtonCssClass },
          React.createElement(
            "a",
            { href: this.props.baseUrl + "/register" },
            "Register"
          )
        ),
        React.createElement(
          "li",
          { className: loginButtonCssClass },
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

  handleClick(e) {
    console.log(e);
    let dropdownClass = "";
    if (this.node.contains(e.target)) {
      if (this.state.dropdownClass === "open") {
        if (e.target.className === "profile-menu-toggle") {
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

    let imageBaseUrl;
    const env = appHelpers.getEnv(window.location.href);
    if (env === "live") {
      imageBaseUrl = "https://cn.pling.com/cache/200x200-2/img/";
    } else {
      imageBaseUrl = "https://cn.pling.it/cache/200x200-2/img/";
    }

    return React.createElement(
      "ul",
      { ref: node => this.node = node, id: "site-header-user-menu-container", className: this.state.dropdownClass },
      React.createElement(
        "li",
        { id: "user-menu-toggle" },
        React.createElement(
          "a",
          { className: "profile-menu-toggle" },
          React.createElement("img", { src: imageBaseUrl + this.props.user.avatar }),
          React.createElement(
            "span",
            null,
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
