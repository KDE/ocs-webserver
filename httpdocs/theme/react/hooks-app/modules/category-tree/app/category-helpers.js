export function ConvertObjectToArray(object){
  let newArray = [];
  for (var i in object){
    newArray.push(object[i]);
  }
  return newArray;
}

export function GetSelectedCategory(categories,categoryId){
  let selectedCategory;
  if (categories){
    categories.forEach(function(cat,catIndex){
      if (!selectedCategory){
        if (parseInt(cat.id) === categoryId){
          selectedCategory = cat;
          selectedCategory.categories = ConvertObjectToArray(selectedCategory.children);
        } else {
          if (cat.has_children === true){
            const catChildren = ConvertObjectToArray(cat.children);
  
            selectedCategory = GetSelectedCategory(catChildren,categoryId);
          }
        }
      }
    });
  }
  return selectedCategory;
}

export function RenderCurrentViewedCategories(categories,selectedCategory){
  let newCurrentViewedCategories = [];
  if (selectedCategory){
    newCurrentViewedCategories = GenerateCurrentViewedCategories(categories,selectedCategory,[])
    if (selectedCategory.has_children === true) newCurrentViewedCategories.push(selectedCategory);
    if (newCurrentViewedCategories.length > 0){
        newCurrentViewedCategories.forEach(function(icvc,index){
            icvc.level = index + 1;
        })
    }
  }
  return newCurrentViewedCategories;
}

export function GenerateCurrentViewedCategories(categories,selectedCategory){
  if (selectedCategory.parent_id){
    const parentId = parseInt(selectedCategory.parent_id);
    let parentCategory = GetSelectedCategory(categories,parentId);
    if (parentCategory){
      parentCategory.categories = ConvertObjectToArray(parentCategory.children);
      parentCategory.categoryId = parentCategory.id;
      const selectedCategories = [parentCategory];
      return getCategoryParents(categories,selectedCategories);
    } else {
      return [];
    }
  } else {
    return [];
  }
}

function getCategoryParents(categories,selectedCategories){
  //if (selectedCategories[0].parent_id !== "34"){
    const parentId = parseInt(selectedCategories[0].parent_id);
    const parentCategory = GetSelectedCategory(categories,parentId);
    if (parentCategory){
      parentCategory.categories = ConvertObjectToArray(parentCategory.children);
      parentCategory.categoryId = parentCategory.id;
      selectedCategories = [parentCategory,...selectedCategories];
      return getCategoryParents(categories,selectedCategories);
    } else {
      return selectedCategories;
    }
  /*} else {
    return selectedCategories;
  }*/
}

export function GetCategoriesBySearchPhrase(categories,searchPhrase){
  let searchResults = [];
  categories.forEach(function(cat,index){
    if (cat.title.toLowerCase().indexOf(searchPhrase.toLowerCase()) > -1) searchResults.push(cat);
    if (cat.has_children){
      cat.categories = ConvertObjectToArray(cat.children);
      cat.categories.forEach(function(cat,index){
        if (cat.title.toLowerCase().indexOf(searchPhrase.toLowerCase()) > -1) searchResults.push(cat);
        if (cat.has_children){
          cat.categories = ConvertObjectToArray(cat.children);
          cat.categories.forEach(function(cat,index){
            if (cat.title.toLowerCase().indexOf(searchPhrase.toLowerCase()) > -1) searchResults.push(cat);
            if (cat.has_children){
              cat.categories = ConvertObjectToArray(cat.children);
              cat.categories.forEach(function(cat,index){
                if (cat.title.toLowerCase().indexOf(searchPhrase.toLowerCase()) > -1) searchResults.push(cat);
                if (cat.has_children){
                  cat.categories = ConvertObjectToArray(cat.children);
                  cat.categories.forEach(function(cat,index){
                    if (cat.title.toLowerCase().indexOf(searchPhrase.toLowerCase()) > -1) searchResults.push(cat);                   
                  });
                }                
              });
            }            
          });
        }        
      });
    }
  })  
  return searchResults;
}

export function getCategoryType(selectedCategories,selectedCategoryId,categoryId){
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

export function generateCategoryLink(baseUrl,urlContext,catId,locationHref){
  if (window.baseUrl !== window.location.origin){
    baseUrl = window.location.origin;
  }
  let link = baseUrl + urlContext + "/browse/";
  if (catId !== "all"){
    link += "cat/" + catId + "/";
  }
  if (locationHref.indexOf('ord') > -1){
    link += "ord/" + locationHref.split('/ord/')[1];
  }
  return link;
}

export function sortArrayAlphabeticallyByTitle(a, b){
  let titleA, titleB;
  if (a.title){
    titleA = a.title.trim().toLowerCase();
    titleB = b.title.trim().toLowerCase();
  } else {
    titleA = a.name.trim().toLowerCase();
    titleB = b.name.trim().toLowerCase();
  }
  if(titleA < titleB) { return -1; }
  if(titleA > titleB) { return 1; }
  return 0;
}

export function getDeviceFromWidth(width){
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

export function getUrlContext(href){
  let urlContext = "";
  if (href.indexOf('/s/') > -1){
    urlContext = "/s/" + href.split('/s/')[1].split('/')[0];
  }
  return urlContext;
}

export function getAllCatItemCssClass(href,baseUrl,urlContext,categoryId){
  if (baseUrl !== window.location.origin){
    baseUrl = window.location.origin;
  }
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

export const GetInitialCategoryTreeWidth = (windowWidth) => {
  
  let catTreeWidth;
  
  if ( windowWidth < 768 ) catTreeWidth = (( windowWidth / 100) * 100); 
  else catTreeWidth = 240;
  
  catTreeWidth -= 30;

  return catTreeWidth;
  
}