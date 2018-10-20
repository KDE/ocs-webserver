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
      if (cat.id === categoryId){
        selectedCategory = cat;
      } else if (cat.has_children){
        console.log('Catgeory ' + cat.id + ' has children');
        const catChildren = appHelpers.convertObjectToArray(cat.children);
        selectedCategory = appHelpers.getSelectedCategory(catChildren,categoryId);
        /*catChildren.forEach(function(child,childIndex){
          if (child.id === categoryId){
            selectedCategory = cat;
          }
          if (child.has_children){

          }
        });*/
      }
    });
    return selectedCategory;
  }

  return {
    convertObjectToArray,
    getSelectedCategory
  }

}());
