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
          selectedCategory = cat;
          console.log(selectedCategory);
        } else {
          if (cat.has_children === true){
            const catChildren = appHelpers.convertObjectToArray(cat.children);
            selectedCategory = appHelpers.getSelectedCategory(catChildren,categoryId);
          }
        }
      }
    });
    return selectedCategory;
  }

  function getCategoryType(selectedCategories,selectedCategoryId,categoryId){
    let categoryType;
    console.log(selectedCategories)
    console.log(selectedCategoryId)
    console.log(categoryId);
    if (parseInt(categoryId) === selectedCategoryId){
      categoryType = "selected";
    } else {
      selectedCategories.forEach(function(selectedCat,index){
        if (selectedCat.id === categoryId){
          categoryType = "parent";
        }
      });
    }
    return categoryType;
  }

  return {
    convertObjectToArray,
    getSelectedCategory,
    getCategoryType
  }

}());
