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
      if (!selectedCategory){
        if (parseInt(cat.id) === categoryId){
          console.log('its the same id - ' + parseInt(cat.id) + ' ' + categoryId );
          selectedCategory = cat;
        } else {
          console.log('its not the same id - ' + parseInt(cat.id) + ' ' + categoryId );
          if (cat.has_children === true){
            const catChildren = appHelpers.convertObjectToArray(cat.children);
            selectedCategory = appHelpers.getSelectedCategory(catChildren,categoryId);
          }
        }        
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
