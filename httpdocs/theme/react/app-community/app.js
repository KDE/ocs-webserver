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
      console.log(response);
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
          <UsersTab items={data} />
        );
      } else if (this.state.selectedIndex === 1){
        tabContent = (
          <CreatorsTab items={data} />
        );
      } else if (this.state.selectedIndex === 2 || this.state.selectedIndex === 3){
        tabContent = (
          <PlingedProductsTab items={data} />
        );
      } else if (this.state.selectedIndex === 5 || this.state.selectedIndex === 6){
        tabContent = (
          <MemberScoresTab items={data} />
        );
      }

    }

    return(
      <div id="community-page-tabs-container">
        <div id="tabs-menu">
          <ul>
            {tabsMenuDisplay}
          </ul>
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

class UsersTab extends React.Component {
  constructor(props){
  	super(props);
  	this.state = {};
  }

  componentDidMount() {
    console.log(this.props);
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
      <div className="community-tab" id="supporters-tab">
        {usersDisplay}
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

class PlingedProductsTab extends React.Component {
  constructor(props){
  	super(props);
  	this.state = {};
  }

  render(){
    let productsDisplay;
    if (this.props.items && this.props.items.length > 0){
      productsDisplay = this.props.items.map((product,index) => (
        <CommunityListItem
          key={index}
          item={product}
          type={'product'}
        />
      ))
    }
    return(
      <div className="community-tab" id="most-pling-creators-tab">
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

  componentDidMount() {
    console.log(this.props);
  }

  render(){
    let membersDisplay;
    if (this.props.items && this.props.items.length > 0){
      membersDisplay = this.props.items.map((member,index) => (
        <CommunityListItem
          key={index}
          item={member}
          type={'score'}
        />
      ));
    }
    return(
      <div className="community-tab" id="supporters-tab">
        {membersDisplay}
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
    if (this.props.type === 'user'){
      specificInfoDisplay = <li>supporter id : {i.supporter_id}</li>
    } else if (this.props.type === 'creator'){
      specificInfoDisplay = <li>cnt : {i.cnt}</li>
    }
    return(
      <div className="supporter-list-item">
        <span>{this.props.type}</span>
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
