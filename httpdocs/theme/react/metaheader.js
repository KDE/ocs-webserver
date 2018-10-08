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

  function getDomainsArray() {
    const domains = [{
      "order": "30101",
      "calcOrder": 9,
      "host": "books.pling.cc",
      "name": "Books",
      "menuactive": 0,
      "menuhref": "books.pling.cc",
      "menugroup": "Desktops"
    }, {
      "order": "30200",
      "calcOrder": 9,
      "host": "comics.pling.cc",
      "name": "Comics",
      "menuactive": 0,
      "menuhref": "opendesktop.cc/s/Comics",
      "menugroup": "Desktops"
    }, {
      "order": "30300",
      "calcOrder": 9,
      "host": "music.pling.cc",
      "name": "Music",
      "menuactive": 0,
      "menuhref": "opendesktop.cc/s/Music",
      "menugroup": "Desktops"
    }, {
      "order": "10000",
      "calcOrder": 10,
      "host": "www.pling.cc",
      "name": "Pling",
      "menuactive": 0,
      "menuhref": "opendesktop.cc/s/Pling",
      "menugroup": "Applications"
    }, {
      "order": "10000",
      "calcOrder": 10,
      "host": "snappy",
      "name": "Snappy",
      "menuactive": 0,
      "menuhref": "opendesktop.cc/s/Snappy",
      "menugroup": "Applications"
    }, {
      "order": "10100",
      "calcOrder": 10,
      "host": "android.pling.cc",
      "name": "Android",
      "menuactive": 0,
      "menuhref": "opendesktop.cc/s/Android",
      "menugroup": "Applications"
    }, {
      "order": "10101",
      "calcOrder": 10,
      "host": "www.opendesktop.cc",
      "name": "opendesktop",
      "menuactive": 0,
      "menuhref": "www.opendesktop.cc",
      "menugroup": "Applications"
    }, {
      "order": "10200",
      "calcOrder": 10,
      "host": "linux.pling.cc",
      "name": "Appimages",
      "menuactive": 0,
      "menuhref": "opendesktop.cc/s/Appimages",
      "menugroup": "Applications"
    }, {
      "order": "10300",
      "calcOrder": 10,
      "host": "windows.pling.cc",
      "name": "Windows",
      "menuactive": 0,
      "menuhref": "opendesktop.cc/s/Windows",
      "menugroup": "Applications"
    }, {
      "order": "20100",
      "calcOrder": 20,
      "host": "kde.pling.cc",
      "name": "KDE",
      "menuactive": 0,
      "menuhref": "opendesktop.cc/s/KDE",
      "menugroup": "Addons"
    }, {
      "order": "20200",
      "calcOrder": 20,
      "host": "gnome.pling.cc",
      "name": "Gnome",
      "menuactive": 0,
      "menuhref": "opendesktop.cc/s/Gnome",
      "menugroup": "Addons"
    }, {
      "order": "20400",
      "calcOrder": 20,
      "host": "xfce.opendesktop.cc",
      "name": "XFCE",
      "menuactive": 0,
      "menuhref": "opendesktop.cc/s/XFCE",
      "menugroup": "Addons"
    }, {
      "order": "20901",
      "calcOrder": 20,
      "host": "pling.local",
      "name": "Siyuan's personal store",
      "menuactive": 0,
      "menuhref": "pling.local",
      "menugroup": "Addons"
    }, {
      "order": "40400",
      "calcOrder": 40,
      "host": "videos.pling.cc",
      "name": "Videos",
      "menuactive": 0,
      "menuhref": "opendesktop.cc/s/Videos",
      "menugroup": "Artwork"
    }, {
      "order": "50300",
      "calcOrder": 50,
      "host": "xfce.pling.cc",
      "name": "XFCE-Pling-CC",
      "menuactive": 0,
      "menuhref": "opendesktop.cc/s/XFCE-Pling-CC",
      "menugroup": "Other"
    }];
    return domains;
  }

  return {
    generateMenuGroupsArray,
    getDomainsArray
  };
}();
class MetaHeader extends React.Component {
  constructor(props) {
    super(props);
    this.state = {
      baseUrl: "opendesktop.cc",
      blogUrl: "https://blog.opendesktop.org",
      loginUrl: loginUrl,
      user: user,
      sName: sName,
      loading: false
    };
    this.getData = this.getData.bind(this);
  }

