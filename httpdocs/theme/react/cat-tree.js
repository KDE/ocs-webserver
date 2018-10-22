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

  function generateCategoryLink(baseUrl, catId, locationHref) {
    let link = baseUrl + "/browse/cat/" + catId;
    if (locationHref.indexOf('ord') > -1) {
      link += "/ord/" + locationHref.split('/ord/')[1];
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
    console.log(href);
  }

  return {
    convertObjectToArray,
    getSelectedCategory,
    getCategoryType,
    generateCategoryLink,
    sortArrayAlphabeticallyByTitle,
    getDeviceFromWidth,
    getUrlContext
  };
}();
class CategoryTree extends React.Component {
  constructor(props) {
    super(props);
    this.state = {
      categories: window.catTree,
      categoryId: window.categoryId,
      selectedCategories: [],
      loading: true,
      showCatTree: false,
      catTreeCssClass: ""
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
    if (this.state.categoryId && this.state.categoryId !== 0) {
      this.getSelectedCategories(this.state.categories, this.state.categoryId);
    } else {
      this.setState({ loading: false });
    }
  }

  getSelectedCategories(categories, catId) {
    const selectedCategory = appHelpers.getSelectedCategory(this.state.categories, catId);
    const selectedCategories = this.state.selectedCategories;
    if (selectedCategory) {
      selectedCategory.selectedIndex = selectedCategories.length;
    }
    selectedCategories.push(selectedCategory);
    this.setState({ selectedCategories: selectedCategories }, function () {
      if (typeof selectedCategory.parent_id === 'string') {
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
          const categoryId = this.state.categoryId;
          const selectedCategories = this.state.selectedCategories;
          const categoryTree = this.state.categories.sort(appHelpers.sortArrayAlphabeticallyByTitle).map((cat, index) => React.createElement(CategoryItem, {
            key: index,
            category: cat,
            categoryId: categoryId,
            selectedCategories: selectedCategories
          }));

          let allCatItemCssClass;
          if (this.state.categoryId && this.state.categoryId !== 0) {
            allCatItemCssClass = "";
          } else {
            if (window.location.href === window.baseUrl + "/browse/") {
              allCatItemCssClass = "active";
            }
          }

          categoryTreeDisplay = React.createElement(
            "ul",
            { className: "main-list" },
            React.createElement(
              "li",
              { className: "cat-item" + " " + allCatItemCssClass },
              React.createElement(
                "a",
                { href: window.baseUrl + "/browse/" },
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
    if (this.props.category.has_children === true && categoryType && this.props.lastChild !== true) {

      const categoryId = this.props.categoryId;
      const category = this.props.category;
      const selectedCategories = this.props.selectedCategories;
      const children = appHelpers.convertObjectToArray(this.props.category.children);
      let lastChild;
      if (categoryType === "selected") {
        lastChild = true;
      }

      const categoryChildren = children.sort(appHelpers.sortArrayAlphabeticallyByTitle).map((cat, index) => React.createElement(CategoryItem, {
        key: index,
        category: cat,
        categoryId: categoryId,
        selectedCategories: selectedCategories,
        lastChild: lastChild,
        parent: category
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

    const categoryItemLink = appHelpers.generateCategoryLink(window.baseUrl, this.props.category.id, window.location.href);

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
        )
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
