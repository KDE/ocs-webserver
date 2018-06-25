window.appHelpers = (function(){

  function getTimeAgo(datetime){
    const a = timeago().format(datetime);
    return a;
  }

  function getDeviceWidth(width){
    let device;
    if (width > 1250){
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

  function getNumberOfProducts(device){
    let num;
    if (device === "full"){
      num = 5;
    } else if (device === "large"){
      num = 4;
    } else if (device === "mid"){
      num = 3;
    } else if (device === "tablet"){
      num = 2;
    } else if (device === "phone"){
      num = 1;
    }
    return num;
  }

  function  splitByLastDot(text) {
      var index = text.lastIndexOf('.');
      return text.slice(index + 1);
  }


  return {
    getTimeAgo,
    getDeviceWidth,
    getNumberOfProducts,
    splitByLastDot
  }

}());
