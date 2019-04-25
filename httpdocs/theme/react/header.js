'use strict';

window.appHelpers = function () {

  function getEnv(domain) {

    var env = void 0;
    var lastDotSplit = this.splitByLastDot(domain);

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
    getEnv: getEnv,
    splitByLastDot: splitByLastDot
  };
}();
"use strict";

var _createClass = function () { function defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } } return function (Constructor, protoProps, staticProps) { if (protoProps) defineProperties(Constructor.prototype, protoProps); if (staticProps) defineProperties(Constructor, staticProps); return Constructor; }; }();

function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }

function _possibleConstructorReturn(self, call) { if (!self) { throw new ReferenceError("this hasn't been initialised - super() hasn't been called"); } return call && (typeof call === "object" || typeof call === "function") ? call : self; }

function _inherits(subClass, superClass) { if (typeof superClass !== "function" && superClass !== null) { throw new TypeError("Super expression must either be null or a function, not " + typeof superClass); } subClass.prototype = Object.create(superClass && superClass.prototype, { constructor: { value: subClass, enumerable: false, writable: true, configurable: true } }); if (superClass) Object.setPrototypeOf ? Object.setPrototypeOf(subClass, superClass) : subClass.__proto__ = superClass; }

var SiteHeader = function (_React$Component) {
  _inherits(SiteHeader, _React$Component);

  function SiteHeader(props) {
    _classCallCheck(this, SiteHeader);

    var _this = _possibleConstructorReturn(this, (SiteHeader.__proto__ || Object.getPrototypeOf(SiteHeader)).call(this, props));

    _this.state = {
      baseUrl: window.json_baseurl,
      searchBaseUrl: window.json_searchbaseurl,
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
      template: window.json_template,
      status: "",
      url_logout: window.json_logouturl
    };
    _this.updateDimensions = _this.updateDimensions.bind(_this);
    return _this;
  }

  _createClass(SiteHeader, [{
    key: "componentWillMount",
    value: function componentWillMount() {
      this.updateDimensions();
    }
  }, {
    key: "componentDidMount",
    value: function componentDidMount() {
      window.addEventListener("resize", this.updateDimensions);
      window.addEventListener("orientationchange", this.updateDimensions);
    }
  }, {
    key: "updateDimensions",
    value: function updateDimensions() {
      var width = window.innerWidth;
      var device = void 0;
      if (width >= 910) {
        device = "large";
      } else if (width < 910 && width >= 610) {
        device = "mid";
      } else if (width < 610) {
        device = "tablet";
      }
      this.setState({ device: device });
    }
  }, {
    key: "render",
    value: function render() {

      var userMenuDisplay = void 0,
          loginMenuDisplay = void 0,
          siteHeaderTopRightCssClass = void 0;
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

      var logoLink = this.state.serverUrl;
      if (this.state.serverUri.indexOf('/s/') > -1) {
        logoLink += "/s/" + this.state.store.name;
      }

      var siteHeaderStoreNameDisplay = void 0;
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

      var HeaderDisplay = void 0;
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
                baseUrl: this.state.baseUrl,
                searchBaseUrl: this.state.searchBaseUrl,
                store: this.state.store,
                height: this.state.template.header['height']
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
        HeaderDisplay = React.createElement(MobileSiteHeader, {
          logoLink: logoLink,
          template: this.state.template,
          user: this.state.user,
          baseUrl: this.state.baseUrl,
          searchBaseUrl: this.state.searchBaseUrl,
          serverUrl: this.state.serverUrl,
          store: this.state.store,
          redirectString: this.state.redirectString
        });
      }

      var templateHeaderStyle = void 0;
      if (this.state.template) {
        templateHeaderStyle = {
          "backgroundImage": this.state.template.header['background-image'],
          "backgroundColor": this.state.template.header['background-color'],
          "height": this.state.template.header['height']
        };
      }

      return React.createElement(
        "section",
        { id: "site-header", style: templateHeaderStyle, className: this.state.store.name.toLowerCase() },
        HeaderDisplay
      );
    }
  }]);

  return SiteHeader;
}(React.Component);

