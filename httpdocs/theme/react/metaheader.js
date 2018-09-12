window.appHelpers = function () {

  function generateMenuGroupsArray(domains) {
    let menuGroups = [];
    domains.forEach(function (domain, index) {
      if (menuGroups.indexOf(domain.menugroup) === -1) {
        menuGroups.push(domain.menugroup);
      }
    });
    return menuGroups;
  }

  return {
    generateMenuGroupsArray
  };
}();
class MetaHeader extends React.Component {
  constructor(props) {
    super(props);
    this.state = {
      baseUrl: baseUrl,
      blogUrl: blogUrl,
      loginUrl: loginUrl,
      domains: domains,
      user: user,
      sName: sName,
      loading: false
    };
  }

  componentDidMount() {
    console.log(this.state);
  }

  render() {

    return React.createElement(
      "nav",
      { id: "metaheader-nav", className: "metaheader" },
      React.createElement(
        "div",
        { className: "metamenu" },
        React.createElement(DomainsMenu, {
          domains: this.state.domains,
          baseUrl: this.state.baseUrl,
          sName: this.state.sName
        }),
        React.createElement(UserMenu, {
          user: this.state.user,
          blogUrl: this.state.blogUrl,
          loginUrl: this.state.loginUrl
        })
      )
    );
  }
}

class DomainsMenu extends React.Component {
  constructor(props) {
    super(props);
    this.state = {};
  }

  componentDidMount() {
    const menuGroups = appHelpers.generateMenuGroupsArray(this.props.domains);
    this.setState({ menuGroups: menuGroups });
  }

  render() {
    let menuGroupsDisplayLeft, menuGroupsDisplayRight;
    if (this.state.menuGroups) {
      menuGroupsDisplayLeft = this.state.menuGroups.slice(0, 2).map((mg, i) => React.createElement(DomainsMenuGroup, {
        key: i,
        domains: this.props.domains,
        menuGroup: mg,
        sName: this.props.sName
      }));
      menuGroupsDisplayRight = this.state.menuGroups.slice(2).map((mg, i) => React.createElement(DomainsMenuGroup, {
        key: i,
        domains: this.props.domains,
        menuGroup: mg,
        sName: this.props.sName
      }));
    }
    return React.createElement(
      "ul",
      { className: "metaheader-menu left", id: "domains-menu" },
      React.createElement(
        "li",
        { className: "active" },
        React.createElement(
          "a",
          { href: "http://" + this.props.baseUrl },
          React.createElement("img", { src: "/images/system/ocs-logo-rounded-16x16.png", className: "logo" }),
          "openDesktop.org :"
        )
      ),
      React.createElement(
        "li",
        { id: "domains-dropdown-menu", className: "dropdown" },
        React.createElement(
          "a",
          { id: "dropdownMenu3",
            "data-toggle": "dropdown",
            "aria-haspopup": "true",
            "aria-expanded": "true" },
          "Themes & Apps"
        ),
        React.createElement(
          "ul",
          { className: "dropdown-menu dropdown-menu-right", "aria-labelledby": "dropdownMenu3" },
          React.createElement(
            "li",
            { className: "submenu-container" },
            React.createElement(
              "ul",
              null,
              menuGroupsDisplayLeft
            )
          ),
          React.createElement(
            "li",
            { className: "submenu-container" },
            React.createElement(
              "ul",
              null,
              menuGroupsDisplayRight
            )
          )
        )
      ),
      React.createElement(
        "li",
        { id: "discussion-boards", className: "dropdown" },
        React.createElement(
          "a",
          { id: "dropdownMenu4",
            "data-toggle": "dropdown",
            "aria-haspopup": "true",
            "aria-expanded": "true" },
          "Discussion Boards"
        ),
        React.createElement(
          "ul",
          { className: "dropdown-menu dropdown-menu-right", "aria-labelledby": "dropdownMenu4" },
          React.createElement(
            "li",
            null,
            React.createElement(
              "a",
              { href: "https://forum.opendesktop.org/c/general" },
              "General"
            )
          ),
          React.createElement(
            "li",
            null,
            React.createElement(
              "a",
              { href: "https://forum.opendesktop.org/c/themes-and-apps" },
              "Themes & Apps"
            )
          ),
          React.createElement(
            "li",
            null,
            React.createElement(
              "a",
              null,
              "Coding"
            )
          )
        )
      )
    );
  }
}

class DomainsMenuGroup extends React.Component {
  constructor(props) {
    super(props);
    this.state = {};
    this.filterDomainsByMenuGroup = this.filterDomainsByMenuGroup.bind(this);
  }

  filterDomainsByMenuGroup(domain) {
    if (domain.menugroup === this.props.menuGroup) {
      return domain;
    }
  }

  render() {
    const domainsDisplay = this.props.domains.filter(this.filterDomainsByMenuGroup).map((domain, index) => React.createElement(
      "li",
      { key: index },
      React.createElement(
        "a",
        { href: "http://" + domain.menuhref },
        domain.name
      )
    ));

    return React.createElement(
      "li",
      null,
      React.createElement(
        "a",
        { href: "#" },
        React.createElement(
          "b",
          null,
          this.props.menuGroup
        )
      ),
      React.createElement(
        "ul",
        { className: "domains-sub-menu" },
        domainsDisplay
      )
    );
  }
}

class UserMenu extends React.Component {
  constructor(props) {
    super(props);
    this.state = {};
  }

