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
    this.state = { loading: true };
  }

  componentDidMount() {
    this.setState({
      baseUrl: baseUrl,
      blogUrl: blogUrl,
      domains: domains,
      loading: false
    });
  }

  render() {
    let navDisplay;
    if (!this.state.loading) {
      navDisplay = React.createElement(
        "div",
        { className: "metamenu" },
        React.createElement(DomainsMenu, {
          domains: this.state.domains,
          baseUrl: this.state.baseUrl
        }),
        React.createElement(UserMenu, {
          user: this.state.user,
          blogUrl: this.state.blogUrl
        })
      );
    }
    return React.createElement(
      "nav",
      { id: "metaheader-nav", className: "metaheader" },
      navDisplay
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
    let menuGroupsDisplay;
    if (this.state.menuGroups) {
      menuGroupsDisplay = this.state.menuGroups.map((mg, i) => React.createElement(DomainsMenuGroup, {
        key: i,
        domains: this.props.domains,
        menuGroup: mg
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
      menuGroupsDisplay
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
        { href: domain.menuhref },
        domain.name
      )
    ));

    return React.createElement(
      "li",
      { className: "dropdown" },
      React.createElement(
        "a",
        { href: "#" },
        this.props.menuGroup
      ),
      React.createElement(
        "ul",
        { className: "dropdown-menu" },
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
      userDropdownDisplay = React.createElement(
        "li",
        null,
        "User"
      );
    } else {
      userDropdownDisplay = React.createElement(
        "li",
        { id: "user-login-container" },
        React.createElement(
          "a",
          { href: "/login", className: "btn btn-metaheader" },
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
                "Themes & Apps"
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
                "Discussion Boards"
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
                "Coding Tools"
              )
            )
          )
        )
      )
    );
  }
}

ReactDOM.render(React.createElement(MetaHeader, null), document.getElementById('metaheader'));
