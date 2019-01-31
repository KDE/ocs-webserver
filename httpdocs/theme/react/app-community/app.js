window.appHelpers = (function(){

  function getHostNameSuffix(){
    let hostNameSuffix = "org";
    if (location.hostname.endsWith('cc')) {
      hostNameSuffix = "cc";
    } else if (location.hostname.endsWith('localhost')) {
      hostNameSuffix = "localhost";
    }
    return hostNameSuffix;
  }

  function generateTabsMenuArray(){
    const baseUrl = "https://www.opendesktop." + this.getHostNameSuffix();
    const tabsMenuArray = [
      {
        title:"Supporters",
        url:baseUrl + "/community/getjson?e=supporters"
      },{
        title:"Most plinged Creators",
        url:baseUrl + "/community/getjson?e=mostplingedcreators"
      },{
        title:"Most plinged Products",
        url:baseUrl + "/community/getjson?e=mostplingedproducts"
      },{
        title:"Recently plinged Products",
        url:baseUrl + "/community/getjson?e=plingedprojects"
      },{
        title:"New Members",
        url:baseUrl + "/community/getjson?e=newmembers"
      },{
        title:"Top Members",
        url:baseUrl + "/community/getjson?e=topmembers"
      },{
        title:"Top List Members",
        url:baseUrl + "/community/getjson?e=toplistmembers"
      }
    ];
    return tabsMenuArray;
  }

  return {
    getHostNameSuffix,
    generateTabsMenuArray
  }
}());

class CommunityPage extends React.Component {
  constructor(props){
  	super(props);
  	this.state = {
      headerData:window.json_data.data
    };
  }

  componentDidMount() {
    /*
      var json_data = <?=json_encode($this->json_data)?>; hier bekommest du die oberteil info
      tabs info top members https://www.opendesktop.cc
      hier ist supporters https://www.opendesktop.cc/community/getjson?e=supporters
      alle tab events sind hier. /var/www/ocs-webserver/application/modules/default/controllers/CommunityController.php
      getjsonAction
    */
  }

  render(){
    return(
      <div id="community-page">
        <div className="container">
          <CommunityPageHeader
            headerData={this.state.headerData}
          />
          <CommunityPageTabsContainer />
        </div>
      </div>
    );
  }
}

class CommunityPageHeader extends React.Component {
  constructor(props){
  	super(props);
  	this.state = {};
  }

  render(){
    return(
      <div id="community-page-header" className="head-wrap">
        <h1>Community</h1>
        <div id="community-page-header-banner" className="col-lg-5 col-md-5 col-sm-6 col-xs-8">
          <div id="header-banner-top">
            <div className="header-banner-row">
              <p>{this.props.headerData.countActiveMembers}</p>
              <span>contributors added</span>
            </div>
            <div className="header-banner-row">
              <p>{this.props.headerData.countProjects}</p>
              <span>products</span>
            </div>
          </div>
          <div id="header-banner-bottom">
            <div className="center">
              <a className="btn btn-native" href="/register">
                Register
              </a>
              <span>to join the community</span>
            </div>
          </div>
        </div>
      </div>
    );
  }
}

class CommunityPageTabsContainer extends React.Component {
  constructor(props){
  	super(props);
  	this.state = {
      loading:true,
      tabs:window.appHelpers.generateTabsMenuArray(),
      selectedIndex:0
    };
    this.renderTabs = this.renderTabs.bind(this);
    this.getSelectedTabData = this.getSelectedTabData.bind(this);
    this.handleTabMenuItemClick = this.handleTabMenuItemClick.bind(this);
  }

  componentDidMount() {
    this.getSelectedTabData();
  }

  renderTabs(selectedIndex){
    if (!selectedIndex){ selectedIndex = 0; }
    const tabs = window.appHelpers.generateTabsMenuArray();
    this.setState({
      tabs:tabs,
      selectedIndex:selectedIndex
    },function(){
      this.getSelectedTabData();
    });
  }

  getSelectedTabData(){
    // get selected tab thing
    const self = this;
    const selectedTab = self.state.tabs[self.state.selectedIndex];
    $.ajax({url: selectedTab.url,cache: false}).done(function(response){
      self.setState({
        tabContent:{
          title:selectedTab.title,
          data:response.data
        },
        loading:false
      })
    });
  }

  handleTabMenuItemClick(itemIndex){
    this.setState({loading:true},function(){
      this.renderTabs(itemIndex);
    });
  }

  render(){
    const selectedIndex = this.state.selectedIndex;
    const tabsMenuDisplay = this.state.tabs.map((t,index) => (
      <CommunityPageTabMenuItem
        key={index}
        index={index}
        selectedIndex={selectedIndex}
        tab={t}
        onTabMenuItemClick={this.handleTabMenuItemClick}
      />
    ));

    let tabContent;
    if (this.state.loading){

      tabContent = <div>loading</div>

    } else if (this.state.loading === false){

      const data = this.state.tabContent.data;

      if (this.state.selectedIndex === 0 || this.state.selectedIndex === 4){
        tabContent = (
          <UsersTab selectedIndex={this.state.selectedIndex} items={data} />
        );
      } else if (this.state.selectedIndex === 1){
        tabContent = (
          <CreatorsTab  selectedIndex={this.state.selectedIndex} items={data} />
        );
      } else if (this.state.selectedIndex === 2 || this.state.selectedIndex === 3){
        tabContent = (
          <PlingedProductsTab selectedIndex={this.state.selectedIndex} items={data} />
        );
      } else if (this.state.selectedIndex === 5 || this.state.selectedIndex === 6){
        tabContent = (
          <MemberScoresTab selectedIndex={this.state.selectedIndex} items={data} />
        );
      }

    }

    return(
      <div id="community-page-tabs-container" className="body-wrap">
        <div id="tabs-menu">
          {tabsMenuDisplay}
        </div>
        <div id="tabs-content">
          {tabContent}
        </div>
      </div>
    );
  }
}

