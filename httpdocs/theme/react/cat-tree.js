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
      console.log(parseInt(cat.id));
      console.log(categoryId);
      if (parseInt(cat.id) === categoryId) {
        selectedCategory = cat;
      } else if (cat.has_children === true) {
        const catChildren = appHelpers.convertObjectToArray(cat.children);
        selectedCategory = appHelpers.getSelectedCategory(catChildren, categoryId);
      }
    });
    return selectedCategory;
  }

  return {
    convertObjectToArray,
    getSelectedCategory
  };
}();
class CategoryTree extends React.Component {
  constructor(props) {
    super(props);
    this.state = {
      categories: window.catTree,
      categoryId: window.categoryId,
      selectedCategories: []
    };
    this.getSelectedCategories = this.getSelectedCategories.bind(this);
  }

  componentDidMount() {
    console.log(this.state);
    if (this.state.categoryId) {
      this.getSelectedCategories(this.state.categories, this.state.categoryId);
    }
  }

  getSelectedCategories(categories, catId) {
    const selectedCategory = appHelpers.getSelectedCategory(this.state.categories, this.state.categoryId);
    const selectedCategories = this.state.selectedCategories;
    selectedCategories.push(selectedCategory);
    this.setState({ selectedCategories: selectedCategories }, function () {
      if (selectedCategory.parent_id) {
        this.getSelectedCategories(categories, parseInt(selectedCategory.parent_id));
      } else {
        console.log(this.state);
      }
    });
  }

  render() {
    let categoryTreeDisplay;
    if (this.state.categories) {
      const categoryId = this.state.categoryId;
      categoryTreeDisplay = this.state.categories.map((cat, index) => React.createElement(CategoryItem, {
        key: index,
        category: cat,
        categoryId: categoryId
      }));
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

  componentDidMount() {}

  render() {
    let categoryChildrenDisplay;
    if (this.props.category.has_children === true) {

      const categoryId = this.props.categoryId;
      const category = this.props.category;
      const children = appHelpers.convertObjectToArray(this.props.category.children);

      const categoryChildren = children.map((cat, index) => React.createElement(CategoryItem, {
        key: index,
        category: cat,
        categoryId: categoryId,
        parent: category
      }));

      categoryChildrenDisplay = React.createElement(
        "ul",
        null,
        categoryChildren
      );
    }

    let categoryItemClass = "cat-item";
    if (this.props.categoryId === this.props.category.id) {
      categoryItemClass += " active";
    }

    return React.createElement(
      "li",
      { id: "cat-" - this.props.category.id, className: categoryItemClass },
      React.createElement(
        "a",
        { href: window.baseUrl + "/browse/cat/" + this.props.category.id },
        this.props.category.title,
        React.createElement(
          "span",
          { className: "product-counter" },
          this.props.product_count
        )
      ),
      categoryChildrenDisplay
    );
  }
}

ReactDOM.render(React.createElement(CategoryTree, null), document.getElementById('category-tree-container'));
