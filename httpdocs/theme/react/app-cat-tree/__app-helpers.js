window.appHelpers = (function(){

  function convertObjectToArray(object){
    newArray = [];
    for (i in object){
      newArray.push(object[i]);
    }
    return newArray;
  }

  function getSelectedCategory(categories,categoryId){
    let selectedCategory;
    categories.forEach(function(cat){
      if (cat.id === categoryId){
        selectedCategory = cat;
      }
      if (cat.has_children){
        console.log('Catgeory has children');
      }
    });
    return selectedCategory;
  }

  return {
    convertObjectToArray,
    getSelectedCategory
  }

}());
