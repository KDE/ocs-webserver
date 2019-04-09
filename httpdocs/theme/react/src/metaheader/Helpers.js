const Helpers = {
    generateMenuGroupsArray: function(domains){
      let menuGroups = [];
      domains.forEach(function (domain, index) {
        if (menuGroups.indexOf(domain.menugroup) === -1) {
          menuGroups.push(domain.menugroup);
        }
      });
      return menuGroups;
    },
    getDeviceFromWidth: function(width){
      let device;
      if (width >= 910) {
        device = "large";
      } else if (width < 910 && width >= 610) {
        device = "mid";
      } else if (width < 610) {
        device = "tablet";
      }
      return device;


    },
    getPopupUrl: function(key, isExternal, baseUrl){
      let url = baseUrl;
      return url;
    },
    generatePopupLinks: function(){
      let pLink = {};
      pLink.plingListUrl = "/#plingList", pLink.ocsapiContentUrl = "/#ocsapiContent", pLink.aboutContentUrl = "/#aboutContent", pLink.linkTarget = "_blank";

      if (window.location.hostname.indexOf('opendesktop') === -1 || window.location.hostname === "opencode.net" || window.location.hostname === "git.opendesktop.cc" || window.location.hostname === "forum.opendesktop.org" || window.location.hostname === "forum.opendesktop.cc" || window.location.hostname === "my.opendesktop.org" || window.location.hostname === "my.opendesktop.cc") {
        pLink.plingListUrl = "/plings";
        pLink.ocsapiContentUrl = "/partials/ocsapicontent.phtml";
        pLink.aboutContentUrl = "/partials/about.phtml";
        pLink.linkTarget = "";
      }
      return pLink;
    }
}

export default Helpers;
