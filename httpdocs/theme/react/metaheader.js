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

  function getLoginQueryUrl(hostname) {
    let loginQuery = {};
    if (hostname === "www.opendesktop.cc") {
      loginQuery.url = "https://www.opendesktop.cc/user/loginurlajax&returnurl=https://www.opendesktop.cc";
      loginQuery.dataType = "jsonp";
    } else if (hostname === "gitlab.pling.cc") {
      loginQuery.url = "https://gitlab.pling.cc/external/get_ocs_data.php?url=home/loginurlajax&returnurl=https://gitlab.pling.cc";
      loginQuery.dataType = "jsonp";
    } else if (hostname === "forum.opendesktop.cc") {
      loginQuery.url = "https://forum.opendesktop.cc:8443/get_ocs_data.php?url=home/loginurlajax&returnurl=https://forum.opendesktop.cc";
      loginQuery.dataType = "json";
    }
    return loginQuery;
  }

  function getUserQueryUrl(hostname) {
    let userQuery = {};
    if (hostname === "www.opendesktop.cc") {
      userQuery.url = "https://www.opendesktop.cc/user/userdataajax";
      userQuery.dataType = "jsonp";
    } else if (hostname === "gitlab.pling.cc") {
      userQuery.url = "https://gitlab.pling.cc/external/get_ocs_data.php?url=user/userdataajax";
      userQuery.dataType = "jsonp";
    } else if (hostname === "forum.opendesktop.cc") {
      userQuery.url = "https://forum.opendesktop.cc:8443/get_ocs_userdata.php";
      userQuery.dataType = "json";
    }
    return userQuery;
  }

  function getDomainsQueryUrl(hostname) {
    let domainsQuery = {};
    if (hostname === "www.opendesktop.cc") {
      domainsQuery.url = "https://www.opendesktop.cc/home/domainsajax";
      domainsQuery.dataType = "jsonp";
    } else if (hostname === "gitlab.pling.cc") {
      domainsQuery.url = "https://gitlab.pling.cc/external/get_ocs_data.php?url=home/domainsajax";
      domainsQuery.dataType = "jsonp";
    } else if (hostname === "forum.opendesktop.cc") {
      domainsQuery.url = "https://forum.opendesktop.cc:8443/get_ocs_data.php?url=home/domainsajax";
      domainsQuery.dataType = "json";
    }
    return domainsQuery;
  }

  function getBaseQueryUrl(hostname) {
    let baseQuery = {};
    if (hostname === "www.opendesktop.cc") {
      baseQuery.url = "https://www.opendesktop.cc/home/baseurlajax";
      baseQuery.dataType = "jsonp";
    } else if (hostname === "gitlab.pling.cc") {
      baseQuery.url = "https://gitlab.pling.cc/external/get_ocs_data.php?url=home/baseurlajax";
      baseQuery.dataType = "jsonp";
    } else if (hostname === "forum.opendesktop.cc") {
      baseQuery.url = "https://forum.opendesktop.cc:8443/get_ocs_data.php?url=home/baseurlajax";
      baseQuery.dataType = "json";
    }
    return baseQuery;
  }

  function getForumQueryUrl(hostname) {
    let forumQuery = {};
    if (hostname === "www.opendesktop.cc") {
      forumQuery.url = "https://www.opendesktop.cc/home/forumurlajax";
      forumQuery.dataType = "jsonp";
    } else if (hostname === "gitlab.pling.cc") {
      forumQuery.url = "https://gitlab.pling.cc/external/get_ocs_data.php?url=home/forumurlajax";
      forumQuery.dataType = "jsonp";
    } else if (hostname === "forum.opendesktop.cc") {
      forumQuery.url = "https://forum.opendesktop.cc:8443/get_ocs_data.php?url=home/forumurlajax";
      forumQuery.dataType = "json";
    }
    return forumQuery;
  }

  function getBlogQueryUrl(hostname) {
    let blogQuery = {};
    if (hostname === "www.opendesktop.cc") {
      blogQuery.url = "https://www.opendesktop.cc/home/blogurlajax";
      blogQuery.dataType = "jsonp";
    } else if (hostname === "gitlab.pling.cc") {
      blogQuery.url = "https://gitlab.pling.cc/external/get_ocs_data.php?url=home/blogurlajax";
      blogQuery.dataType = "jsonp";
    } else if (hostname === "forum.opendesktop.cc") {
      blogQuery.url = "https://forum.opendesktop.cc:8443/get_ocs_data.php?url=home/blogurlajax";
      blogQuery.dataType = "json";
    }
    return blogQuery;
  }

  function getStoreQueryUrl(hostname) {
    let storeQuery = {};
    if (hostname === "www.opendesktop.cc") {
      storeQuery.url = "";
      storeQuery.dataType = "jsonp";
    } else if (hostname === "gitlab.pling.cc") {
      storeQuery.url = "https://gitlab.pling.cc/external/get_ocs_data.php?url=home/storenameajax";
      storeQuery.dataType = "jsonp";
    } else if (hostname === "forum.opendesktop.cc") {
      storeQuery.url = "https://forum.opendesktop.cc:8443/get_ocs_data.php?url=home/storenameajax";
      storeQuery.dataType = "json";
    }
    return storeQuery;
  }

  return {
    generateMenuGroupsArray,
    getDomainsArray,
    getLoginQueryUrl,
    getUserQueryUrl,
    getDomainsQueryUrl,
    getForumQueryUrl,
    getBaseQueryUrl,
    getBlogQueryUrl,
    getStoreQueryUrl
  };
}();
