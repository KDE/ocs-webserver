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
    return (
      <aside className="explore-sidebar">
        <ul>
          <li><a href="#">category</a></li>
          <li className="active">
            <a href="#">category</a>
            <ul>
              <li><a href="#">subcategory</a></li>
              <li><a href="#">subcategory</a></li>
              <li><a href="#">subcategory</a></li>
              <li><a href="#">subcategory</a></li>
              <li><a href="#">subcategory</a></li>
              <li><a href="#">subcategory</a></li>
            </ul>
          </li>
          <li><a href="#">category</a></li>
          <li><a href="#">category</a></li>
          <li><a href="#">category</a></li>
          <li><a href="#">category</a></li>
        </ul>
      </aside>
    )
  }
}

const mapStateToExploreSideBarProps = (state) => {
  const device = state.device;
  const products = state.products;
  return {
    device,
    products
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

class ExploreTopBar extends React.Component {
  constructor(props){
  	super(props);
  	this.state = {};
  }

  componentWillReceiveProps(nextProps) {
    console.log(nextProps.filters);
  }

  render(){
    console.log(this.props);
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
