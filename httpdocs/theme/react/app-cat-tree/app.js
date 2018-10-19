class CategoryTree extends React.Component {
  constructor(props){
  	super(props);
  	this.state = {};
  }

  componentDidMount() {
    console.log(window.catTree);
    console.log(window.catSelected);
  }

  render(){
    return(
      <div id="category-tree"></div>
    );
  }
}

ReactDOM.render(
    <CategoryTree />,
    document.getElementById('category-tree-container')
);
