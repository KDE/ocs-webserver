window.appHelpers = (function(){

  function getEnv(domain){

    let env;
    let lastDotSplit = this.splitByLastDot(domain);

    if (lastDotSplit.indexOf('/') > -1){
      lastDotSplit = lastDotSplit.split('/')[0];
    }

    if (lastDotSplit === 'com' || lastDotSplit === 'org'){
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
