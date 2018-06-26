window.appHelpers = (function(){

  function getEnv(domain){
    let env;
    if (this.splitByLastDot(domain) === 'com'){
      env = 'live';
    } else {
      env = 'test';
    }
    return env;
  }

  function getDeviceWidth(width){
    let device;
    if (width > 1500){
      device = "huge";
    } else if (width < 1500 && width > 1250){
      device = "full";
    } else if (width < 1250 && width >= 1000){
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

  function splitByLastDot(text) {
      var index = text.lastIndexOf('.');
      return text.slice(index + 1);
  }

  function getTimeAgo(datetime){
    const a = timeago().format(datetime);
    return a;
  }

  return {
    getEnv,
    getDeviceWidth,
    splitByLastDot,
    getTimeAgo
  }

}());
