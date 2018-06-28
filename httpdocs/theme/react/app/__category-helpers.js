window.categoryHelpers = (function(){

  function convertCatChildrenObjectToArray(children){
    console.log(children);
    let cArray = [];
    for (var i in children) {
      cArray.push(children[i]);
    }
    console.log(cArray);
    return cArray;
  }

  return {
    convertCatChildrenObjectToArray
  }
}());
