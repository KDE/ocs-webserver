/* ========================================================================
 * Bootstrap: dropdown.js v3.4.0
 * http://getbootstrap.com/javascript/#dropdowns
 * ========================================================================
 * Copyright 2011-2018 Twitter, Inc.
 * Licensed under MIT (https://github.com/twbs/bootstrap/blob/master/LICENSE)
 * ======================================================================== */

+function ($) {
  'use strict';

  // DROPDOWN CLASS DEFINITION
  // =========================

  var backdrop = '.dropdown-backdrop';
  var toggle = '[data-toggle="dropdown"]';
  var Dropdown = function (element) {
    $(element).on('click.bs.dropdown', this.toggle);
  };

  Dropdown.VERSION = '3.4.0';

  function getParent($this) {
    var selector = $this.attr('data-target');

    if (!selector) {
      selector = $this.attr('href');
      selector = selector && /#[A-Za-z]/.test(selector) && selector.replace(/.*(?=#[^\s]*$)/, ''); // strip for ie7
    }

    var $parent = selector && $(document).find(selector);

    return $parent && $parent.length ? $parent : $this.parent();
  }

  function clearMenus(e) {
    if (e && e.which === 3) return;
    $(backdrop).remove();
    $(toggle).each(function () {
      var $this = $(this);
      var $parent = getParent($this);
      var relatedTarget = { relatedTarget: this };

      if (!$parent.hasClass('open')) return;

      if (e && e.type == 'click' && /input|textarea/i.test(e.target.tagName) && $.contains($parent[0], e.target)) return;

      $parent.trigger(e = $.Event('hide.bs.dropdown', relatedTarget));

      if (e.isDefaultPrevented()) return;

      $this.attr('aria-expanded', 'false');
      $parent.removeClass('open').trigger($.Event('hidden.bs.dropdown', relatedTarget));
    });
  }

  Dropdown.prototype.toggle = function (e) {
    var $this = $(this);

    if ($this.is('.disabled, :disabled')) return;

    var $parent = getParent($this);
    var isActive = $parent.hasClass('open');

    clearMenus();

    if (!isActive) {
      if ('ontouchstart' in document.documentElement && !$parent.closest('.navbar-nav').length) {
        // if mobile we use a backdrop because click events don't delegate
        $(document.createElement('div')).addClass('dropdown-backdrop').insertAfter($(this)).on('click', clearMenus);
      }

      var relatedTarget = { relatedTarget: this };
      $parent.trigger(e = $.Event('show.bs.dropdown', relatedTarget));

      if (e.isDefaultPrevented()) return;

      $this.trigger('focus').attr('aria-expanded', 'true');

      $parent.toggleClass('open').trigger($.Event('shown.bs.dropdown', relatedTarget));
    }

    return false;
  };

  Dropdown.prototype.keydown = function (e) {
    if (!/(38|40|27|32)/.test(e.which) || /input|textarea/i.test(e.target.tagName)) return;

    var $this = $(this);

    e.preventDefault();
    e.stopPropagation();

    if ($this.is('.disabled, :disabled')) return;

    var $parent = getParent($this);
    var isActive = $parent.hasClass('open');

    if (!isActive && e.which != 27 || isActive && e.which == 27) {
      if (e.which == 27) $parent.find(toggle).trigger('focus');
      return $this.trigger('click');
    }

    var desc = ' li:not(.disabled):visible a';
    var $items = $parent.find('.dropdown-menu' + desc);

    if (!$items.length) return;

    var index = $items.index(e.target);

    if (e.which == 38 && index > 0) index--; // up
    if (e.which == 40 && index < $items.length - 1) index++; // down
    if (!~index) index = 0;

    $items.eq(index).trigger('focus');
  };

  // DROPDOWN PLUGIN DEFINITION
  // ==========================

  function Plugin(option) {
    return this.each(function () {
      var $this = $(this);
      var data = $this.data('bs.dropdown');

      if (!data) $this.data('bs.dropdown', data = new Dropdown(this));
      if (typeof option == 'string') data[option].call($this);
    });
  }

  var old = $.fn.dropdown;

  $.fn.dropdown = Plugin;
  $.fn.dropdown.Constructor = Dropdown;

  // DROPDOWN NO CONFLICT
  // ====================

  $.fn.dropdown.noConflict = function () {
    $.fn.dropdown = old;
    return this;
  };

  // APPLY TO STANDARD DROPDOWN ELEMENTS
  // ===================================

  $(document).on('click.bs.dropdown.data-api', clearMenus).on('click.bs.dropdown.data-api', '.dropdown form', function (e) {
    e.stopPropagation();
  }).on('click.bs.dropdown.data-api', toggle, Dropdown.prototype.toggle).on('keydown.bs.dropdown.data-api', toggle, Dropdown.prototype.keydown).on('keydown.bs.dropdown.data-api', '.dropdown-menu', Dropdown.prototype.keydown);
}(jQuery);
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

  function getUserQueryUrl(hostname) {
    let userQueryUrl;
    if (hostname === "www.opendesktop.cc") {
      userQueryUrl = "https://www.opendesktop.cc/user/userdataajax";
    } else if (hostname === "gitlab.pling.cc") {
      userQueryUrl = "https://gitlab.pling.cc/external/get_ocs_data.php?url=home/storenameajax";
    }
    return userQueryUrl;
  }

  function getBaseQueryUrl(hostname) {
    let baseQueryUrl;
    if (hostname === "www.opendesktop.cc") {
      baseQueryUrl = "https://www.opendesktop.cc/home/baseurlajax";
    } else if (hostname === "gitlab.pling.cc") {
      baseQueryUrl = "https://gitlab.pling.cc/external/get_ocs_data.php?url=home/baseurlajax";
    }
    return baseQueryUrl;
  }

  function getForumQueryUrl(hostname) {
    let forumQueryUrl;
    if (hostname === "www.opendesktop.cc") {
      forumQueryUrl = "https://www.opendesktop.cc/home/forumurlajax";
    } else if (hostname === "gitlab.pling.cc") {
      forumQueryUrl = "https://gitlab.pling.cc/external/get_ocs_data.php?url=home/forumurlajax";
    }
    return forumQueryUrl;
  }

  function getBlogQueryUrl(hostname) {
    let blogQueryUrl;
    if (hostname === "www.opendesktop.cc") {
      blogQueryUrl = "https://www.opendesktop.cc/home/blogurlajax";
    } else if (hostname === "gitlab.pling.cc") {
      blogQueryUrl = "https://gitlab.pling.cc/external/get_ocs_data.php?url=home/blogurlajax";
    }
    return blogQueryUrl;
  }

  return {
    generateMenuGroupsArray,
    getDomainsArray,
    getUserQueryUrl,
    getForumQueryUrl,
    getBaseQueryUrl,
    getBlogQueryUrl
  };
}();
class MetaHeader extends React.Component {
  constructor(props) {
    super(props);
    this.state = {
      baseUrl: "https://www.opendesktop.cc",
      blogUrl: "https://blog.opendesktop.org",
      loginUrl: "https://www.opendesktop.cc/login/redirect/TFVIFZfgicowyCW5clpDz3sfM1rVUJsb_GwOHCL1oRyPOkMMVswIRPd2kvVz5oQW",
      user: user,
      sName: sName,
      loading: false
    };
    this.getUser = this.getUser.bind(this);
    this.getUrls = this.getUrls.bind(this);
  }

