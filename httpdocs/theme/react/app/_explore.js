class ExplorePage extends React.Component {
  constructor(props){
  	super(props);
  	this.state = {
      device:store.getState().device,
      products:store.getState().products
    };
  }

  componentWillReceiveProps(nextProps) {
    if (nextProps.device){
      this.setState({device:nextProps.device});
    }
    if (nextProps.products){
      this.setState({products:nextProps.products});
    }
    if (nextProps.filters){
      this.setState({filters:filters});
    }
  }

  render(){
    return (
      <div id="explore-page">
        <div className="wrapper">
          <div className="section">
            <div className="container mdl-grid">
              <div className="sidebar-container mdl-cell--3-col mdl-cell--2-col-tablet">
                <ExploreSideBarWrapper/>
              </div>
              <div className="main-content mdl-cell--9-col  mdl-cell--6-col-tablet">
                <div className="top-bar">
                  <ExploreTopBarWrapper/>
                </div>
                <div className="explore-products-container">
                  <ProductGroup
                    products={this.state.products}
                    device={this.state.device}
                  />
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    )
  }
}

const mapStateToExploreProps = (state) => {
  const device = state.device;
  const products = state.products;
  return {
    device,
    products
  }
}

const mapDispatchToExploreProps = (dispatch) => {
  return {
    dispatch
  }
}

const ExplorePageWrapper = ReactRedux.connect(
  mapStateToExploreProps,
  mapDispatchToExploreProps
)(ExplorePage);

class ExploreSideBar extends React.Component {
  constructor(props){
  	super(props);
  	this.state = {};
  }
  render(){

    let categoryTree;
    if (this.props.categories){
      const filters = this.props.filters;
      const current = this.props.categories.current;
      categoryTree = this.props.categories.items.map((cat,index) => (
        <ExploreSideBarItem
          key={index}
          category={cat}
          current={current}
        />
      ));
    }

    return (
      <aside className="explore-sidebar">
        <ul>
          <li className="category-item">
            <a className={this.props.categories.current === 0 ? "active" : ""} href={"/browse/ord/" + filters.order}>
              <span className="title">All</span>
            </a>
          </li>
          {categoryTree}
        </ul>
      </aside>
    )
  }
}

const mapStateToExploreSideBarProps = (state) => {
  const categories = state.categories;
  const filters = state.filters;
  return {
    categories
  }
}

const mapDispatchToExploreSideBarProps = (dispatch) => {
  return {
    dispatch
  }
}

const ExploreSideBarWrapper = ReactRedux.connect(
  mapStateToExploreSideBarProps,
  mapDispatchToExploreSideBarProps
)(ExploreSideBar);

class ExploreSideBarItem extends React.Component {
  render(){

    const order = store.getState().filters.order;

    let active;
    if (this.props.current === parseInt(this.props.category.id)) active = true;

    let subcatMenu;
    if (this.props.category.has_children && active){
      const cArray = categoryHelpers.convertCatChildrenObjectToArray(this.props.category.children);
      const subcategories = cArray.map((cat,index) => (
        <ExploreSideBarItem
          key={index}
          category={cat}
        />
      ));
      subcatMenu = (
        <ul>
          {subcategories}
        </ul>
      );
    }

    return (
      <li className="category-item">
        <a className={active === true ? "active" : ""} href={"/browse/cat/" + this.props.category.id + "/ord/" + order}>
          <span className="title">{this.props.category.title}</span>
          <span className="product-counter">{this.props.category.product_count}</span>
        </a>
        {subcatMenu}
      </li>
    )
  }
}

class ExploreTopBar extends React.Component {
  constructor(props){
  	super(props);
  	this.state = {};
  }

  render(){
    return (
      <div className="explore-top-bar">
        <a className={this.props.filters.order === "latest" ? "item active" : "item"}>Latest</a>
        <a className={this.props.filters.order === "top" ? "item active" : "item"}>Top</a>
      </div>
    )
  }
}

const mapStateToExploreTopBarProps = (state) => {
  const filters = state.filters;
  return {
    filters
  }
}

const mapDispatchToExploreTopBarProps = (dispatch) => {
  return {
    dispatch
  }
}

const ExploreTopBarWrapper = ReactRedux.connect(
  mapStateToExploreTopBarProps,
  mapDispatchToExploreTopBarProps
)(ExploreTopBar);
