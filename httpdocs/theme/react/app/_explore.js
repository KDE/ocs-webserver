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
          <div className="main-content-container">
            <div className="mdl-grid">
              <div className="left-sidebar-container mdl-cell--3-col mdl-cell--2-col-tablet">
                <ExploreLeftSideBarWrapper/>
              </div>

              <div className="main-content mdl-cell--9-col mdl-cell--4-col-tablet">
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
          <div className="right-sidebar-container">
            <ExploreRightSideBarWrapper/>
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

class ExploreTopBar extends React.Component {
  constructor(props){
  	super(props);
  	this.state = {};
  }

  render(){
    const link = appHelpers.generateFilterUrl(window.location,store.getState().categories.current);
    return (
      <div className="explore-top-bar">
        <a href={link.base + "latest" + link.search} className={this.props.filters.order === "latest" ? "item active" : "item"}>Latest</a>
        <a href={link.base + "top" + link.search} className={this.props.filters.order === "top" ? "item active" : "item"}>Top</a>
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

class ExploreLeftSideBar extends React.Component {
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
      <aside className="explore-left-sidebar">
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

const mapStateToExploreLeftSideBarProps = (state) => {
  const categories = state.categories;
  const filters = state.filters;
  return {
    categories
  }
}

const mapDispatchToExploreLeftSideBarProps = (dispatch) => {
  return {
    dispatch
  }
}

const ExploreLeftSideBarWrapper = ReactRedux.connect(
  mapStateToExploreLeftSideBarProps,
  mapDispatchToExploreLeftSideBarProps
)(ExploreLeftSideBar);

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
        <a className={active === true ? "active" : ""} href={"/browse/cat/" + this.props.category.id + "/ord/" + order + window.location.search}>
          <span className="title">{this.props.category.title}</span>
          <span className="product-counter">{this.props.category.product_count}</span>
        </a>
        {subcatMenu}
      </li>
    )
  }
}

class ExploreRightSideBar extends React.Component {
  constructor(props){
  	super(props);
  	this.state = {};
  }

  render(){
    console.log(store.getState());
    return (
      <aside className="explore-right-sidebar">
        <div className="ers-section">
          <a href="https://www.opendesktop.org/p/1175480/" target="_blank">
            <img id="download-app" src="/images/system/download-app.png"/>
          </a>
        </div>
        <div className="ers-section">
          <a href="/support" className="mdl-button mdl-js-button mdl-button--colored mdl-button--raised mdl-js-ripple-effect mdl-color--primary">
            Become a supporter
          </a>
        </div>
        <div className="ers-section">
          <p>supporters</p>
        </div>
        <div className="ers-section">
          <RssNewsContainer/>
        </div>
        <div className="ers-section">
          <BlogFeedContainer/>
        </div>
        <div className="ers-section">
          <p>comments</p>
        </div>
        <div className="ers-section">
          <p>top products</p>
        </div>
      </aside>
    )
  }
}

const mapStateToExploreRightSideBarProps = (state) => {
  const categories = state.categories;
  const filters = state.filters;
  return {
    categories
  }
}

const mapDispatchToExploreRightSideBarProps = (dispatch) => {
  return {
    dispatch
  }
}

const ExploreRightSideBarWrapper = ReactRedux.connect(
  mapStateToExploreRightSideBarProps,
  mapDispatchToExploreRightSideBarProps
)(ExploreRightSideBar);

class RssNewsContainer extends React.Component {
  constructor(props){
  	super(props);
  	this.state = {};
  }

  componentDidMount() {
    const self = this;
    $.getJSON("https://blog.opendesktop.org/?json=1&callback=?", function (res) {
      self.setState({items:res.posts});
    });
  }

  render(){
    let feedItemsContainer;
    if (this.state.items){

      const feedItems = this.state.items.slice(0,3).map((fi,index) => (
        <li key={index}>
          <a href={fi.url}>
            <span className="title">{fi.title}</span>
          </a>
          <span className="info-row">
            <span className="date">{appHelpers.getTimeAgo(fi.date)}</span>
            <span className="comment-counter">{fi.comment_count} comments</span>
          </span>
        </li>
      ));

      feedItemsContainer = <ul>{feedItems}</ul>;
    }
    return (
      <div id="rss-new-container" className="sidebar-feed-container">
        <h3>News</h3>
        {feedItemsContainer}
      </div>
    )
  }
}

class BlogFeedContainer extends React.Component {
  constructor(props){
  	super(props);
  	this.state = {};
  }

  componentDidMount() {
    const self = this;
    $.ajax("https://forum.opendesktop.org/latest.json").then(function (result) {
      let topics = result.topic_list.topics;
      topics.sort(function(a,b){
        return new Date(b.last_posted_at) - new Date(a.last_posted_at);
      });
      topics = topics.slice(0,3);
      console.log(topics);
      self.setState({items:topics});
    });
  }

  render(){
    let feedItemsContainer;
    if (this.state.items){

      const feedItems = this.state.items.map((fi,index) => (
        <li key={index}>
          <a href={"https://forum.opendesktop.org//t/" + fi.id}>
            <span className="title">{fi.title}</span>
          </a>
          <span className="info-row">
            <span className="date">{appHelpers.getTimeAgo(fi.created_at)}</span>
            <span className="comment-counter">{fi.reply_count} replies</span>
          </span>
        </li>
      ));

      feedItemsContainer = <ul>{feedItems}</ul>;
    }
    return (
      <div id="blog-feed-container" className="sidebar-feed-container">
        <h3>Forum</h3>
        {feedItemsContainer}
      </div>
    )
  }
}
