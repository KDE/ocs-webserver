window.productHelpers = (function(){

  function getNumberOfProducts(device,numRows){
    let num;
    if (device === "huge"){
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

  function generatePaginationObject(numPages,pathname,currentCategoy,order){
    let pagination = [];

    let baseHref = "/browse";
    if (pathname.indexOf('cat') > -1){
      baseHref += "/cat/" + currentCategoy;
    }

    for (var i = 0; i < numPages; i++){
      const page = {
        number:parseInt(i + 1),
        link:baseHref + "/page/" + parseInt(i + 1) + "/ord/" + order
      }
      pagination.push(page);
    }
    return pagination;
  }

  return {
    getNumberOfProducts,
    generatePaginationObject
  }
}());
