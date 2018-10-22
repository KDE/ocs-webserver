class CategoryTree extends React.Component {
  constructor(props){
  	super(props);
  	this.state = {
      categories:window.catTree,
      categoryId:window.categoryId,
      selectedCategories:[],
      loading:true,
      showCatTree:false,
      catTreeCssClass:""
    };
    this.getSelectedCategories = this.getSelectedCategories.bind(this);
    this.updateDimensions = this.updateDimensions.bind(this);
    this.toggleCatTree = this.toggleCatTree.bind(this);
  }

  componentWillMount() {
    this.updateDimensions();
  }

  componentWillUnmount(){
    window.removeEventListener("resize", this.updateDimensions);
  }

  componentDidMount() {
    console.log(this.state);
    window.addEventListener("resize", this.updateDimensions);
    const urlContext = appHelpers.getUrlContext(window.location.href);
    this.setState({urlContext:urlContext},function(){
      if (this.state.categoryId && this.state.categoryId !== 0){
        this.getSelectedCategories(this.state.categories,this.state.categoryId);
      } else {
        this.setState({loading:false});
      }
    });
  }

  getSelectedCategories(categories,catId){
    const selectedCategory = appHelpers.getSelectedCategory(this.state.categories,catId);
    const selectedCategories = this.state.selectedCategories;
    if (selectedCategory){
      selectedCategory.selectedIndex = selectedCategories.length;
      selectedCategories.push(selectedCategory);
    }
    this.setState({selectedCategories:selectedCategories},function(){
      if (selectedCategory && selectedCategory.parent_id){
        this.getSelectedCategories(categories,parseInt(selectedCategory.parent_id));
      } else {
        this.setState({loading:false});
      }
    });
  }

  updateDimensions(){
    const device = appHelpers.getDeviceFromWidth(window.innerWidth);
    this.setState({device:device});
  }

  toggleCatTree(){
    const showCatTree = this.state.showCatTree === true ? false : true;
    const catTreeCssClass = this.state.catTreeCssClass === "open" ? "" : "open";
    this.setState({showCatTree:showCatTree,catTreeCssClass:catTreeCssClass});
  }

  render(){
    let categoryTreeDisplay, selectedCategoryDisplay;
    if (!this.state.loading){

      if (this.state.device === "tablet" && this.state.selectedCategories &&  this.state.selectedCategories.length > 0){
        selectedCategoryDisplay = (
          <SelectedCategory
            categoryId={this.state.categoryId}
            selectedCategory={this.state.selectedCategories[0]}
            selectedCategories={this.state.selectedCategories}
            onCatTreeToggle={this.toggleCatTree}
          />
        );
      }
      if (this.state.device === "tablet" && this.state.showCatTree || this.state.device !== "tablet" || this.state.selectedCategories && this.state.selectedCategories.length === 0) {
        if (this.state.categories){
          const urlContext = this.state.urlContext;
          const categoryId = this.state.categoryId;
          const selectedCategories = this.state.selectedCategories;
          const categoryTree = this.state.categories.sort(appHelpers.sortArrayAlphabeticallyByTitle).map((cat,index) => (
            <CategoryItem
              key={index}
              category={cat}
              categoryId={categoryId}
              urlContext={urlContext}
              selectedCategories={selectedCategories}
            />
          ));

          let allCatItemCssClass;
          if (this.state.categoryId && this.state.categoryId !== 0){
            allCatItemCssClass = "";
          } else {
            if (window.location.href === window.baseUrl + this.state.urlContext + "/browse/" ||
                window.location.href === window.baseUrl + this.state.urlContext){
                  allCatItemCssClass = "active";
            }
          }

          categoryTreeDisplay = (
            <ul className="main-list">
              <li className={"cat-item" + " " + allCatItemCssClass}>
                <a href={window.baseUrl + this.state.urlContext +"/browse/"}><span className="title">All</span></a>
              </li>
              {categoryTree}
            </ul>
          );
        }
      }

    }

    return(
      <div id="category-tree" className={this.state.device + " " + this.state.catTreeCssClass}>
        {selectedCategoryDisplay}
        {categoryTreeDisplay}
      </div>
    );
  }
}

class CategoryItem extends React.Component {
  constructor(props){
  	super(props);
  	this.state = {};
  }

  render(){
    let categoryChildrenDisplay;

    const categoryType = appHelpers.getCategoryType(this.props.selectedCategories,this.props.categoryId,this.props.category.id);
    if (this.props.category.has_children === true && categoryType && this.props.lastChild !== true){

      const urlContext = this.props.urlContext;
      const categoryId = this.props.categoryId;
      const category = this.props.category;
      const selectedCategories = this.props.selectedCategories;
      const children = appHelpers.convertObjectToArray(this.props.category.children);
      let lastChild;
      if (categoryType === "selected"){
        lastChild = true;
      }

      const categoryChildren = children.sort(appHelpers.sortArrayAlphabeticallyByTitle).map((cat,index) => (
        <CategoryItem
          key={index}
          category={cat}
          categoryId={categoryId}
          urlContext={urlContext}
          selectedCategories={selectedCategories}
          lastChild={lastChild}
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
    if (this.props.categoryId === parseInt(this.props.category.id)){
      categoryItemClass += " active";
    }

    let productCountDisplay;
    if (this.props.category.product_count !== "0"){
      productCountDisplay = this.props.category.product_count;
    }

    const categoryItemLink = appHelpers.generateCategoryLink(window.baseUrl,this.props.urlContext,this.props.category.id,window.location.href);

    return(
      <li id={"cat-"+this.props.category.id} className={categoryItemClass}>
        <a href={categoryItemLink}>
          <span className="title">{this.props.category.title}</span>
          <span className="product-counter">{productCountDisplay}</span>
        </a>
        {categoryChildrenDisplay}
      </li>
    )
  }
}

class SelectedCategory extends React.Component {
  constructor(props){
  	super(props);
  	this.state = {};
  }

  render(){
    let selectedCategoryDisplay;
    if (this.props.selectedCategory){
      selectedCategoryDisplay = (
        <a onClick={this.props.onCatTreeToggle}>{this.props.selectedCategory.title}</a>
      );
    }

    let selectedCategoriesDisplay;
    if (this.props.selectedCategories){
      const selectedCategoriesReverse = this.props.selectedCategories.slice(0);
      selectedCategoriesDisplay = selectedCategoriesReverse.reverse().map((sc,index) => (
        <a key={index}>{sc.title}</a>
      ));
    }

    return (
      <div onClick={this.props.onCatTreeToggle} id="selected-category-tree-item">
        {selectedCategoriesDisplay}
        <span className="selected-category-arrow-down"></span>
      </div>
    )
  }
}

ReactDOM.render(
    <CategoryTree />,
    document.getElementById('category-tree-container')
);