  componentDidMount() {
    console.log('component did mount');
    this.getUser();
    this.getUrls();
  }

  getUser() {
    console.log('get user');
    const userQueryUrl = appHelpers.getUserQueryUrl(window.location.hostname);
    console.log(userQueryUrl);
    const self = this;
    $.ajax({
      url: userQueryUrl,
      method: 'get',
      dataType: 'jsonp',
      done: function (response) {
        console.log('done');
        console.log(response);
      },
      error: function (response) {
        console.log('get user');
        console.log(response);
        const res = JSON.parse(response.responseText);
        if (res.status === "success") {
          self.setState({ user: res.data, loading: false });
        } else {
          self.setState({ loading: false });
        }
      },
      success: function (response) {
        console.log('success');
        console.log(response);
      }
    });
  }

  getUrls() {
    const self = this;

    const forumQueryUrl = appHelpers.getForumQueryUrl(window.location.hostname);
    $.ajax({
      url: forumQueryUrl,
      method: 'get',
      dataType: 'jsonp',
      error: function (response) {
        console.log('get forum');
        console.log(response);
        const res = JSON.parse(response.responseText);
        if (res.status === "success") {
          self.setState({ forumUrl: res.data.url_forum });
        }
      }
    });

    const blogQueryUrl = appHelpers.getBlogQueryUrl(window.location.hostname);
    $.ajax({
      url: blogQueryUrl,
      method: 'get',
      dataType: 'jsonp',
      error: function (response) {
        console.log('get blog');
        console.log(response);
        const res = JSON.parse(response.responseText);
        if (res.status === "success") {
          self.setState({ blogUrl: res.data.url_blog });
        }
      }
    });

    const baseQueryUrl = appHelpers.getBaseQueryUrl(window.location.hostname);
    $.ajax({
      url: baseQueryUrl,
      method: 'get',
      dataType: 'jsonp',
      error: function (response) {
        console.log('get base');
        console.log('error');
        console.log(response);
        const res = JSON.parse(response.responseText);
        if (res.status === "success") {
          let baseUrl = res.data.base_url;
          if (res.data.base_url.indexOf('http') === -1) {
            baseUrl = "http://" + res.data.base_url;
          }
          self.setState({ baseUrl: baseUrl });
        }
      }
    });
  }

