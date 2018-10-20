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

      const categories = this.state.categories;
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

  componentDidMount() {
    console.log(this.props);
  }

  render() {
    return React.createElement(
      "li",
      { id: "cat-" - this.props.category.cat_id },
      this.props.category.cat_id
    );
  }
}

ReactDOM.render(React.createElement(CategoryTree, null), document.getElementById('category-tree-container'));
