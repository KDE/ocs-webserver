window.productHelpers = (function(){

  function getNumberOfProducts(device,numRows){
    let num;
    if (device === "very-huge"){
      num = 7;
    } else if (device === "huge"){
      num = 6;
    } else if (device === "full"){
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
    if (numRows) num = num * numRows;
    return num;
  }

  function generatePaginationObject(numPages,pathname,currentCategoy,order,page){
    let pagination = [];

    let baseHref = "/browse";
    if (pathname.indexOf('cat') > -1){
      baseHref += "/cat/" + currentCategoy;
    }

    if (page > 1){
      const prev = {
        number:'previous',
        link:baseHref + "/page/" + parseInt(page - 1) + "/ord/" + order
      }
      pagination.push(prev);
    }

    for (var i = 0; i < numPages; i++){
      const p = {
        number:parseInt(i + 1),
        link:baseHref + "/page/" + parseInt(i + 1) + "/ord/" + order
      }
      pagination.push(p);
    }

    if (page < numPages){
      const next = {
        number:'next',
        link:baseHref + "/page/" + parseInt(page + 1) + "/ord/" + order
      }
      pagination.push(next);
    }

    return pagination;
  }

  function calculateProductRatings(ratings){
    let pRating;
    let totalUp = 0,
        totalDown = 0;
    ratings.forEach(function(r,index){
      if (r.rating_active === "1"){
        if (r.user_like === "1"){
          totalUp += 1;
        } else if (r.user_dislike === "1"){
          totalDown += 1;
        }
      }
    });
    pRating = 100 / ratings.length * (totalUp - totalDown);
    return pRating;
  }

  function getActiveRatingsNumber(ratings){
    let activeRatingsNumber = 0;
    ratings.forEach(function(r,index){
      if (r.rating_active === "1"){
        activeRatingsNumber += 1;
      }
    });
    return activeRatingsNumber;
  }

  function getFilesSummary(files){
    let summery = {
      downloads:0,
      archived:0,
      fileSize:0,
      total:0,
      archived:0
    }
    files.forEach(function(file,index){
      if (file.active === "1"){
        summery.total += 1;
      } else {
        summery.archived += 1;
      }
      summery.fileSize += parseInt(file.size);
      summery.downloads += parseInt(file.downloaded_count);
    });

    return summery;
  }

  function checkIfLikedByUser(user,likes){
    let likedByUser = false;
    likes.forEach(function(like,index){
      if (user.member_id === like.member_id){
        likedByUser = true;
      }
    });
    return likedByUser;
  }

  function getLoggedUserRatingOnProduct(user,ratings){
    let userRating = -1;
    ratings.forEach(function(r,index){
      if (r.member_id === user.member_id){
        if (r.user_like === "1"){
          userRating = 1;
        } else {
          userRating = 0
        }
      }
    });
    return userRating;
  }

  function calculateProductLaplaceScore(ratings){
    let laplace_score = 0;
    let upvotes = 0;
    let downvotes = 0;
    ratings.forEach(function(rating,index){
      if (rating.rating_active === "1"){
        if (rating.user_like === "1"){
          upvotes += 1;
        } else if (rating.user_like === "0") {
          downvotes += 1;
        }
      }
    });
    laplace_score = (Math.round(((upvotes + 6) / ((upvotes + downvotes) + 12)),2) * 100);
    return laplace_score;
  }

  function generateOcsInstallLink(f,xdgType,downloadUrl){
    let ocsUrl,
        osId = '',
        link = '',
        licenseId = '',
        license = '',
        packagetypeId = '',
        architectureId = '',
        filesTags,
        fileDescription = '';

    if (f.description){
      fileDescription = f.description;
    }

    if (f.tags){
      fileTags = f.tags.split(',');
      fileTags.forEach(function(tag,index){
        let tagStr;
        if (tag.length > 0){
          if (tag.indexOf("##")==-1){
            tagStr = tag.split('-');
            if (tagStr.length == 2 && tagStr[0] == 'os') {
              osId = tagStr[1];
            } else if (tagStr.length == 2 && tagStr[0] == 'licensetype') {
              licenseId = tagStr[1];
            } else if (tagStr.length == 2 && tagStr[0] == 'packagetypeid') {
              packagetypeId = tagStr[1];
            } else if (tagStr.length == 2 && tagStr[0] == 'architectureid') {
              architectureId = tagStr[1];
            }
          }
          else {
            tagStr = tag.split('##');
             if (tagStr.length == 2 && tagStr[0] == 'link') {
                link = tagStr[1];
             } else if (tagStr.length == 2 && tagStr[0] == 'license') {
                license = tagStr[1];
                license = Base64.decode(license);
             } else if (tagStr.length == 2 && tagStr[0] == 'packagetypeid') {
                packagetypeId = tagStr[1];
             } else if (tagStr.length == 2 && tagStr[0] == 'architectureid') {
                architectureId = tagStr[1];
             }
         }
        }
      });
    }


    if (typeof link !== 'undefined' && link) {
        ocsUrl = generateOcsUrl(decodeURIComponent(link),xdgType);
    } else if (!link) {
        ocsUrl = generateOcsUrl(downloadUrl,xdgType,f.name);
    }

    function generateOcsUrl(url, type, filename) {
      if (!url || !type) { return ''; }
      if (!filename) { filename = url.split('/').pop().split('?').shift(); }
      return 'ocs://install' + '?url=' + encodeURIComponent(url) + '&type=' + encodeURIComponent(type) + '&filename=' + encodeURIComponent(filename);
    }

    return ocsUrl;
  }

  function getFileWithLongestTitle(files){
    let longestTitleFile;
    let maxTitleLength = 0;
    files.forEach(function(file,index){
      if (file.title.length > maxTitleLength){
        longestTitleFile = file;
        maxTitleLength = file.title.length;
      }
    });
    return longestTitleFile;
  }

  return {
    getNumberOfProducts,
    generatePaginationObject,
    calculateProductRatings,
    getActiveRatingsNumber,
    getFilesSummary,
    checkIfLikedByUser,
    getLoggedUserRatingOnProduct,
    calculateProductLaplaceScore,
    generateOcsInstallLink,
    getFileWithLongestTitle
  }
}());
