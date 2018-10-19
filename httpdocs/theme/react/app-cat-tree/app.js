class CategoryTree extends React.Component {
  constructor(props){
  	super(props);
  	this.state = {};
  }
  render(){
    return(
      <div id="category-tree"></div>
    );
  }
}

ReactDOM.render(
    <MetaHeader />,
    document.getElementById('category-tree-container')
);
