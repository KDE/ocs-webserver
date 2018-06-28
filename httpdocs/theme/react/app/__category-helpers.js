window.categoryHelpers = (function(){

  function findParentCategory(categories){
    console.log(categories);
  }

  function convertCatChildrenObjectToArray(children){
    let cArray = [];
    for (var i in children) {
      cArray.push(children[i]);
    }
    return cArray;
  }

  return {
    findParentCategory,
    convertCatChildrenObjectToArray
  }
}());
