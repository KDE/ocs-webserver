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

  function getDeviceFromWidth(width) {
    let device;
    if (width >= 910) {
      device = "large";
    } else if (width < 910 && width >= 600) {
      device = "mid";
    } else if (width < 600) {
      device = "tablet";
    }
    return device;
  }

  return {
    generateMenuGroupsArray,
    getDeviceFromWidth
  };
}();
class MetaHeader extends React.Component {
  constructor(props) {
    super(props);
    this.state = {
      domains: window.domains,
      baseUrl: window.baseUrl,
      blogUrl: window.blogUrl,
      forumUrl: window.forumUrl,
      loginUrl: window.loginUrl,
      logoutUrl: window.logoutUrl,
      gitlabUrl: window.gitlabUrl,
      sName: window.sName,
      user: {}
    };
    this.initMetaHeader = this.initMetaHeader.bind(this);
    this.updateDimensions = this.updateDimensions.bind(this);
    this.getUser = this.getUser.bind(this);
  }

  componentWillMount() {
    this.updateDimensions();
  }

  componentDidMount() {
    this.initMetaHeader();
  }

  componentWillUnmount() {
    window.removeEventListener("resize", this.updateDimensions);
  }

  initMetaHeader() {
    window.addEventListener("resize", this.updateDimensions);
    this.getUser();
  }

  getUser() {
    const decodedCookie = decodeURIComponent(document.cookie);
    let ocs_data = decodedCookie.split('ocs_data=')[1];
    if (ocs_data) {
      if (ocs_data.indexOf(';') > -1) {
        ocs_data = ocs_data.split(';')[0];
      }
      const user = JSON.parse(ocs_data);
      this.setState({ user: user });
    }
  }

  updateDimensions() {
    const device = appHelpers.getDeviceFromWidth(window.innerWidth);
    this.setState({ device: device });
  }

