class CategoryTree extends React.Component {
  constructor(props){
  	super(props);
  	this.state = {
      categories:window.catTree,
      categoryId:window.categoryId,
      selectedCategories:[]
    };
    this.getSelectedCategories = this.getSelectedCategories.bind(this);
  }

  componentDidMount() {
    console.log(this.state);
    if (this.state.categoryId){
      this.getSelectedCategories(this.state.categories,this.state.categoryId);
    }
  }

  getSelectedCategories(categories,catId){
    const selectedCategory = appHelpers.getSelectedCategory(this.state.categories,this.state.categoryId);
    const selectedCategories = this.state.selectedCategories;
    selectedCategories.push(selectedCategory);
    this.setState({selectedCategories:selectedCategories},function(){
      if (selectedCategory.parent_id){
        this.getSelectedCategories(categories,parseInt(selectedCategory.parent_id));
      } else {
        console.log(this.state);
      }
    });
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
    if (this.props.category.has_children === true){

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
