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
    }
    files.forEach(function(file,index){
      summery.total += 1;
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

  return {
    getNumberOfProducts,
    generatePaginationObject,
    calculateProductRatings,
    getActiveRatingsNumber,
    getFilesSummary,
    checkIfLikedByUser,
    getLoggedUserRatingOnProduct,
    calculateProductLaplaceScore
  }
}());
