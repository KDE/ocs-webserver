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

  return {
    convertObjectToArray,
    getSelectedCategory,
    getCategoryType,
    generateCategoryLink,
    sortArrayAlphabeticallyByTitle,
    getDeviceFromWidth
  };
}();
class CategoryTree extends React.Component {
  constructor(props) {
    super(props);
    this.state = {
      categories: window.catTree,
      categoryId: window.categoryId,
      selectedCategories: [],
      loading: true
    };
    this.getSelectedCategories = this.getSelectedCategories.bind(this);
    this.updateDimensions = this.updateDimensions.bind(this);
  }

  componentWillMount() {
    this.updateDimensions();
  }

  componentWillUnmount() {
    window.removeEventListener("resize", this.updateDimensions);
  }

  componentDidMount() {
    window.addEventListener("resize", this.updateDimensions);
    if (this.state.categoryId !== 0) {
      this.getSelectedCategories(this.state.categories, this.state.categoryId);
    } else {
      this.setState({ loading: false });
    }
  }

  getSelectedCategories(categories, catId) {
    const selectedCategory = appHelpers.getSelectedCategory(this.state.categories, catId);
    const selectedCategories = this.state.selectedCategories;
    selectedCategory.selectedIndex = selectedCategories.length;
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

  render() {
    let categoryTreeDisplay, selectedCategoryDisplay;
    if (!this.state.loading) {

      if (this.state.selectedCategories) {
        selectedCategoryDisplay = React.createElement(SelectedCategory, {
          categoryId: this.state.categoryId,
          selectedCategories: this.state.selectedCategories
        });
      }

      if (this.state.categories) {
        const categoryId = this.state.categoryId;
        const selectedCategories = this.state.selectedCategories;
        categoryTreeDisplay = this.state.categories.sort(appHelpers.sortArrayAlphabeticallyByTitle).map((cat, index) => React.createElement(CategoryItem, {
          key: index,
          category: cat,
          categoryId: categoryId,
          selectedCategories: selectedCategories
        }));
      }
    }

    return React.createElement(
      "div",
      { id: "category-tree" },
      selectedCategoryDisplay,
      React.createElement(
        "ul",
        { className: "main-list" },
        React.createElement(
          "li",
          { className: "cat-item" },
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
        categoryTreeDisplay
      )
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
    this.getSelectedCategory = this.getSelectedCategory.bind(this);
  }

  componentDidMount() {
    this.getSelectedCategory();
  }

  getSelectedCategory() {
    console.log('get selectedCategory');
    console.log(this.props);
    let category;
    const categoryId = this.props.categoryId;
    this.props.selectedCategories.forEach(function (cat, index) {
      console.log(cat);
      console.log(cat.id);
      if (parseInt(cat.id) === categoryId) {
        category = cat;
      }
    });
    console.log(category);
    this.setState({ category: category }, function () {
      console.log('wtf');
    });
  }

  render() {
    let selectedCategoryDisplay;
    if (this.state.category) {
      selectedCategoryDisplay = React.createElement(
        "a",
        null,
        this.state.category.title
      );
    }
    return React.createElement(
      "div",
      { id: "slected-category-tree-item" },
      selectedCategoryDisplay
    );
  }
}

ReactDOM.render(React.createElement(CategoryTree, null), document.getElementById('category-tree-container'));
