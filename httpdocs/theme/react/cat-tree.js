class CategoryTree extends React.Component {
  constructor(props) {
    super(props);
    this.state = {};
  }

  componentDidMount() {
    console.log(window.catTree);
    console.log(window.catSelected);
  }

  render() {
    return React.createElement("div", { id: "category-tree" });
  }
}

ReactDOM.render(React.createElement(CategoryTree, null), document.getElementById('category-tree-container'));
