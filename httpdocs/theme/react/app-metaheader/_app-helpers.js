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
    } else if (width < 910 && width >= 600){
      device = "mid";
    } else if (width < 600){
      device = "tablet";
    }
    return device;
  }

  return {
    generateMenuGroupsArray,
    getDeviceFromWidth
  }

}());