var SiteHeaderSearchForm = function (_React$Component2) {
  _inherits(SiteHeaderSearchForm, _React$Component2);

  function SiteHeaderSearchForm(props) {
    _classCallCheck(this, SiteHeaderSearchForm);

    var _this2 = _possibleConstructorReturn(this, (SiteHeaderSearchForm.__proto__ || Object.getPrototypeOf(SiteHeaderSearchForm)).call(this, props));

    _this2.state = {
      searchText: ''
    };
    _this2.onSearchTextChange = _this2.onSearchTextChange.bind(_this2);
    _this2.onSearchFormSubmit = _this2.onSearchFormSubmit.bind(_this2);
    return _this2;
  }

  _createClass(SiteHeaderSearchForm, [{
    key: "onSearchTextChange",
    value: function onSearchTextChange(e) {
      this.setState({ searchText: e.target.value });
    }
  }, {
    key: "onSearchFormSubmit",
    value: function onSearchFormSubmit(e) {
      e.preventDefault();
      window.location.href = this.props.searchBaseUrl + this.state.searchText;
    }
  }, {
    key: "render",
    value: function render() {

      var siteHeaderSearchFormStyle = void 0;
      console.log(this.props);
      console.log(this.props.store.name.indexOf("appimagehub") > -1);
      if (this.props.store.name.indexOf("appimagehub") > -1) {
        siteHeaderSearchFormStyle = {
          "marginTop": this.props.height / 2 - 19 + "px"
        };
      }

      return React.createElement(
        "div",
        { id: "site-header-search-form", style: siteHeaderSearchFormStyle },
        React.createElement(
          "form",
          { id: "search-form", onSubmit: this.onSearchFormSubmit },
          React.createElement("input", { onChange: this.onSearchTextChange, value: this.state.searchText, type: "text", name: "projectSearchText" }),
          React.createElement("a", { onClick: this.onSearchFormSubmit })
        )
      );
    }
  }]);

  return SiteHeaderSearchForm;
}(React.Component);

var SiteHeaderLoginMenu = function (_React$Component3) {
  _inherits(SiteHeaderLoginMenu, _React$Component3);

  function SiteHeaderLoginMenu(props) {
    _classCallCheck(this, SiteHeaderLoginMenu);

    var _this3 = _possibleConstructorReturn(this, (SiteHeaderLoginMenu.__proto__ || Object.getPrototypeOf(SiteHeaderLoginMenu)).call(this, props));

    _this3.state = {};
    return _this3;
  }

  _createClass(SiteHeaderLoginMenu, [{
    key: "render",
    value: function render() {

      var registerButtonCssClass = void 0,
          loginButtonCssClass = void 0;

      if (window.location.href.indexOf('/register') > -1) {
        registerButtonCssClass = "active";
      }

      if (window.location.href.indexOf('/login') > -1) {
        loginButtonCssClass = "active";
      }

      var menuItemCssClass = {
        "borderColor": this.props.template['header-nav-tabs']['border-color'],
        "backgroundColor": this.props.template['header-nav-tabs']['background-color']
        //<li style={menuItemCssClass} className={registerButtonCssClass}><a href={this.props.baseUrl + "/register"}>Register</a></li>
      };return React.createElement(
        "div",
        { id: "site-header-login-menu" },
        React.createElement(
          "ul",
          null,
          React.createElement(
            "li",
            { style: menuItemCssClass, className: loginButtonCssClass },
            React.createElement(
              "a",
              { href: this.props.baseUrl + "/login" + this.props.redirectString },
              "Sign in"
            )
          )
        )
      );
    }
  }]);

  return SiteHeaderLoginMenu;
}(React.Component);

var SiteHeaderUserMenu = function (_React$Component4) {
  _inherits(SiteHeaderUserMenu, _React$Component4);

  function SiteHeaderUserMenu(props) {
    _classCallCheck(this, SiteHeaderUserMenu);

    var _this4 = _possibleConstructorReturn(this, (SiteHeaderUserMenu.__proto__ || Object.getPrototypeOf(SiteHeaderUserMenu)).call(this, props));

    _this4.state = {};
    _this4.handleClick = _this4.handleClick.bind(_this4);
    return _this4;
  }

  _createClass(SiteHeaderUserMenu, [{
    key: "componentWillMount",
    value: function componentWillMount() {
      document.addEventListener('mousedown', this.handleClick, false);
    }
  }, {
    key: "componentWillUnmount",
    value: function componentWillUnmount() {
      document.removeEventListener('mousedown', this.handleClick, false);
    }
  }, {
    key: "handleClick",
    value: function handleClick(e) {
      var dropdownClass = "";
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
  }, {
    key: "render",
    value: function render() {
      var _this5 = this;

      return React.createElement(
        "ul",
        { id: "site-header-user-menu-container" },
        React.createElement(
          "li",
          { ref: function ref(node) {
              return _this5.node = node;
            }, id: "user-menu-toggle", className: this.state.dropdownClass },
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
                { href: window.json_baseurl + "product/add" },
                "Add Product"
              )
            ),
            React.createElement(
              "li",
              null,
              React.createElement(
                "a",
                { href: window.json_baseurl + "u/" + this.props.user.username + "/products" },
                "Products"
              )
            ),
            React.createElement(
              "li",
              null,
              React.createElement(
                "a",
                { href: window.json_baseurl + "u/" + this.props.user.username + "/payout" },
                "Payout"
              )
            ),
            React.createElement(
              "li",
              null,
              React.createElement(
                "a",
                { href: window.json_baseurl + "settings" },
                "Settings"
              )
            ),
            React.createElement(
              "li",
              null,
              React.createElement(
                "a",
                { href: window.json_logouturl },
                "Logout"
              )
            )
          )
        )
      );
    }
  }]);

  return SiteHeaderUserMenu;
}(React.Component);

