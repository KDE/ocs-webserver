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

  function generateCategoryLink(baseUrl,catId,locationHref){
    let link = baseUrl + "/browse/cat/" + catId;
    if (locationHref.indexOf('ord') > -1){
      link += "/ord/" + locationHref.split('/ord/')[1];
    }
    return link;
  }

  function sortArrayAlphabeticallyByTitle(a, b){
    if(a.title < b.title) { return -1; }
    if(a.title > b.title) { return 1; }
    return 0;
  }

  return {
    convertObjectToArray,
    getSelectedCategory,
    getCategoryType,
    generateCategoryLink,
    sortArrayAlphabeticallyByTitle
  }

}());
