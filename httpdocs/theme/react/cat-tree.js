window.appHelpers = function () {

  function convertObjectToArray(object) {
    newArray = [];
    for (i in object) {
      newArray.push(object[i]);
    }
    return newArray;
  }

  function getSelectedCategory(categories, categoryId) {
    let selectedCategory;
    categories.forEach(function (cat, catIndex) {
      if (!selectedCategory) {
        if (parseInt(cat.id) === categoryId) {
          selectedCategory = cat;
        } else {
          if (cat.has_children === true) {
            const catChildren = appHelpers.convertObjectToArray(cat.children);
            selectedCategory = appHelpers.getSelectedCategory(catChildren, categoryId);
          }
        }
      }
    });
    return selectedCategory;
  }

  function getCategoryType(selectedCategories, selectedCategoryId, categoryId) {
    let categoryType;
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

  function generateCategoryLink(baseUrl, urlContext, catId, locationHref) {
    if (window.baseUrl !== window.location.origin) {
      baseUrl = window.location.origin;
    }
    let link = baseUrl + urlContext + "/browse/";
    if (catId !== "all") {
      link += "cat/" + catId + "/";
    }
    if (locationHref.indexOf('ord') > -1) {
      link += "ord/" + locationHref.split('/ord/')[1];
    }
    return link;
  }

  function sortArrayAlphabeticallyByTitle(a, b) {
    const titleA = a.title.toLowerCase();
    const titleB = b.title.toLowerCase();
    if (titleA < titleB) {
      return -1;
    }
    if (titleA > titleB) {
      return 1;
    }
    return 0;
  }

  function getDeviceFromWidth(width) {
    let device;
    if (width >= 910) {
      device = "large";
    } else if (width < 910 && width >= 610) {
      device = "mid";
    } else if (width < 610) {
      device = "tablet";
    }
    return device;
  }

  function getUrlContext(href) {
    let urlContext = "";
    if (href.indexOf('/s/') > -1) {
      urlContext = "/s/" + href.split('/s/')[1].split('/')[0];
    }
    return urlContext;
  }

  function getAllCatItemCssClass(href, baseUrl, urlContext, categoryId) {
    if (baseUrl !== window.location.origin) {
      baseUrl = window.location.origin;
    }
    let allCatItemCssClass;
    if (categoryId && categoryId !== 0) {
      allCatItemCssClass = "";
    } else {
      if (href === baseUrl + urlContext || href === baseUrl + urlContext + "/browse/" || href === baseUrl + urlContext + "/browse/ord/latest/" || href === baseUrl + urlContext + "/browse/ord/top/" || href === "https://store.kde.org" || href === "https://store.kde.org/browse/ord/latest/" || href === "https://store.kde.org/browse/ord/top/" || href === "https://addons.videolan.org" || href === "https://addons.videolan.org/browse/ord/latest/" || href === "https://addons.videolan.org/browse/ord/top/" || href === "https://share.krita.org/" || href === "https://share.krita.org/browse/ord/latest/" || href === "https://share.krita.org/browse/ord/top/") {
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
  };
}();
class CategoryTree extends React.Component {
  constructor(props) {
    super(props);
    this.state = {
      categories: window.catTree,
      categoryId: window.categoryId,
      catTreeCssClass: "",
      selectedCategories: [],
      showCatTree: false,
      backendView: window.backendView,
      loading: true
    };
    this.getSelectedCategories = this.getSelectedCategories.bind(this);
    this.updateDimensions = this.updateDimensions.bind(this);
    this.toggleCatTree = this.toggleCatTree.bind(this);
  }

  componentWillMount() {
    this.updateDimensions();
  }

  componentWillUnmount() {
    window.removeEventListener("resize", this.updateDimensions);
  }

  componentDidMount() {
    window.addEventListener("resize", this.updateDimensions);
    const urlContext = appHelpers.getUrlContext(window.location.href);
    this.setState({ urlContext: urlContext }, function () {
      if (this.state.categoryId && this.state.categoryId !== 0) {
        this.getSelectedCategories(this.state.categories, this.state.categoryId);
      } else {
        this.setState({ loading: false });
      }
    });
  }

  getSelectedCategories(categories, catId) {
    const selectedCategory = appHelpers.getSelectedCategory(this.state.categories, catId);
    const selectedCategories = this.state.selectedCategories;
    if (typeof selectedCategory !== 'undefined') {
      selectedCategory.selectedIndex = selectedCategories.length;
      selectedCategories.push(selectedCategory);
    }
    this.setState({ selectedCategories: selectedCategories }, function () {
      if (selectedCategory && selectedCategory.parent_id) {
        this.getSelectedCategories(categories, parseInt(selectedCategory.parent_id));
      } else {
        this.setState({ loading: false });
      }
    });
  }

  updateDimensions() {
    const device = appHelpers.getDeviceFromWidth(window.innerWidth);
    this.setState({ device: device });
  }

  toggleCatTree() {
    const showCatTree = this.state.showCatTree === true ? false : true;
    const catTreeCssClass = this.state.catTreeCssClass === "open" ? "" : "open";
    this.setState({ showCatTree: showCatTree, catTreeCssClass: catTreeCssClass });
  }

  render() {
    let categoryTreeDisplay, selectedCategoryDisplay;
    if (!this.state.loading) {

      if (this.state.device === "tablet" && this.state.selectedCategories && this.state.selectedCategories.length > 0) {
        selectedCategoryDisplay = React.createElement(SelectedCategory, {
          categoryId: this.state.categoryId,
          selectedCategory: this.state.selectedCategories[0],
          selectedCategories: this.state.selectedCategories,
          onCatTreeToggle: this.toggleCatTree
        });
      }
      if (this.state.device === "tablet" && this.state.showCatTree || this.state.device !== "tablet" || this.state.selectedCategories && this.state.selectedCategories.length === 0) {
        if (this.state.categories) {
          const self = this;
          const categoryTree = this.state.categories.sort(appHelpers.sortArrayAlphabeticallyByTitle).map((cat, index) => React.createElement(CategoryItem, {
            key: index,
            category: cat,
            categoryId: self.state.categoryId,
            urlContext: self.state.urlContext,
            selectedCategories: self.state.selectedCategories,
            backendView: self.state.backendView
          }));

          const allCatItemCssClass = appHelpers.getAllCatItemCssClass(window.location.href, window.baseUrl, this.state.urlContext, this.state.categoryId);
          let baseUrl;
          if (window.baseUrl !== window.location.origin) {
            baseUrl = window.location.origin;
          }

          const categoryItemLink = appHelpers.generateCategoryLink(window.baseUrl, this.state.urlContext, "all", window.location.href);

          categoryTreeDisplay = React.createElement(
            "ul",
            { className: "main-list" },
            React.createElement(
              "li",
              { className: "cat-item" + " " + allCatItemCssClass },
              React.createElement(
                "a",
                { href: categoryItemLink },
                React.createElement(
                  "span",
                  { className: "title" },
                  "All"
                )
              )
            ),
            categoryTree
          );
        }
      }
    }

    return React.createElement(
      "div",
      { id: "category-tree", className: this.state.device + " " + this.state.catTreeCssClass },
      selectedCategoryDisplay,
      categoryTreeDisplay
    );
  }
}

class CategoryItem extends React.Component {
  constructor(props) {
    super(props);
    this.state = {};
  }

  render() {
    let categoryChildrenDisplay;

    const categoryType = appHelpers.getCategoryType(this.props.selectedCategories, this.props.categoryId, this.props.category.id);
    if (this.props.category.has_children === true && categoryType && this.props.lastChild !== true || this.props.category.has_children === true && this.props.backendView === true) {

      const self = this;

      let lastChild;
      if (categoryType === "selected") {
        lastChild = true;
      }

      const children = appHelpers.convertObjectToArray(this.props.category.children);
      const categoryChildren = children.sort(appHelpers.sortArrayAlphabeticallyByTitle).map((cat, index) => React.createElement(CategoryItem, {
        key: index,
        category: cat,
        categoryId: self.props.categoryId,
        urlContext: self.props.urlContext,
        selectedCategories: self.props.selectedCategories,
        lastChild: lastChild,
        parent: self.props.category,
        backendView: self.props.backendView
      }));

      categoryChildrenDisplay = React.createElement(
        "ul",
        null,
        categoryChildren
      );
    }

    let categoryItemClass = "cat-item";
    if (this.props.categoryId === parseInt(this.props.category.id)) {
      categoryItemClass += " active";
    }

    let productCountDisplay;
    if (this.props.category.product_count !== "0") {
      productCountDisplay = this.props.category.product_count;
    }

    const categoryItemLink = appHelpers.generateCategoryLink(window.baseUrl, this.props.urlContext, this.props.category.id, window.location.href);

    let submenuToggleDisplay;
    if (this.props.backendView === true && this.props.has_children === true) {
      if (this.state.showSubmenu === true) {
        submenuToggleDisplay = React.createElement(
          "span",
          { onclick: this.toggleSubmenu },
          "[-]"
        );
      } else {
        submenuToggleDisplay = React.createElement(
          "span",
          { onclick: this.toggleSubmenu },
          "[+]"
        );
      }
    }

    return React.createElement(
      "li",
      { id: "cat-" + this.props.category.id, className: categoryItemClass },
      React.createElement(
        "a",
        { href: categoryItemLink },
        React.createElement(
          "span",
          { className: "title" },
          this.props.category.title
        ),
        React.createElement(
          "span",
          { className: "product-counter" },
          productCountDisplay
        ),
        submenuToggleDisplay
      ),
      categoryChildrenDisplay
    );
  }
}

class SelectedCategory extends React.Component {
  constructor(props) {
    super(props);
    this.state = {};
  }

  render() {
    let selectedCategoryDisplay;
    if (this.props.selectedCategory) {
      selectedCategoryDisplay = React.createElement(
        "a",
        { onClick: this.props.onCatTreeToggle },
        this.props.selectedCategory.title
      );
    }

    let selectedCategoriesDisplay;
    if (this.props.selectedCategories) {
      const selectedCategoriesReverse = this.props.selectedCategories.slice(0);
      selectedCategoriesDisplay = selectedCategoriesReverse.reverse().map((sc, index) => React.createElement(
        "a",
        { key: index },
        sc.title
      ));
    }

    return React.createElement(
      "div",
      { onClick: this.props.onCatTreeToggle, id: "selected-category-tree-item" },
      selectedCategoriesDisplay,
      React.createElement("span", { className: "selected-category-arrow-down" })
    );
  }
}

ReactDOM.render(React.createElement(CategoryTree, null), document.getElementById('category-tree-container'));
