window.categoryHelpers = (function(){

  function findCurrentCategories(categories,catId){
    let currentCategories = {}
    categories.forEach(function(mc,index){
      if (parseInt(mc.id) === catId){
        currentCategories.category = mc;
      } else {
        const cArray = categoryHelpers.convertCatChildrenObjectToArray(mc.children);
        cArray.forEach(function(sc,index){
          if (parseInt(sc.id) === catId){
            currentCategories.category = mc;
            currentCategories.subcategory = sc;
          } else {
            const scArray = categoryHelpers.convertCatChildrenObjectToArray(sc.children);
            scArray.forEach(function(ssc,index){
              if (parseInt(ssc.id) === catId){
                currentCategories.category = mc;
                currentCategories.subcategory = sc;
                currentCategories.secondSubCategory = ssc;
              }
            })
          }
        });
      }
    });
    return currentCategories;
  }

  function convertCatChildrenObjectToArray(children){
    let cArray = [];
    for (var i in children) {
      cArray.push(children[i]);
    }
    return cArray;
  }

  return {
    findCurrentCategories,
    convertCatChildrenObjectToArray
  }
}());