var MobileSiteHeader = function (_React$Component5) {
  _inherits(MobileSiteHeader, _React$Component5);

  function MobileSiteHeader(props) {
    _classCallCheck(this, MobileSiteHeader);

    var _this6 = _possibleConstructorReturn(this, (MobileSiteHeader.__proto__ || Object.getPrototypeOf(MobileSiteHeader)).call(this, props));

    _this6.state = {
      status: "switch"
    };
    _this6.showMobileUserMenu = _this6.showMobileUserMenu.bind(_this6);
    _this6.showMobileSearchForm = _this6.showMobileSearchForm.bind(_this6);
    _this6.showMobileSwitchMenu = _this6.showMobileSwitchMenu.bind(_this6);

    return _this6;
  }

  _createClass(MobileSiteHeader, [{
    key: "showMobileUserMenu",
    value: function showMobileUserMenu() {
      this.setState({ status: "user" });
    }
  }, {
    key: "showMobileSearchForm",
    value: function showMobileSearchForm() {
      this.setState({ status: "search" });
    }
  }, {
    key: "showMobileSwitchMenu",
    value: function showMobileSwitchMenu() {
      this.setState({ status: "switch" });
    }
  }, {
    key: "render",
    value: function render() {
      var menuItemCssClass = {
        "borderColor": this.props.template['header-nav-tabs']['border-color'],
        "backgroundColor": this.props.template['header-nav-tabs']['background-color']
      };

      var closeMenuElementDisplay = React.createElement(
        "a",
        { className: "menu-item", onClick: this.showMobileSwitchMenu },
        React.createElement("span", { className: "glyphicon glyphicon-remove" })
      );

      var mobileMenuDisplay = void 0;
      if (this.state.status === "switch") {
        mobileMenuDisplay = React.createElement(
          "div",
          { id: "switch-menu" },
          React.createElement(
            "a",
            { className: "menu-item", onClick: this.showMobileSearchForm, id: "user-menu-switch" },
            React.createElement("span", { className: "glyphicon glyphicon-search" })
          ),
          React.createElement(
            "a",
            { className: "menu-item", onClick: this.showMobileUserMenu, id: "search-menu-switch" },
            React.createElement("span", { className: "glyphicon glyphicon-option-horizontal" })
          )
        );
      } else if (this.state.status === "user") {
        mobileMenuDisplay = React.createElement(
          "div",
          { id: "mobile-user-menu" },
          React.createElement(
            "div",
            { className: "menu-content-wrapper" },
            React.createElement(MobileUserContainer, {
              user: this.props.user,
              baseUrl: this.props.baseUrl,
              serverUrl: this.state.serverUrl,
              template: this.props.template,
              redirectString: this.props.redirectString
            })
          ),
          closeMenuElementDisplay
        );
      } else if (this.state.status === "search") {
        mobileMenuDisplay = React.createElement(
          "div",
          { id: "mobile-search-menu" },
          React.createElement(
            "div",
            { className: "menu-content-wrapper" },
            React.createElement(SiteHeaderSearchForm, {
              baseUrl: this.props.baseUrl,
              searchBaseUrl: this.props.searchBaseUrl
            })
          ),
          closeMenuElementDisplay
        );
      }

      var logoElementCssClass = this.props.store.name;
      if (this.state.status !== "switch") {
        logoElementCssClass += " mini-version";
      }

      return React.createElement(
        "section",
        { id: "mobile-site-header" },
        React.createElement(
          "div",
          { id: "mobile-site-header-logo", className: logoElementCssClass },
          React.createElement(
            "a",
            { href: this.props.logoLink },
            React.createElement("img", { src: this.props.template['header-logo']['image-src'] })
          )
        ),
        React.createElement(
          "div",
          { id: "mobile-site-header-menus-container" },
          mobileMenuDisplay
        )
      );
    }
  }]);

  return MobileSiteHeader;
}(React.Component);

var MobileUserContainer = function (_React$Component6) {
  _inherits(MobileUserContainer, _React$Component6);

  function MobileUserContainer(props) {
    _classCallCheck(this, MobileUserContainer);

    var _this7 = _possibleConstructorReturn(this, (MobileUserContainer.__proto__ || Object.getPrototypeOf(MobileUserContainer)).call(this, props));

    _this7.state = {};
    return _this7;
  }

  _createClass(MobileUserContainer, [{
    key: "render",
    value: function render() {

      var userDisplay = void 0;
      if (this.props.user) {
        userDisplay = React.createElement(SiteHeaderUserMenu, {
          serverUrl: this.state.serverUrl,
          baseUrl: this.state.baseUrl,
          user: this.props.user
        });
      } else {
        userDisplay = React.createElement(SiteHeaderLoginMenu, {
          user: this.props.user,
          baseUrl: this.props.baseUrl,
          template: this.props.template,
          redirectString: this.props.redirectString
        });
      }

      return React.createElement(
        "div",
        { id: "mobile-user-container" },
        userDisplay
      );
    }
  }]);

  return MobileUserContainer;
}(React.Component);

ReactDOM.render(React.createElement(SiteHeader, null), document.getElementById('site-header-container'));