  componentDidMount() {
    this.getData();
  }

  getData() {
    console.log('get data');
    const self = this;
    $.ajax({
      url: 'https://www.opendesktop.cc/user/userdataajax',
      method: 'get',
      dataType: 'jsonp',
      done: function (response) {
        console.log(response.responseText);
        const user = JSON.parse(response.responseText);
        console.log(user);
        self.setState({ user: user, loading: false });
      },
      error: function (response) {
        console.log('error');
        console.log(response);
        const user = JSON.parse(response.responseText);
        console.log(user);
        self.setState({ user: user, loading: false });
      },
      success: function (response) {
        console.log('success');
        console.log(response);
        const user = JSON.parse(response.responseText);
        console.log(user);
        self.setState({ user: user, loading: false });
      }
    });
  }

  render() {
    let metaMenuDisplay;
    if (!this.state.loading) {
      console.log('not loading');
      metaMenuDisplay = React.createElement(
        "div",
        { className: "metamenu" },
        React.createElement(DomainsMenu, {
          domains: appHelpers.getDomainsArray(),
          baseUrl: this.state.baseUrl,
          sName: this.state.sName
        }),
        React.createElement(UserMenu, {
          user: this.state.user,
          blogUrl: this.state.blogUrl,
          loginUrl: this.state.loginUrl
        })
      );
    }
    return React.createElement(
      "nav",
      { id: "metaheader-nav", className: "metaheader" },
      metaMenuDisplay
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
              { href: "https://www.opencode.net/" },
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
    const domainsDisplay = this.props.domains.filter(this.filterDomainsByMenuGroup).map((domain, index) => {
      let domainPrefix = "http://";
      if (domain.menuhref.indexOf('pling.cc') === -1 && domain.menuhref.indexOf('www') === -1) {
        console.log(domain.menuhref.indexOf('www'));
        domainPrefix += "www.";
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
    if (this.props.user) {
      userDropdownDisplay = React.createElement(UserLoginMenuContainer, {
        user: this.props.user
      });
      userAppsContextDisplay = React.createElement(UserContextMenuContainer, {
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
        userAppsContextDisplay,
        userDropdownDisplay
      )
    );
  }
}

class UserContextMenuContainer extends React.Component {
  constructor(props) {
    super(props);
    this.state = {
      loading: true
    };
  }

  componentDidMount() {
    const self = this;
    $.ajax({ url: "https://gitlab.opencode.net/api/v4/users?username=" + this.props.user.username, cache: false }).done(function (response) {
      const gitlabLink = "https://gitlab.opencode.net/dashboard/issues?assignee_id=" + response[0].id;
      self.setState({ gitlabLink: gitlabLink, loading: false });
    });
  }

  render() {

    const messagesLink = "https://forum.opendesktop.org/u/" + this.props.user.username + "/messages";

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
            { id: "opencode-link-item" },
            React.createElement(
              "a",
              { href: "https://gitlab.opencode.net/dashboard/projects" },
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
              { href: messagesLink },
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
          React.createElement("img", { src: this.props.user.avatar })
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
                  React.createElement("img", { src: this.props.user.profile_image_url })
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
          React.createElement("li", { id: "main-seperator", role: "separator", className: "divider" }),
          React.createElement(
            "li",
            { className: "buttons" },
            React.createElement(
              "a",
              { href: "/settings/", className: "btn btn-default btn-metaheader" },
              "Settings"
            ),
            React.createElement(
              "a",
              { href: "/logout/", className: "btn btn-default pull-right btn-metaheader" },
              "Logout"
            )
          )
        )
      )
    );
  }
}

ReactDOM.render(React.createElement(MetaHeader, null), document.getElementById('metaheader'));
