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
    if (width > 1720){
      device = "very-huge";
    } else if (width < 1720 && width > 1500){
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

  function getFileSize(size) {
    if (isNaN(size))
    	size = 0;

    if (size < 1024)
    	return size + ' Bytes';

    size /= 1024;

    if (size < 1024)
    	return size.toFixed(2) + ' Kb';

    size /= 1024;

    if (size < 1024)
    	return size.toFixed(2) + ' Mb';

    size /= 1024;

    if (size < 1024)
    	return size.toFixed(2) + ' Gb';

    size /= 1024;

    return size.toFixed(2) + ' Tb';
	}

  function generateFilterUrl(location,currentCat){
    let link = {}
    console.log(currentCat);
    if (currentCat && currentCat !== 0){
      link.base = "/browse/cat/" + currentCat + "/ord/";
    } else {
      link.base = "/browse/ord/";
    }
    if (location.search) link.search = location.search;
    return link;
  }

  function generateFileDownloadHash(file,env){
    let salt;
    if (env === "test"){
      salt = "vBHnf7bbdhz120bhNsd530LsA2mkMvh6sDsCm4jKlm23D186Fj";
    } else {
      salt = "Kcn6cv7&dmvkS40Hna§4ffcvl=021nfMs2sdlPs123MChf4s0K";
    }

    const timestamp = Date.now() + 3600;
    const hash = md5(salt,file.collection_id+timestamp);
    return hash;
    /*
    $salt = PPLOAD_DOWNLOAD_SECRET;
    $collectionID = $productInfo->ppload_collection_id;
    $timestamp = time() + 3600; // one hour valid
    $hash = md5($salt . $collectionID . $timestamp);
    */
  }

  return {
    getEnv,
    getDeviceWidth,
    splitByLastDot,
    getTimeAgo,
    getFileSize,
    generateFilterUrl,
    generateFileDownloadHash
  }

}());
