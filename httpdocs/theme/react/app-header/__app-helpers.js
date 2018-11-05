window.appHelpers = (function(){

  function getEnv(domain){

    let env;
    let lastDotSplit = this.splitByLastDot(domain);

    if (lastDotSplit.indexOf('/') > -1){
      lastDotSplit = lastDotSplit.split('/')[0];
    }

    console.log(lastDotSplit);

    if ( === 'com' || this.splitByLastDot(domain) === 'org'){
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