  render() {
    let userDropdownDisplay;
    if (this.props.user) {
      userDropdownDisplay = React.createElement(UserLoginMenuContainer, {
        user: this.props.user
      });
    } else {
      userDropdownDisplay = React.createElement(
        "li",
        { id: "user-login-container" },
        React.createElement(
          "a",
          { href: this.props.loginUrl, className: "btn btn-metaheader" },
          "Login"
        )
      );
    }

    return React.createElement(
      "div",
      { id: "user-menu-container", className: "right" },
      React.createElement(
        "ul",
        { className: "metaheader-menu", id: "user-menu" },
        React.createElement(
          "li",
          null,
          React.createElement(
            "a",
            { href: "/community" },
            "Community"
          )
        ),
        React.createElement(
          "li",
          null,
          React.createElement(
            "a",
            { href: this.props.blogUrl, target: "_blank" },
            "Blog"
          )
        ),
        React.createElement(
          "li",
          null,
          React.createElement(
            "a",
            { id: "plingList", className: "popuppanel", href: "/plings" },
            "What are Plings?"
          )
        ),
        React.createElement(
          "li",
          null,
          React.createElement(
            "a",
            { id: "ocsapiContent", className: "popuppanel", href: "/partials/ocsapicontent.phtml" },
            "API"
          )
        ),
        React.createElement(
          "li",
          null,
          React.createElement(
            "a",
            { id: "aboutContent", className: "popuppanel", href: "/partials/about.phtml" },
            "About"
          )
        ),
        React.createElement(UserContextMenuContainer, null),
        userDropdownDisplay
      )
    );
  }
}

class UserContextMenuContainer extends React.Component {
  constructor(props) {
    super(props);
    this.state = {};
  }

  render() {
    return React.createElement(
      "li",
      { id: "user-context-menu-container" },
      React.createElement(
        "div",
        { className: "user-dropdown" },
        React.createElement(
          "button",
          {
            className: "btn btn-default dropdown-toggle",
            type: "button",
            id: "dropdownMenu2",
            "data-toggle": "dropdown",
            "aria-haspopup": "true",
            "aria-expanded": "true" },
          React.createElement("span", { className: "glyphicon glyphicon-th" })
        ),
        React.createElement(
          "ul",
          { className: "dropdown-menu dropdown-menu-right", "aria-labelledby": "dropdownMenu2" },
          React.createElement(
            "li",
            { id: "opendesktop-link-item" },
            React.createElement(
              "a",
              { href: "http://www.opendesktop.org" },
              React.createElement("div", { className: "icon" }),
              React.createElement(
                "span",
                null,
                "Themes ",
                React.createElement("br", null),
                " & Apps"
              )
            )
          ),
          React.createElement(
            "li",
            { id: "discourse-link-item" },
            React.createElement(
              "a",
              { href: "http://discourse.opendesktop.org/" },
              React.createElement("div", { className: "icon" }),
              React.createElement(
                "span",
                null,
                "Discussion ",
                React.createElement("br", null),
                " Boards"
              )
            )
          ),
          React.createElement(
            "li",
            { id: "opencode-link-item" },
            React.createElement(
              "a",
              { href: "https://www.opencode.net/" },
              React.createElement("div", { className: "icon" }),
              React.createElement(
                "span",
                null,
                "Coding ",
                React.createElement("br", null),
                " Tools"
              )
            )
          )
        )
      )
    );
  }
}

class UserLoginMenuContainer extends React.Component {
  constructor(props) {
    super(props);
    this.state = {};
  }

  render() {
    return React.createElement(
      "li",
      { id: "user-login-menu-container" },
      React.createElement(
        "div",
        { className: "user-dropdown" },
        React.createElement(
          "button",
          {
            className: "btn btn-default dropdown-toggle",
            type: "button",
            id: "userLoginDropdown",
            "data-toggle": "dropdown",
            "aria-haspopup": "true",
            "aria-expanded": "true" },
          React.createElement("img", { src: this.props.user.profile_image_url })
        ),
        React.createElement(
          "ul",
          { className: "dropdown-menu dropdown-menu-right", "aria-labelledby": "userLoginDropdown" },
          React.createElement(
            "li",
            { id: "user-info-menu-item" },
            React.createElement(
              "div",
              { id: "user-info-section" },
              React.createElement(
                "div",
                { className: "user-avatar" },
                React.createElement(
                  "div",
                  { className: "no-avatar-user-letter" },
                  React.createElement("img", { src: this.props.user.profile_image_url }),
                  React.createElement(
                    "a",
                    { className: "change-profile-pic" },
                    "Change"
                  )
                )
              ),
              React.createElement(
                "div",
                { className: "user-details" },
                React.createElement(
                  "ul",
                  null,
                  React.createElement(
                    "li",
                    null,
                    React.createElement(
                      "b",
                      null,
                      this.props.user.username
                    )
                  ),
                  React.createElement(
                    "li",
                    null,
                    this.props.user.mail
                  ),
                  React.createElement("li", null),
                  React.createElement(
                    "li",
                    null,
                    React.createElement(
                      "a",
                      null,
                      "Profile"
                    ),
                    " - ",
                    React.createElement(
                      "a",
                      null,
                      "Privacy"
                    )
                  ),
                  React.createElement(
                    "li",
                    null,
                    React.createElement(
                      "button",
                      { className: "btn btn-default btn-metaheader" },
                      "Account"
                    )
                  )
                )
              )
            )
          ),
          React.createElement("li", { id: "main-seperator", role: "separator", className: "divider" }),
          React.createElement(
            "li",
            { className: "buttons" },
            React.createElement(
              "button",
              { className: "btn btn-default btn-metaheader" },
              "Add Account"
            ),
            React.createElement(
              "button",
              { className: "btn btn-default pull-right btn-metaheader" },
              React.createElement(
                "a",
                { href: "/register" },
                "Sign Up"
              )
            )
          )
        )
      )
    );
  }
}

ReactDOM.render(React.createElement(MetaHeader, null), document.getElementById('metaheader'));
