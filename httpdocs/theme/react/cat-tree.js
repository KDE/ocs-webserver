class CategoryTree extends React.Component {
  constructor(props) {
    super(props);
    this.state = {};
  }
  render() {
    return React.createElement("div", { id: "category-tree" });
  }
}

ReactDOM.render(React.createElement(MetaHeader, null), document.getElementById('category-tree-container'));
