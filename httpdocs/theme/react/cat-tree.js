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
          console.log(selectedCategory);
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

  return {
    convertObjectToArray,
    getSelectedCategory,
    getCategoryType
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
  }

  componentDidMount() {
    if (this.state.categoryId) {
      this.getSelectedCategories(this.state.categories, this.state.categoryId);
    } else {
      this.setState({ loading: false });
    }
  }

  getSelectedCategories(categories, catId) {
    const selectedCategory = appHelpers.getSelectedCategory(this.state.categories, catId);
    const selectedCategories = this.state.selectedCategories;
    selectedCategories.push(selectedCategory);
    this.setState({ selectedCategories: selectedCategories }, function () {
      if (typeof selectedCategory.parent_id === 'string') {
        this.getSelectedCategories(categories, parseInt(selectedCategory.parent_id));
      } else {
        this.setState({ loading: false });
      }
    });
  }

  render() {
    let categoryTreeDisplay;
    if (!this.state.loading) {
      if (this.state.categories) {
        const categoryId = this.state.categoryId;
        const selectedCategories = this.state.selectedCategories;
        categoryTreeDisplay = this.state.categories.map((cat, index) => React.createElement(CategoryItem, {
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
      React.createElement(
        "ul",
        null,
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

      const categoryChildren = children.map((cat, index) => React.createElement(CategoryItem, {
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

    const categoryItemLink = app.generateCategoryLink(window.baseUrl, this.props.category.id, window.location.href);
    console.log(categoryItemLink);
    return React.createElement(
      "li",
      { id: "cat-" + this.props.category.id, className: categoryItemClass },
      React.createElement(
        "a",
        { href: categoryItemLink },
        this.props.category.title,
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

ReactDOM.render(React.createElement(CategoryTree, null), document.getElementById('category-tree-container'));