  render() {
    let metaMenuDisplay;
    if (!this.state.loading) {
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
          { href: this.props.baseUrl },
          React.createElement("img", { src: this.props.baseUrl + "/images/system/ocs-logo-rounded-16x16.png", className: "logo" }),
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
    if (this.props.user.member_id) {
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

    let plingListUrl = "https://www.opendesktop.cc/#plingList",
        ocsapiContentUrl = "https://www.opendesktop.cc/#ocsapiContent",
        aboutContentUrl = "https://www.opendesktop.cc/#aboutContent",
        linkTarget = "_blank";

    if (window.location.hostname === "www.opendesktop.cc") {
      plingListUrl = "https://www.opendesktop.cc/plings";
      ocsapiContentUrl = "https://www.opendesktop.cc/partials/ocsapicontent.phtml";
      aboutContentUrl = "https://www.opendesktop.cc/partials/about.phtml";
      linkTarget = "";
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
            { href: "https://www.opendesktop.cc/community" },
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
            { id: "plingList", className: "popuppanel", target: linkTarget, href: plingListUrl },
            "What are Plings?"
          )
        ),
        React.createElement(
          "li",
          null,
          React.createElement(
            "a",
            { id: "ocsapiContent", className: "popuppanel", target: linkTarget, href: ocsapiContentUrl },
            "API"
          )
        ),
        React.createElement(
          "li",
          null,
          React.createElement(
            "a",
            { id: "aboutContent", className: "popuppanel", target: linkTarget, href: aboutContentUrl },
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
      loading: true,
      ariaExpanded: false
    };
    this.toggleDropdown = this.toggleDropdown.bind(this);
  }

  componentDidMount() {
    const self = this;
    $.ajax({ url: "https://gitlab.opencode.net/api/v4/users?username=" + this.props.user.username, cache: false }).done(function (response) {
      const gitlabLink = "https://gitlab.opencode.net/dashboard/issues?assignee_id=" + response[0].id;
      self.setState({ gitlabLink: gitlabLink, loading: false });
    });
  }

  toggleDropdown(e) {
    const ariaExpanded = this.state.ariaExpanded === true ? false : true;
    this.setState({ ariaExpanded: ariaExpanded });
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
            "aria-expanded": this.state.ariaExpanded,
            onClick: this.toggleDropdown },
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
          React.createElement("li", { id: "main-seperator", role: "separator", className: "divider" }),
          React.createElement(
            "li",
            { className: "buttons" },
            React.createElement(
              "a",
              { href: "https://www.opendesktop.cc/settings/", className: "btn btn-default btn-metaheader" },
              "Settings"
            ),
            React.createElement(
              "a",
              { href: "https://www.opendesktop.cc/logout/", className: "btn btn-default pull-right btn-metaheader" },
              "Logout"
            )
          )
        )
      )
    );
  }
}

ReactDOM.render(React.createElement(MetaHeader, null), document.getElementById('metaheader'));
