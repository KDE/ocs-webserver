window.categoryHelpers = (function(){

  function findParentCategory(categories,catId){
    let selectedCategories = {}
    categories.forEach(function(mc,index){
      if (parseInt(mc.id) === catId){
        selectedCategories.category = mc;
      } else {
        const cArray = categoryHelpers.convertCatChildrenObjectToArray(mc.children);
        cArray.forEach(function(sc,index){
          if (parseInt(sc.id) === catId){
            selectedCategories.category = mc;
            selectedCategories.subcategry = sc;
          } else {
            const scArray = categoryHelpers.convertCatChildrenObjectToArray(sc.children);
            scArray.forEach(function(ssc,index){
              if (parseInt(ssc.id) === catId){
                selectedCategories.category = mc;
                selectedCategories.subcategry = sc;
                selectedCategories.secondSubCategory = ssc;
              }
            })
          }
        });
      }
    });
    return selectedCategories;
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
