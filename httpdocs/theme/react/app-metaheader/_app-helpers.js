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

  function getDeviceFromWidth(){
    let device;
    if (width < 1250 && width >= 1000){
      device = "large";
    } else if (width < 1000 && width >= 661){
      device = "mid";
    } else if (width < 661 && width >= 400){
      device = "tablet";
    } else if (width < 400){
      device = "phone"
    }
    return device;
  }

  return {
    generateMenuGroupsArray,
    getDeviceFromWidth
  }

}());
