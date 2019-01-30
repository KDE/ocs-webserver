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
        title:"Top Members",
        url:baseUrl + "/community/getjson?e=topmembers"
      }
    ]
  }

  return {
    getHostNameSuffix
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
  	this.state = {};
  }

  render(){
    return(
      <div id="community-page-tabs-container">
        <div id="tabs-menu">
          tabs
        </div>
        <div id="tabs-content">
          content
        </div>
      </div>
    );
  }
}

ReactDOM.render(
    <CommunityPage />,
    document.getElementById('community-page-container')
);