class CommunityPageTabMenuItem extends React.Component {
  constructor(props){
  	super(props);
  	this.state = {};
    this.onTabMenuItemClick = this.onTabMenuItemClick.bind(this);
  }

  onTabMenuItemClick(){
    this.props.onTabMenuItemClick(this.props.index);
  }

  render(){
    const activeCssClass = this.props.index === this.props.selectedIndex ? "active" : "";
    return(
      <div className={"tab-menu-item " + activeCssClass}>
        <a onClick={this.onTabMenuItemClick}>
          {this.props.tab.title}
        </a>
      </div>
    );
  }
}

class UsersTab extends React.Component {
  constructor(props){
  	super(props);
  	this.state = {};
  }

  render(){
    let usersDisplay;
    if (this.props.items && this.props.items.length > 0){
      usersDisplay = this.props.items.map((user,index) => (
        <CommunityListItem
          key={index}
          item={user}
          type={'user'}
        />
      ));
    }
    return(
      <div className="community-tab card-list-display" id="supporters-tab">
        <ul>{usersDisplay}</ul>
      </div>
    );
  }
}

class CreatorsTab extends React.Component {
  constructor(props){
  	super(props);
  	this.state = {};
  }

  render(){
    let creatorsDisplay;
    if (this.props.items && this.props.items.length > 0){
      creatorsDisplay = this.props.items.map((creator,index) => (
        <CommunityListItem
          key={index}
          item={creator}
          type={'creator'}
          selectedIndex={this.props.selectedIndex}
        />
      ))
    }
    return(
      <div className="community-tab top-list-display" id="most-pling-creators-tab">
        <ol>{creatorsDisplay}</ol>
      </div>
    );
  }
}

class PlingedProductsTab extends React.Component {
  constructor(props){
  	super(props);
  	this.state = {};
  }

  render(){
    let products;
    if (this.props.items && this.props.items.length > 0){
      products = this.props.items.map((product,index) => (
        <CommunityListItem
          key={index}
          item={product}
          type={'product'}
        />
      ))
    }

    let productsDisplay,
        tabContainerCssClass;
    if (this.props.selectedIndex === 3){
      productsDisplay = (
        <ol>{products}</ol>
      );
      tabContainerCssClass = "top-list-display";
    } else if (this.props.selectedIndex === 2) {
      productsDisplay = (
        <ul>{products}</ul>
      );
      tabContainerCssClass = "card-list-display"
    }
    return(
      <div className={"community-tab " + tabContainerCssClass} id="most-pling-creators-tab">
        {productsDisplay}
      </div>
    );
  }
}

class MemberScoresTab extends React.Component {
  constructor(props){
  	super(props);
  	this.state = {};
  }

  render(){
    let members;
    if (this.props.items && this.props.items.length > 0){
      members = this.props.items.map((member,index) => (
        <CommunityListItem
          key={index}
          item={member}
          type={'score'}
        />
      ));
    }

    let membersDisplay,
        tabContainerCssClass;
    if (this.props.selectedIndex === 6){
      membersDisplay = (
        <ol>{members}</ol>
      );
      tabContainerCssClass = "top-list-display";
    } else if (this.props.selectedIndex === 5) {
      membersDisplay = (
        <ul>{members}</ul>
      );
      tabContainerCssClass = "card-list-display"
    }

    return(
      <div className={"community-tab " + tabContainerCssClass} id="supporters-tab">
        {membersDisplay}
      </div>
    );
  }
}

class CommunityListItem extends React.Component {
  constructor(props){
  	super(props);
    console.log(this.props.item);
  	this.state = {};
  }

  render(){

    const i = this.props.item;
    let score;
    if (this.props.type === 'user'){
      // score = '';
    } else if (this.props.type === 'creator'){
      score = i.cnt;
    } else if (this.props.type === 'product'){
      score = i.laplace_score;
    } else if (this.props.type === 'score'){
      score = i.score
    }

    const usersDisplay = (
      <a className="user-display-container">
        <div className="user">
          <figure><img src={i.profile_image_url}/></figure>
          <span className="username"><a href={"/u/"+i.username+"/"}>{i.username}</a></span>
          <span className="user-created">{i.created_at}</span>
        </div>
      </a>
    );

    const project = {
      id:i.project_id,
      title:i.title,
      cat_title:i.catTitle,
      image_url:i.image_small
    }

    return(
      <li className="list-item">
        {usersDisplay}
      </li>
    );
  }
}

ReactDOM.render(
    <CommunityPage />,
    document.getElementById('community-page-container')
);
