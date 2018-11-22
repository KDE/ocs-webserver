window.appHelpers = (function(){

  function generateMenuGroupsArray(domains){
    let menuGroups = [];
    domains.forEach(function(domain,index){
      if (menuGroups.indexOf(domain.menugroup) === -1){
        menuGroups.push(domain.menugroup);
      }
    });
    return menuGroups;
  }

  function getDeviceFromWidth(width){
    let device;
    if (width >= 910){
      device = "large";
    } else if (width < 910 && width >= 610){
      device = "mid";
    } else if (width < 610){
      device = "tablet";
    }
    return device;
  }

  function generatePopupLinks(){
    let pLink;
    pLink.plingListUrl = "/#plingList",
    pLink.ocsapiContentUrl = "/#ocsapiContent",
    pLink.aboutContentUrl = "/#aboutContent",
    pLink.linkTarget = "_blank";

    if (window.location.hostname.indexOf('opendesktop') === -1 ||
        window.location.hostname === "git.opendesktop.org" ||
        window.location.hostname === "forum.opendesktop.org" ||
        window.location.hostname === "my.opendesktop.org"){
        pLink.plingListUrl = "/plings";
        pLink.ocsapiContentUrl = "/partials/ocsapicontent.phtml";
        pLink.aboutContentUrl = "/partials/about.phtml";
        pLink.linkTarget = "";
    }
    return pLink;
  }

  return {
    generateMenuGroupsArray,
    getDeviceFromWidth,
    generatePopupLinks
  }

}());