  render() {
    let domainsMenuDisplay;
    if (this.state.device === "tablet") {
      domainsMenuDisplay = React.createElement(MobileLeftMenu, {
        device: this.state.device,
        domains: domains,
        user: this.state.user,
        baseUrl: this.state.baseUrl,
        blogUrl: this.state.blogUrl,
        forumUrl: this.state.forumUrl,
        sName: this.state.sName
      });
    } else {
      domainsMenuDisplay = React.createElement(DomainsMenu, {
        device: this.state.device,
        domains: domains,
        user: this.state.user,
        baseUrl: this.state.baseUrl,
        blogUrl: this.state.blogUrl,
        forumUrl: this.state.forumUrl,
        sName: this.state.sName
      });
    }
    return React.createElement(
      "nav",
      { id: "metaheader-nav", className: "metaheader" },
      React.createElement(
        "div",
        { className: "metamenu" },
        domainsMenuDisplay,
        React.createElement(UserMenu, {
          device: this.state.device,
          user: this.state.user,
          baseUrl: this.state.baseUrl,
          blogUrl: this.state.blogUrl,
          forumUrl: this.state.forumUrl,
          loginUrl: this.state.loginUrl,
          logoutUrl: this.state.logoutUrl,
          gitlabUrl: this.state.gitlabUrl
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

  render() {

    let moreMenuItemDisplay;
    if (this.props.device !== "large") {
      moreMenuItemDisplay = React.createElement(MoreDropDownMenu, {
        domains: this.props.domains,
        baseUrl: this.props.baseUrl,
        blogUrl: this.props.blogUrl
      });
    }

    return React.createElement(
      "ul",
      { className: "metaheader-menu left", id: "domains-menu" },
      React.createElement(
        "li",
        { className: "active" },
        React.createElement(
          "a",
          { href: this.props.baseUrl },
          React.createElement("img", { src: this.props.baseUrl + "/images/system/ocs-logo-rounded-16x16.png", className: "logo" }),
          "openDesktop.org :"
        )
      ),
      React.createElement(DomainsDropDownMenu, {
        domains: this.props.domains
      }),
      React.createElement(DiscussionBoardsDropDownMenu, {
        forumUrl: this.props.forumUrl
      }),
      moreMenuItemDisplay
    );
  }
}

class DomainsDropDownMenu extends React.Component {
  constructor(props) {
    super(props);
    this.state = {};
    this.handleClick = this.handleClick.bind(this);
  }

  componentDidMount() {
    const menuGroups = appHelpers.generateMenuGroupsArray(this.props.domains);
    this.setState({ menuGroups: menuGroups });
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
        if (e.target.className === "domains-menu-link-item") {
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
      "li",
      { ref: node => this.node = node, id: "domains-dropdown-menu", className: this.state.dropdownClass },
      React.createElement(
        "a",
        { className: "domains-menu-link-item" },
        "Themes & Apps"
      ),
      React.createElement(
        "ul",
        { className: "dropdown-menu dropdown-menu-right" },
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
    );
  }
}

class DiscussionBoardsDropDownMenu extends React.Component {
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
        if (e.target.className === "discussion-menu-link-item") {
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
      "li",
      { ref: node => this.node = node, id: "discussion-boards", className: this.state.dropdownClass },
      React.createElement(
        "a",
        null,
        "Discussion Boards"
      ),
      React.createElement(
        "ul",
        { className: "discussion-menu dropdown-menu dropdown-menu-right" },
        React.createElement(
          "li",
          null,
          React.createElement(
            "a",
            { href: this.props.forumUrl + "/c/general" },
            "General"
          )
        ),
        React.createElement(
          "li",
          null,
          React.createElement(
            "a",
            { href: this.props.forumUrl + "/c/themes-and-apps" },
            "Themes & Apps"
          )
        ),
        React.createElement(
          "li",
          null,
          React.createElement(
            "a",
            { href: this.props.forumUrl + "/c/coding" },
            "Coding"
          )
        )
      )
    );
  }

}

class MoreDropDownMenu extends React.Component {
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
        if (e.target.className === "more-menu-link-item") {
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

    let plingListUrl = "/#plingList",
        ocsapiContentUrl = "/#ocsapiContent",
        aboutContentUrl = "/#aboutContent",
        linkTarget = "_blank";
    if (window.location.hostname === this.props.baseUrl.split('https://')[1]) {
      plingListUrl = "/plings";
      ocsapiContentUrl = "/partials/ocsapicontent.phtml";
      aboutContentUrl = "/partials/about.phtml";
      linkTarget = "";
    }

    return React.createElement(
      "li",
      { ref: node => this.node = node, id: "more-dropdown-menu", className: this.state.dropdownClass },
      React.createElement(
        "a",
        { className: "more-menu-link-item" },
        "More"
      ),
      React.createElement(
        "ul",
        { className: "dropdown-menu" },
        React.createElement(
          "li",
          null,
          React.createElement(
            "a",
            { href: this.props.baseUrl + "/community" },
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
            { id: "plingList", className: "popuppanel", target: linkTarget, href: this.props.baseUrl + plingListUrl },
            "What are Plings?"
          )
        ),
        React.createElement(
          "li",
          null,
          React.createElement(
            "a",
            { id: "ocsapiContent", className: "popuppanel", target: linkTarget, href: this.props.baseUrl + ocsapiContentUrl },
            "API"
          )
        ),
        React.createElement(
          "li",
          null,
          React.createElement(
            "a",
            { id: "aboutContent", className: "popuppanel", target: linkTarget, href: this.props.baseUrl + aboutContentUrl },
            "About"
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
    const domainsDisplay = this.props.domains.filter(this.filterDomainsByMenuGroup).map((domain, index) => {
      let domainPrefix = "";
      if (domain.menuhref.indexOf('https://') === -1 && domain.menuhref.indexOf('http://') === -1) {
        domainPrefix += "http://";
      }
      return React.createElement(
        "li",
        { key: index },
        React.createElement(
          "a",
          { href: domainPrefix + domain.menuhref },
          domain.name
        )
      );
    });

    return React.createElement(
      "li",
      null,
      React.createElement(
        "a",
        { className: "groupname" },
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
    let userDropdownDisplay, userAppsContextDisplay;
    if (this.props.user && this.props.user.member_id) {
      userDropdownDisplay = React.createElement(UserLoginMenuContainer, {
        user: this.props.user,
        logoutUrl: this.props.logoutUrl,
        baseUrl: this.props.baseUrl
      });
      userAppsContextDisplay = React.createElement(UserContextMenuContainer, {
        user: this.props.user,
        forumUrl: this.props.forumUrl,
        gitlabUrl: this.props.gitlabUrl
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

    let userMenuContainerDisplay;
    if (this.props.device === "large") {

      let plingListUrl = "/#plingList",
          ocsapiContentUrl = "/#ocsapiContent",
          aboutContentUrl = "/#aboutContent",
          linkTarget = "_blank";
      if (window.location.hostname === this.props.baseUrl.split('https://')[1]) {
        plingListUrl = "/plings";
        ocsapiContentUrl = "/partials/ocsapicontent.phtml";
        aboutContentUrl = "/partials/about.phtml";
        linkTarget = "";
      }

      userMenuContainerDisplay = React.createElement(
        "ul",
        { className: "metaheader-menu", id: "user-menu" },
        React.createElement(
          "li",
          null,
          React.createElement(
            "a",
            { href: this.props.baseUrl + "/community" },
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
            { id: "plingList", className: "popuppanel", target: linkTarget, href: this.props.baseUrl + plingListUrl },
            "What are Plings?"
          )
        ),
        React.createElement(
          "li",
          null,
          React.createElement(
            "a",
            { id: "ocsapiContent", className: "popuppanel", target: linkTarget, href: this.props.baseUrl + ocsapiContentUrl },
            "API"
          )
        ),
        React.createElement(
          "li",
          null,
          React.createElement(
            "a",
            { id: "aboutContent", className: "popuppanel", target: linkTarget, href: this.props.baseUrl + aboutContentUrl },
            "About"
          )
        ),
        userAppsContextDisplay,
        userDropdownDisplay
      );
    } else {
      userMenuContainerDisplay = React.createElement(
        "ul",
        { className: "metaheader-menu", id: "user-menu" },
        userAppsContextDisplay,
        userDropdownDisplay
      );
    }

    return React.createElement(
      "div",
      { id: "user-menu-container", className: "right" },
      userMenuContainerDisplay
    );
  }
}

class UserContextMenuContainer extends React.Component {
  constructor(props) {
    super(props);
    this.state = {
      gitlabLink: window.gitlabUrl + "/dashboard/issues?assignee_id="
    };
    this.handleClick = this.handleClick.bind(this);
  }

  componentWillMount() {
    document.addEventListener('mousedown', this.handleClick, false);
  }

  componentWillUnmount() {
    document.removeEventListener('mousedown', this.handleClick, false);
  }

  componentDidMount() {
    const self = this;
    $.ajax({ url: window.gitlabUrl + "/api/v4/users?username=" + this.props.user.username, cache: false }).done(function (response) {
      const gitlabLink = self.state.gitlabLink + response[0].id;
      self.setState({ gitlabLink: gitlabLink, loading: false });
    });
  }

  handleClick(e) {
    let dropdownClass = "";
    if (this.node.contains(e.target)) {
      if (this.state.dropdownClass === "open") {
        if (e.target.className === "th-icon" || e.target.className === "btn btn-default dropdown-toggle") {
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
      "li",
      { ref: node => this.node = node, id: "user-context-menu-container" },
      React.createElement(
        "div",
        { className: "user-dropdown " + this.state.dropdownClass },
        React.createElement(
          "button",
          {
            className: "btn btn-default dropdown-toggle", type: "button", onClick: this.toggleDropDown },
          React.createElement("span", { className: "th-icon" })
        ),
        React.createElement(
          "ul",
          { id: "user-context-dropdown", className: "dropdown-menu dropdown-menu-right" },
          React.createElement(
            "li",
            { id: "opencode-link-item" },
            React.createElement(
              "a",
              { href: this.props.gitlabUrl + "/dashboard/projects" },
              React.createElement("div", { className: "icon" }),
              React.createElement(
                "span",
                null,
                "Projects"
              )
            )
          ),
          React.createElement(
            "li",
            { id: "issues-link-item" },
            React.createElement(
              "a",
              { href: this.state.gitlabLink },
              React.createElement("div", { className: "icon" }),
              React.createElement(
                "span",
                null,
                "Issues"
              )
            )
          ),
          React.createElement(
            "li",
            { id: "messages-link-item" },
            React.createElement(
              "a",
              { href: this.props.forumUrl + "/u/" + this.props.user.username + "/messages" },
              React.createElement("div", { className: "icon" }),
              React.createElement(
                "span",
                null,
                "Messages"
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
        if (e.target.className === "th-icon" || e.target.className === "btn btn-default dropdown-toggle") {
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
      "li",
      { id: "user-login-menu-container", ref: node => this.node = node },
      React.createElement(
        "div",
        { className: "user-dropdown " + this.state.dropdownClass },
        React.createElement(
          "button",
          {
            className: "btn btn-default dropdown-toggle",
            type: "button",
            id: "userLoginDropdown" },
          React.createElement("img", { className: "th-icon", src: this.props.user.avatar })
        ),
        React.createElement(
          "ul",
          { className: "dropdown-menu dropdown-menu-right" },
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
                  React.createElement("img", { src: this.props.user.avatar })
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
                  )
                )
              )
            )
          ),
          React.createElement(
            "li",
            { className: "buttons" },
            React.createElement(
              "a",
              { href: this.props.baseUrl + "/settings/", className: "btn btn-default btn-metaheader" },
              "Settings"
            ),
            React.createElement(
              "a",
              { href: this.props.logoutUrl, className: "btn btn-default pull-right btn-metaheader" },
              "Logout"
            )
          )
        )
      )
    );
  }
}

/** MOBILE SPECIFIC **/

class MobileLeftMenu extends React.Component {
  constructor(props) {
    super(props);
    this.state = {
      overlayClass: ""
    };
    this.toggleLeftSideOverlay = this.toggleLeftSideOverlay.bind(this);
    this.handleClick = this.handleClick.bind(this);
  }

  componentWillMount() {
    window.addEventListener('mousedown', this.handleClick, false);
    window.addEventListener('touchend', this.handleClick, false);
  }

  componentWillUnmount() {
    window.removeEventListener('mousedown', this.handleClick, false);
    window.addEventListener('touchend', this.handleClick, false);
  }

  toggleLeftSideOverlay() {
    let overlayClass = "open";
    if (this.state.overlayClass === "open") {
      overlayClass = "";
    }
    this.setState({ overlayClass: overlayClass });
  }

  handleClick(e) {
    let overlayClass = "";
    if (this.node.contains(e.target)) {
      if (this.state.overlayClass === "open") {
        if (e.target.id === "left-side-overlay" || e.target.id === "menu-toggle-item") {
          overlayClass = "";
        } else {
          overlayClass = "open";
        }
      } else {
        overlayClass = "open";
      }
    }
    this.setState({ overlayClass: overlayClass });
  }

  render() {
    return React.createElement(
      "div",
      { ref: node => this.node = node, id: "metaheader-left-mobile", className: this.state.overlayClass },
      React.createElement("a", { onClick: this.toggleLeftSideOverlay, className: "menu-toggle", id: "menu-toggle-item" }),
      React.createElement(
        "div",
        { id: "left-side-overlay" },
        React.createElement(MobileLeftSidePanel, {
          baseUrl: this.props.baseUrl,
          domains: this.props.domains
        })
      )
    );
  }
}

class MobileLeftSidePanel extends React.Component {
  constructor(props) {
    super(props);
    this.state = {};
  }

  componentDidMount() {
    const menuGroups = appHelpers.generateMenuGroupsArray(this.props.domains);
    this.setState({ menuGroups: menuGroups });
  }

  render() {
    let panelMenuGroupsDisplay;
    if (this.state.menuGroups) {
      panelMenuGroupsDisplay = this.state.menuGroups.map((mg, i) => React.createElement(DomainsMenuGroup, {
        key: i,
        domains: this.props.domains,
        menuGroup: mg,
        sName: this.props.sName
      }));
    }

    return React.createElement(
      "div",
      { id: "left-side-panel" },
      React.createElement(
        "div",
        { id: "panel-header" },
        React.createElement(
          "a",
          { href: this.props.baseUrl },
          React.createElement("img", { src: this.props.baseUrl + "/images/system/opendesktop-logo.png", className: "logo" }),
          " openDesktop.org"
        )
      ),
      React.createElement(
        "div",
        { id: "panel-menu" },
        React.createElement(
          "ul",
          null,
          panelMenuGroupsDisplay
        )
      )
    );
  }
}

ReactDOM.render(React.createElement(MetaHeader, null), document.getElementById('metaheader'));
