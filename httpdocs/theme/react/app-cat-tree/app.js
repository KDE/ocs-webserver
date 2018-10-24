class CategoryTree extends React.Component {
  constructor(props){
  	super(props);
  	this.state = {
      categories:window.catTree,
      categoryId:window.categoryId,
      catTreeCssClass:"",
      selectedCategories:[],
      showCatTree:false,
      backendView:window.backendView,
      loading:true,
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
    window.addEventListener("resize", this.updateDimensions);
    const urlContext = appHelpers.getUrlContext(window.location.href);
    console.log('url context: ' + urlContext);
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
    if (typeof(selectedCategory) !== 'undefined'){
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
          const self = this;
          const categoryTree = this.state.categories.sort(appHelpers.sortArrayAlphabeticallyByTitle).map((cat,index) => (
            <CategoryItem
              key={index}
              category={cat}
              categoryId={self.state.categoryId}
              urlContext={self.state.urlContext}
              selectedCategories={self.state.selectedCategories}
              backendView={self.state.backendView}
            />
          ));

          const allCatItemCssClass = appHelpers.getAllCatItemCssClass(window.location.href,window.baseUrl,this.state.urlContext, this.state.categoryId);
          let baseUrl;
          if (window.baseUrl !== window.location.origin){
            baseUrl = window.location.origin;
          }

          const categoryItemLink = appHelpers.generateCategoryLink(window.baseUrl,this.state.urlContext,"all",window.location.href);

          categoryTreeDisplay = (
            <ul className="main-list">
              <li className={"cat-item" + " " + allCatItemCssClass}>
                <a href={categoryItemLink}><span className="title">All</span></a>
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
    if (this.props.category.has_children === true && categoryType && this.props.lastChild !== true ||
        this.props.category.has_children === true && this.props.backendView === true){

      const self = this;

      let lastChild;
      if (categoryType === "selected"){
        lastChild = true;
      }

      const children = appHelpers.convertObjectToArray(this.props.category.children);
      const categoryChildren = children.sort(appHelpers.sortArrayAlphabeticallyByTitle).map((cat,index) => (
        <CategoryItem
          key={index}
          category={cat}
          categoryId={self.props.categoryId}
          urlContext={self.props.urlContext}
          selectedCategories={self.props.selectedCategories}
          lastChild={lastChild}
          parent={self.props.category}
          backendView={self.props.backendView}
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
