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
    console.log(this.state);
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
      <div id="community-page-header">
        <h1>Community</h1>
        <div id="community-page-header-banner">
          <div className="header-banner-row">
            <p>{this.props.headerData.countActiveMembers}</p>
            <span>contributors added</span>
          </div>
          <div className="header-banner-row">
            <p>{this.props.headerData.countProjects}</p>
            <span>products</span>
          </div>
          <div id="header-banner-bottom">
            <div className="center">
              <a href="/register">
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
      tabs:[]
    };
    this.renderTabs = this.renderTabs.bind(this);
    this.handleTabMenuItemClick = this.handleTabMenuItemClick.bind(this);
  }

  componentDidMount() {
    this.renderTabs();
  }

  renderTabs(selectedIndex){
    if (!selectedIndex){ selectedIndex = 0; }
    const tabs = window.appHelpers.generateTabsMenuArray();
    this.setState({
      tabs:tabs,
      selectedIndex:selectedIndex
    },function(){
      // get selected tab thing
      const self = this;
      const selectedTab = self.state.tabs[self.state.selectedIndex];
      $.ajax({url: selectedTab.url,cache: false}).done(function(response){
        console.log(response);
        self.setState({
          tabContent:{
            title:selectedTab.title,
            data:response.data
          },
          loading:false
        })
      });
    });
  }

  handleTabMenuItemClick(itemIndex){
    this.renderTabs(itemIndex);
  }

  render(){
    console.log(this.state);
    let tabsMenu, tabContent;
    if (this.state.loading){
      tabContent = <div>loading</div>
    } else if (this.state.loading === false){
      const selectedIndex = this.state.selectedIndex;
      const tabsMenuDisplay = this.state.tabs.map((t,index) => (
        <CommunityPageTabMenuItem
          key={index}
          index={index}
          selectedIndex={selectedIndex}
          tab={t}
          onTabMenuItemClick={this.handleTabMenuItemClick}
        />
      ))
      tabsMenu = (
        <ul>
          {tabsMenuDisplay}
        </ul>
      );

      const data = this.state.tabContent.data;
      if (this.state.selectedIndex === 0){
        tabContent = (
          <SupportersTab
            items={data}
          />
        );
      } else if (this.state.selectedIndex === 1){
        tabContent = (
          <MostPlingedCreatorsTab
            items={data}
          />
        );
      }

    }

    return(
      <div id="community-page-tabs-container">
        <div id="tabs-menu">
          {tabsMenu}
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
    return(
      <li>
        <a onClick={this.onTabMenuItemClick}>
          {this.props.tab.title}
        </a>
      </li>
    );
  }
}

class SupportersTab extends React.Component {
  constructor(props){
  	super(props);
  	this.state = {};
  }

  componentDidMount() {
    console.log(this.props);
  }

  render(){
    let supportersDisplay;
    if (this.props.items && this.props.items.length > 0){
      supportersDisplay = this.props.items.map((supporter,index) => (
        <CommunityListItem
          key={index}
          item={supporter}
          type={'supporter'}
        />
      ));
    }
    return(
      <div className="community-tab" id="supporters-tab">
        {supportersDisplay}
      </div>
    );
  }
}

class MostPlingedCreatorsTab extends React.Component {
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
        />
      ))
    }
    return(
      <div className="community-tab" id="most-pling-creators-tab">
        {creatorsDisplay}
      </div>
    );
  }
}

class CommunityListItem extends React.Component {
  constructor(props){
  	super(props);
  	this.state = {};
  }

  render(){
    let i = this.props.item;
    let specificInfoDisplay;
    if (this.props.type === 'supporter'){
      specificInfoDisplay = <li>supporter id : {i.supporter_id}</li>
    } else if (this.props.type){
      specificInfoDisplay = <li>cnt : {i.cnt}</li>
    }
    return(
      <div className="supporter-list-item">
        <ul>
          <li>member id : {i.member_id}</li>
          <li>username : {i.username}</li>
          <li>profile_image_url : {i.profile_image_url}</li>
          <li>created at : {i.created_at}</li>
          {specificInfoDisplay}
        </ul>
      </div>
    );
  }
}


ReactDOM.render(
    <CommunityPage />,
    document.getElementById('community-page-container')
);
