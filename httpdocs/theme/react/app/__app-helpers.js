window.appHelpers = (function(){

  function getTimeAgo(datetime){
    const a = timeago().format(datetime);
    return a;
  }

  return {
    getTimeAgo
  }

}());
