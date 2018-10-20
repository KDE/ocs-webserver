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
      if (parseInt(cat.id) === categoryId){
        console.log(cat.id);
        console.log('its the same id');
        selectedCategory = cat;
        console.log(selectedCategory);
      } else if (cat.has_children === true){
        const catChildren = appHelpers.convertObjectToArray(cat.children);
        selectedCategory = appHelpers.getSelectedCategory(catChildren,categoryId);
      }
    });
    console.log(selectedCategory);
    return selectedCategory;
  }

  return {
    convertObjectToArray,
    getSelectedCategory
  }

}());
