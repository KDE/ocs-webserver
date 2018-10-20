class CategoryTree extends React.Component {
  constructor(props){
  	super(props);
  	this.state = {
      categories:window.catTree,
      categoryId:window.categoryId
    };
  }

  componentDidMount() {
    const selectedCategory = appHelpers.getSelectedCategory(this.state.cateogries,this.state.categoryId);
  }

  render(){
    let categoryTreeDisplay;
    if (this.state.categories){
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

  }

  render(){
    let categoryChildrenDisplay;
    if (this.props.category.has_children){

      const categoryId = this.props.categoryId;
      const category = this.props.category;
      const children = appHelpers.convertObjectToArray(this.props.category.children);

      const categoryChildren = children.map((cat,index) => (
        <CategoryItem
          key={index}
          category={cat}
          categoryId={categoryId}
          parent={category}
        />
      ));

      categoryChildrenDisplay = (
        <ul>
          {categoryChildren}
        </ul>
      );

    }

    let categoryItemClass = "cat-item";
    if (this.props.categoryId === this.props.category.id){
      categoryItemClass += " active";
    }

    return(
      <li id={"cat-"-this.props.category.id} className={categoryItemClass}>
        <a href={window.baseUrl + "/browse/cat/" + this.props.category.id}>
          {this.props.category.title}
          <span className="product-counter">{this.props.product_count}</span>
        </a>
        {categoryChildrenDisplay}
      </li>
    )
  }
}

ReactDOM.render(
    <CategoryTree />,
    document.getElementById('category-tree-container')
);
