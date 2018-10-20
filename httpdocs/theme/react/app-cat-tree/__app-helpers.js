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
    categories.forEach(function(cat,catIndex){
      console.log(parseInt(cat.id));
      console.log(categoryId);
      if (parseInt(cat.id) === categoryId){
        selectedCategory = cat;
      } else if (cat.has_children === true){
        const catChildren = appHelpers.convertObjectToArray(cat.children);
        selectedCategory = appHelpers.getSelectedCategory(catChildren,categoryId);
      }
    });
    return selectedCategory;
  }

  return {
    convertObjectToArray,
    getSelectedCategory
  }

}());
