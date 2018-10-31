window.appHelpers = (function(){

  function getEnv(domain){
    let env;
    if (this.splitByLastDot(domain) === 'com' || this.splitByLastDot(domain) === 'org'){
      env = 'live';
    } else {
      env = 'test';
    }
    return env;
  }

  function splitByLastDot(text) {
    var index = text.lastIndexOf('.');
    return text.slice(index + 1);
  }

  return {
    getEnv,
    splitByLastDot
  }

}());
