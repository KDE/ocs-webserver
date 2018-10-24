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

  function generateCategoryLink(baseUrl,urlContext,catId,locationHref){
    let link = baseUrl + urlContext + "/browse/cat/" + catId + "/";
    if (locationHref.indexOf('ord') > -1){
      link += "ord/" + locationHref.split('/ord/')[1];
    }
    return link;
  }

  function sortArrayAlphabeticallyByTitle(a, b){
    const titleA = a.title.toLowerCase();
    const titleB = b.title.toLowerCase();
    if(titleA < titleB) { return -1; }
    if(titleA > titleB) { return 1; }
    return 0;
  }

  function getDeviceFromWidth(width){
    let device;
    if (width >= 910){
      device = "large";
    } else if (width < 910 && width >= 610){
      device = "mid";
    } else if (width < 610){
      device = "tablet";
    }
    return device;
  }

  function getUrlContext(href){
    let urlContext = "";
    if (href.indexOf('/s/') > -1){
      urlContext = "/s/" + href.split('/s/')[1].split('/')[0];
    }
    return urlContext;
  }

  function getAllCatItemCssClass(href,baseUrl,urlContext,categoryId){
    let allCatItemCssClass;
    if (categoryId && categoryId !== 0){
      allCatItemCssClass = "";
    } else {
      if (href === baseUrl + urlContext ||
          href === baseUrl + urlContext + "/browse/" || href === baseUrl + urlContext + "/browse/ord/latest/" || href === baseUrl + urlContext + "/browse/ord/top/" ||
          href === "https://store.kde.org" || href === "https://store.kde.org/browse/ord/latest/" ||  href === "https://store.kde.org/browse/ord/top/" ||
          href === "https://addons.videolan.org" || href === "https://addons.videolan.org/browse/ord/latest/" ||  href === "https://addons.videolan.org/browse/ord/top/" ||
          href === "https://share.krita.org/" || href === "https://share.krita.org/browse/ord/latest/" ||  href === "https://share.krita.org/browse/ord/top/"){
            allCatItemCssClass = "active";
      }
    }
    return allCatItemCssClass;
  }

  return {
    convertObjectToArray,
    getSelectedCategory,
    getCategoryType,
    generateCategoryLink,
    sortArrayAlphabeticallyByTitle,
    getDeviceFromWidth,
    getUrlContext,
    getAllCatItemCssClass
  }

}());
