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

  return {
    getNumberOfProducts,
    generatePaginationObject
  }
}());
