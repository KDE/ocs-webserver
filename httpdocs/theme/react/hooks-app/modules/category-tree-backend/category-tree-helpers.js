
    const ConvertObjectToArray = (object) => {
      var newArray = [];
      for (var i in object) {
        newArray.push(object[i]);
      }
      return newArray;
    }
  
    const GetSelectedCategory = (categories, categoryId) => {
      var selectedCategory = void 0;
      categories.forEach(function (cat, catIndex) {
        if (!selectedCategory) {
          if (parseInt(cat.id) === categoryId) {
            selectedCategory = cat;
          } else {
            if (cat.has_children === true) {
              var catChildren = appHelpers.convertObjectToArray(cat.children);
              selectedCategory = appHelpers.getSelectedCategory(catChildren, categoryId);
            }
          }
        }
      });
      return selectedCategory;
    }
  
    const GetCategoryType = (selectedCategories, selectedCategoryId, categoryId) => {
      var categoryType = void 0;
      if (parseInt(categoryId) === selectedCategoryId) {
        categoryType = "selected";
      } else {
        selectedCategories.forEach(function (selectedCat, index) {
          if (selectedCat.id === categoryId) {
            categoryType = "parent";
          }
        });
      }
      return categoryType;
    }
  
    const GenerateCategoryLink = (baseUrl, urlContext, catId, locationHref) => {
      if (window.baseUrl !== window.location.origin) {
        baseUrl = window.location.origin;
      }
      var link = baseUrl + urlContext + "/browse/";
      if (catId !== "all") {
        link += "cat/" + catId + "/";
      }
      if (locationHref.indexOf('ord') > -1) {
        link += "ord/" + locationHref.split('/ord/')[1];
      }
      return link;
    }
  
    const SortArrayAlphabeticallyByTitle = (a, b) => {
      var titleA = a.title.toLowerCase();
      var titleB = b.title.toLowerCase();
      if (titleA < titleB) {
        return -1;
      }
      if (titleA > titleB) {
        return 1;
      }
      return 0;
    }
  
    const GetDeviceFromWidth = (width) => {
      var device = void 0;
      if (width >= 910) {
        device = "large";
      } else if (width < 910 && width >= 610) {
        device = "mid";
      } else if (width < 610) {
        device = "tablet";
      }
      return device;
    }
  
    const GetUrlContext = (href) => {
      var urlContext = "";
      if (href.indexOf('/s/') > -1) {
        urlContext = "/s/" + href.split('/s/')[1].split('/')[0];
      }
      return urlContext;
    }
  
    const GetAllCatItemCssClass = (href, baseUrl, urlContext, categoryId) => {
      if (baseUrl !== window.location.origin) {
        baseUrl = window.location.origin;
      }
      var allCatItemCssClass = void 0;
      if (categoryId && categoryId !== 0) {
        allCatItemCssClass = "";
      } else {
        if (href === baseUrl + urlContext || href === baseUrl + urlContext + "/browse/" || href === baseUrl + urlContext + "/browse/ord/latest/" || href === baseUrl + urlContext + "/browse/ord/top/" || href === "https://store.kde.org" || href === "https://store.kde.org/browse/ord/latest/" || href === "https://store.kde.org/browse/ord/top/" || href === "https://addons.videolan.org" || href === "https://addons.videolan.org/browse/ord/latest/" || href === "https://addons.videolan.org/browse/ord/top/" || href === "https://share.krita.org/" || href === "https://share.krita.org/browse/ord/latest/" || href === "https://share.krita.org/browse/ord/top/") {
          allCatItemCssClass = "active";
        }
      }
      return allCatItemCssClass;
    }