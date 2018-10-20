class CategoryTree extends React.Component {
  constructor(props) {
    super(props);
    this.state = {
      categories: window.catTree,
      categoryId: window.categoryId
    };
  }

  componentDidMount() {}

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
    if (this.props.category.has_children) {
      const categoryId = this.props.categoryId;
      const categoryChildren = this.props.category.children.map((category, index) => React.createElement(CategoryItem, {
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
