window.appHelpers = (function(){

  function generateMenuGroupsArray(domains){
    let menuGroups = [];
    domains.forEach(function(domain,index){
      if (menuGroups.indexOf(domain.menugroup) === -1){
        menuGroups.push(domain.menugroup);
      } else {
        console.log(domain.menugroup);
      }
    });
    console.log(menuGroups);
    return menuGroups;
  }

  return {
    generateMenuGroupsArray
  }

}());
