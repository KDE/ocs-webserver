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

  return {
    generateMenuGroupsArray
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
    this.getUser = this.getUser.bind(this);
  }

  componentDidMount() {
    console.log(this.state.baseUrl);
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

  render() {
    return React.createElement(
      'nav',
      { id: 'metaheader-nav', className: 'metaheader' },
      React.createElement(
        'div',
        { className: 'metamenu' },
        React.createElement(DomainsMenu, {
          domains: domains,
          baseUrl: this.state.baseUrl,
          forumUrl: this.state.forumUrl,
          sName: this.state.sName
        }),
        React.createElement(UserMenu, {
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
      'ul',
      { className: 'metaheader-menu left', id: 'domains-menu' },
      React.createElement(
        'li',
        { className: 'active' },
        React.createElement(
          'a',
          { href: this.props.baseUrl },
          React.createElement('img', { src: this.props.baseUrl + "/images/system/ocs-logo-rounded-16x16.png", className: 'logo' }),
          'openDesktop.org :'
        )
      ),
      React.createElement(
        'li',
        { id: 'domains-dropdown-menu', className: 'dropdown' },
        React.createElement(
          'a',
          { id: 'dropdownMenu3',
            'data-toggle': 'dropdown',
            'aria-haspopup': 'true',
            'aria-expanded': 'true' },
          'Themes & Apps'
        ),
        React.createElement(
          'ul',
          { className: 'dropdown-menu dropdown-menu-right', 'aria-labelledby': 'dropdownMenu3' },
          React.createElement(
            'li',
            { className: 'submenu-container' },
            React.createElement(
              'ul',
              null,
              menuGroupsDisplayLeft
            )
          ),
          React.createElement(
            'li',
            { className: 'submenu-container' },
            React.createElement(
              'ul',
              null,
              menuGroupsDisplayRight
            )
          )
        )
      ),
      React.createElement(
        'li',
        { id: 'discussion-boards', className: 'dropdown' },
        React.createElement(
          'a',
          { id: 'dropdownMenu4',
            'data-toggle': 'dropdown',
            'aria-haspopup': 'true',
            'aria-expanded': 'true' },
          'Discussion Boards'
        ),
        React.createElement(
          'ul',
          { className: 'dropdown-menu dropdown-menu-right', 'aria-labelledby': 'dropdownMenu4' },
          React.createElement(
            'li',
            null,
            React.createElement(
              'a',
              { href: this.props.forumUrl + "/c/general" },
              'General'
            )
          ),
          React.createElement(
            'li',
            null,
            React.createElement(
              'a',
              { href: this.props.forumUrl + "/c/themes-and-apps" },
              'Themes & Apps'
            )
          ),
          React.createElement(
            'li',
            null,
            React.createElement(
              'a',
              { href: 'https://www.opencode.net/' },
              'Coding'
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
      let domainPrefix = "";
      if (domain.menuhref.indexOf('https://') === -1 && domain.menuhref.indexOf('http://') === -1) {
        domainPrefix += "https://";
        if (domain.menuhref.indexOf('www') === -1) {
          domainPrefix += "www.";
        }
      }
      return React.createElement(
        'li',
        { key: index },
        React.createElement(
          'a',
          { href: domainPrefix + domain.menuhref },
          domain.name
        )
      );
    });

    return React.createElement(
      'li',
      null,
      React.createElement(
        'a',
        { className: 'groupname' },
        React.createElement(
          'b',
          null,
          this.props.menuGroup
        )
      ),
      React.createElement(
        'ul',
        { className: 'domains-sub-menu' },
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
        'li',
        { id: 'user-login-container' },
        React.createElement(
          'a',
          { href: this.props.loginUrl, className: 'btn btn-metaheader' },
          'Login'
        )
      );
    }

    let plingListUrl = "/#plingList",
        ocsapiContentUrl = "/partials/#ocsapiContent",
        aboutContentUrl = "/partials/#aboutContent",
        linkTarget = "_blank";

    console.log(window.location.hostname);
    console.log(this.props.baseUrl);
    if (window.location.hostname === this.props.baseUrl.split('https://')[1]) {
      plingListUrl = "/plings";
      ocsapiContentUrl = "/partials/ocsapicontent.phtml";
      aboutContentUrl = "/partials/about.phtml";
      linkTarget = "";
    }

    return React.createElement(
      'div',
      { id: 'user-menu-container', className: 'right' },
      React.createElement(
        'ul',
        { className: 'metaheader-menu', id: 'user-menu' },
        React.createElement(
          'li',
          null,
          React.createElement(
            'a',
            { href: this.props.baseUrl + "/community" },
            'Community'
          )
        ),
        React.createElement(
          'li',
          null,
          React.createElement(
            'a',
            { href: this.props.blogUrl, target: '_blank' },
            'Blog'
          )
        ),
        React.createElement(
          'li',
          null,
          React.createElement(
            'a',
            { id: 'plingList', className: 'popuppanel', target: linkTarget, href: this.props.baseUrl + plingListUrl },
            'What are Plings?'
          )
        ),
        React.createElement(
          'li',
          null,
          React.createElement(
            'a',
            { id: 'ocsapiContent', className: 'popuppanel', target: linkTarget, href: this.props.baseUrl + ocsapiContentUrl },
            'API'
          )
        ),
        React.createElement(
          'li',
          null,
          React.createElement(
            'a',
            { id: 'aboutContent', className: 'popuppanel', target: linkTarget, href: this.props.baseUrl + aboutContentUrl },
            'About'
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
      'li',
      { ref: node => this.node = node, id: 'user-context-menu-container' },
      React.createElement(
        'div',
        { className: "user-dropdown " + this.state.dropdownClass },
        React.createElement(
          'button',
          {
            className: 'btn btn-default dropdown-toggle', type: 'button', onClick: this.toggleDropDown },
          React.createElement('span', { className: 'th-icon' })
        ),
        React.createElement(
          'ul',
          { id: 'user-context-dropdown', className: 'dropdown-menu dropdown-menu-right' },
          React.createElement(
            'li',
            { id: 'opencode-link-item' },
            React.createElement(
              'a',
              { href: this.props.gitlabUrl + "/dashboard/projects" },
              React.createElement('div', { className: 'icon' }),
              React.createElement(
                'span',
                null,
                'Projects'
              )
            )
          ),
          React.createElement(
            'li',
            { id: 'issues-link-item' },
            React.createElement(
              'a',
              { href: this.state.gitlabLink },
              React.createElement('div', { className: 'icon' }),
              React.createElement(
                'span',
                null,
                'Issues'
              )
            )
          ),
          React.createElement(
            'li',
            { id: 'messages-link-item' },
            React.createElement(
              'a',
              { href: this.props.forumUrl + "/u/" + this.props.user.username + "/messages" },
              React.createElement('div', { className: 'icon' }),
              React.createElement(
                'span',
                null,
                'Messages'
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
      'li',
      { id: 'user-login-menu-container', ref: node => this.node = node },
      React.createElement(
        'div',
        { className: "user-dropdown " + this.state.dropdownClass },
        React.createElement(
          'button',
          {
            className: 'btn btn-default dropdown-toggle',
            type: 'button',
            id: 'userLoginDropdown' },
          React.createElement('img', { className: 'th-icon', src: this.props.user.avatar })
        ),
        React.createElement(
          'ul',
          { className: 'dropdown-menu dropdown-menu-right' },
          React.createElement(
            'li',
            { id: 'user-info-menu-item' },
            React.createElement(
              'div',
              { id: 'user-info-section' },
              React.createElement(
                'div',
                { className: 'user-avatar' },
                React.createElement(
                  'div',
                  { className: 'no-avatar-user-letter' },
                  React.createElement('img', { src: this.props.user.avatar })
                )
              ),
              React.createElement(
                'div',
                { className: 'user-details' },
                React.createElement(
                  'ul',
                  null,
                  React.createElement(
                    'li',
                    null,
                    React.createElement(
                      'b',
                      null,
                      this.props.user.username
                    )
                  ),
                  React.createElement(
                    'li',
                    null,
                    this.props.user.mail
                  )
                )
              )
            )
          ),
          React.createElement(
            'li',
            { className: 'buttons' },
            React.createElement(
              'a',
              { href: this.props.baseUrl + "/settings/", className: 'btn btn-default btn-metaheader' },
              'Settings'
            ),
            React.createElement(
              'a',
              { href: this.props.logoutUrl, className: 'btn btn-default pull-right btn-metaheader' },
              'Logout'
            )
          )
        )
      )
    );
  }
}

ReactDOM.render(React.createElement(MetaHeader, null), document.getElementById('metaheader'));
