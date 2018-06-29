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
              <div className="left-sidebar-container mdl-cell--3-col mdl-cell--3-col-tablet mdl-cell--4-col-phone">
                <ExploreLeftSideBarWrapper/>
              </div>

              <div className="main-content mdl-cell--9-col mdl-cell--5-col-tablet mdl-cell--4-col-phone">
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
    return (
      <aside className="explore-right-sidebar">
        <div className="ers-section">
          <a href="https://www.opendesktop.org/p/1175480/" target="_blank">
            <img id="download-app" src="/images/system/download-app.png"/>
          </a>
        </div>
        <div className="ers-section">
          <a href="/support" id="become-a-supporter" className="mdl-button mdl-js-button mdl-button--colored mdl-button--raised mdl-js-ripple-effect mdl-color--primary">
            Become a supporter
          </a>
        </div>
        <div className="ers-section">
          <ExploreSupportersContainerWrapper/>
        </div>
        <div className="ers-section">
          <RssNewsContainer/>
        </div>
        <div className="ers-section">
          <BlogFeedContainer/>
        </div>
        <div className="ers-section">
          <ExploreCommentsContainerWrapper/>
        </div>
        <div className="ers-section">
          <ExploreTopProductsWrapper/>
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

class ExploreSupportersContainer extends React.Component {
  constructor(props){
  	super(props);
  	this.state = {};
  }


  render(){
    let supportersContainer;
    if (this.props.supporters){
      const cArray = categoryHelpers.convertCatChildrenObjectToArray(this.props.supporters);
      const supporters = cArray.map((sp,index) => (
        <div className="supporter-item" key={index}>
          <a href={"/member/"+sp.member_id} className="item">
            <img src={sp.profile_image_url}/>
          </a>
        </div>
      ));
      supportersContainer = <div className="supporter-list-wrapper">{supporters}</div>;
    }

    return (
      <div id="supporters-container" className="sidebar-feed-container">
        <h3>{this.props.supporters.length} people support those who create freedom</h3>
        {supportersContainer}
      </div>
    )
  }
}

const mapStateToExploreSupportersContainerProps = (state) => {
  const supporters = state.supporters;
  return {
    supporters
  }
}

const mapDispatchToExploreSupportersContainerProps = (dispatch) => {
  return {
    dispatch
  }
}

const ExploreSupportersContainerWrapper = ReactRedux.connect(
  mapStateToExploreSupportersContainerProps,
  mapDispatchToExploreSupportersContainerProps
)(ExploreSupportersContainer)

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
          <a className="title" href={fi.url}>
            <span>{fi.title}</span>
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
      self.setState({items:topics});
    });
  }

  render(){
    let feedItemsContainer;
    if (this.state.items){

      const feedItems = this.state.items.map((fi,index) => (
        <li key={index}>
          <a className="title" href={"https://forum.opendesktop.org//t/" + fi.id}>
            <span>{fi.title}</span>
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

class ExploreCommentsContainer extends React.Component {
  constructor(props){
  	super(props);
  	this.state = {};
  }

  render(){
    let commentsContainer;
    if (this.props.comments){
      const comments = this.props.comments.map((cm,index) => (
        <li key={index}>
          <div className="cm-content">
            <span className="cm-userinfo">
              <img src={cm.profile_image_url}/>
              <span className="username"><a href={"/p/"+cm.comment_target_id}>{cm.username}</a></span>
            </span>
            <a className="title" href={"/member/"+cm.member_id}><span>{cm.title}</span></a>
            <span className="content">
              {cm.comment_text}
            </span>
            <span className="info-row">
              <span className="date">
                {appHelpers.getTimeAgo(cm.comment_created_at)}
              </span>
            </span>
          </div>
        </li>
      ));
      commentsContainer = <ul>{comments}</ul>
    }
    return (
      <div id="blog-feed-container" className="sidebar-feed-container">
        <h3>Forum</h3>
        {commentsContainer}
      </div>
    )
  }
}

const mapStateToExploreCommentsContainerProps = (state) => {
  const comments = state.comments;
  return {
    comments
  }
}

const mapDispatchToExploreCommentsContainerProps = (dispatch) => {
  return {
    dispatch
  }
}

const ExploreCommentsContainerWrapper = ReactRedux.connect(
  mapStateToExploreCommentsContainerProps,
  mapDispatchToExploreCommentsContainerProps
)(ExploreCommentsContainer);

class ExploreTopProducts extends React.Component {
  constructor(props){
  	super(props);
  	this.state = {};
  }

  render(){
    let topProductsContainer;
    if (this.props.topProducts){

      let imageBaseUrl;
      if (store.getState().env === 'live') {
        imageBaseUrl = 'cn.pling.com';
      } else {
        imageBaseUrl = 'cn.pling.it';
      }

      const topProducts = this.props.topProducts.map((tp,index) => (
        <li key={index}>
          <img src={"https://" + imageBaseUrl + "/cache/40x40/img/" + tp.image_small}/>
          <a href={"/p/" + tp.project_id}>
            {tp.title}
          </a>
          <span className="cat-name">
            {tp.cat_title}
          </span>
        </li>
      ));

      topProductsContainer = <ol>{topProducts}</ol>

    }
    return (
      <div id="top-products-container" className="sidebar-feed-container">
        <h3>3 Months Ranking</h3>
        <small>(based on downloads)</small>
        {topProductsContainer}
      </div>
    )
  }
}

const mapStateToExploreTopProductsProps = (state) => {
  const topProducts = state.topProducts;
  return {
    topProducts
  }
}

const mapDispatchToExploreTopProductsProps = (dispatch) => {
  return {
    dispatch
  }
}

const ExploreTopProductsWrapper = ReactRedux.connect(
  mapStateToExploreTopProductsProps,
  mapDispatchToExploreTopProductsProps
)(ExploreTopProducts);
