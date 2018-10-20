class CategoryTree extends React.Component {
  constructor(props){
  	super(props);
  	this.state = {
      categories:window.catTree,
      categoryId:window.categoryId
    };
  }

  componentDidMount() {

  }

  render(){
    let categoryTreeDisplay;
    if (this.state.categories){

      const categories = this.state.categories;
      const categoryId = this.state.categoryId;

      categoryTreeDisplay = this.state.categories.map((cat,index) => (
        <CategoryItem
          key={index}
          category={cat}
          categoryId={categoryId}
        />
      ));
    }

    return(
      <div id="category-tree">
        <ul>
          {categoryTreeDisplay}
        </ul>
      </div>
    );
  }
}

class CategoryItem extends React.Component {
  constructor(props){
  	super(props);
  	this.state = {};
  }

  componentDidMount() {
    console.log(this.props);
  }

  render(){
    return(
      <li id={"cat-"-this.props.category.cat_id}>
        {this.props.category.cat_id}
      </li>
    )
  }
}

ReactDOM.render(
    <CategoryTree />,
    document.getElementById('category-tree-container')
);
