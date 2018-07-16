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
      if (r.user_like === "1"){
        totalUp += 1;
      } else if (r.user_dislike === "1"){
        totalDown += 1;
      }
    });
    pRating = 100 / ratings.length * (totalUp - totalDown);
    return pRating;
  }

  return {
    getNumberOfProducts,
    generatePaginationObject,
    calculateProductRatings
  }
}());
