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

  function formatDate(dateString) {
    const monthNames = [
      "Jan", "Feb", "Mar",
      "Apr", "May", "Jun",
      "Jul", "Aug", "Sep",
      "Oct", "Nov", "Dec"
    ];

    const date = dateString.split(' ')[0];
    const year = date.split('-')[0];
    const month = date.split('-')[1];
    const day = date.split('-')[2];
    const monthNameIndex = parseInt(month) - 1;
    const monthName = monthNames[monthNameIndex];

    return monthName + ' ' + day + ' ' + year;
  }

  return {
    getHostNameSuffix,
    generateTabsMenuArray,
    formatDate
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

      tabContent = (
        <div id="loading-container">
           <div className="ajax-loader"></div>
        </div>
      );

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
      const selectedIndex = this.props.selectedIndex;
      usersDisplay = this.props.items.map((user,index) => (
        <CommunityListItem
          key={index}
          index={index}
          selectedIndex={selectedIndex}
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
      const selectedIndex = this.props.selectedIndex;
      creatorsDisplay = this.props.items.map((creator,index) => (
        <CommunityListItem
          key={index}
          item={creator}
          type={'creator'}
          index={index}
          selectedIndex={selectedIndex}
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
      const selectedIndex = this.props.selectedIndex;
      products = this.props.items.map((product,index) => (
        <CommunityListItem
          key={index}
          item={product}
          type={'product'}
          index={index}
          selectedIndex={selectedIndex}
        />
      ))
    }

    let productsDisplay,
        tabContainerCssClass;
    if (this.props.selectedIndex === 2){
      productsDisplay = (
        <ol>{products}</ol>
      );
      tabContainerCssClass = "top-list-display";
    } else if (this.props.selectedIndex === 3) {
      productsDisplay = (
        <ul>{products}</ul>
      );
      tabContainerCssClass = "card-list-display"
    }
    return(
      <div className={"community-tab " + tabContainerCssClass} id="most-pling-product-tab">
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
      const selectedIndex = this.props.selectedIndex;
      members = this.props.items.map((member,index) => (
        <CommunityListItem
          key={index}
          item={member}
          type={'score'}
          index={index}
          selectedIndex={selectedIndex}
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
      <div className={"community-tab " + tabContainerCssClass} id="score-tab">
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

    const i = this.props.item;

    /* USER DISPLAY */
    const userDisplay = (
      <CommunityListItemUserDisplay
        selectedIndex={this.props.selectedIndex}
        item={i}
      />
    );
    /* /USER DISPLAY */

    /* PROJECT DISPLAY */
    let imageBaseUrl;
    if (i.image_small){
      imageBaseUrl = "https://cn.opendesktop."+window.appHelpers.getHostNameSuffix()+"/cache/167x167-0/img/"+i.image_small;
    }

    const projectDisplay = (
      <a href={"/p/"+i.project_id}>
        <div className="project">
          <figure><img src={imageBaseUrl}/></figure>
          <div className="project-info">
            <h3 className="project-title">{i.title} <span className="version">{i.version}</span></h3>
            <span className="cat-title">{i.catTitle}</span>
          </div>
        </div>
      </a>
    );
    /* /PROJECT DISPLAY */

    /* SCORE DISPLAY */
    const scoreDisplay = (
      <CommunityListItemScoreDisplay
        item={i}
      />
    );
    /* /SCORE DISPLAY */

    /* DISPLAY TEMPLATE */
    let displayTemplate;
    if (this.props.selectedIndex === 0 || this.props.selectedIndex === 4){
      displayTemplate = (
        <div className="list-item-template">
          {userDisplay}
        </div>
      );
    } else if (this.props.selectedIndex === 1){
      displayTemplate = (
        <div className="list-item-template">
          <div className="creator-wrapper">
            <div className="list-ranking">{this.props.index + 1}</div>
            {userDisplay}
            {scoreDisplay}
          </div>
        </div>
      );
    } else if (this.props.selectedIndex === 2 || this.props.selectedIndex === 3){
      displayTemplate = (
        <div className="list-item-template">
          <div className="creator-wrapper">
            <div className="left-side-section">
              <div className="list-ranking">{this.props.index + 1}</div>
              {projectDisplay}
            </div>
            <div className="right-side-section">
              {userDisplay}
              {scoreDisplay}
            </div>
          </div>
        </div>
      );
    } else if (this.props.selectedIndex === 5 || this.props.selectedIndex === 6){
      displayTemplate = (
        <div className="list-item-template">
          <div className="scored-wrapper">
            {userDisplay}
            <div className="list-ranking">
              <span className="rank">{this.props.index + 1}</span>
              <span className="sum-plings">{i.score}</span>
            </div>
          </div>
        </div>
      );
    }
    /* /DISPLAY TEMPLATE */

    return(
      <li className="list-item">
        {displayTemplate}
      </li>
    );
  }
}

class CommunityListItemUserDisplay extends React.Component {
  constructor(props){
  	super(props);
  	this.state = {
      showHoverDiv:false
    };
    this.handleImageLoaded = this.handleImageLoaded.bind(this);
    this.getImageElementDimensions = this.getImageElementDimensions.bind(this);
    this.handleMouseIn = this.handleMouseIn.bind(this);
    this.handleMouseOut = this.handleMouseOut.bind(this);
  }

  componentDidMount() {
    this.getImageElementDimensions();
  }

  getImageElementDimensions(){
    const height = this.divElement.clientHeight;
    const width = this.divElement.clientWidth;
    this.setState({
      imgHeight:height,
      imgWidth:width
    });
  }

  handleMouseIn(){
    this.setState({
      showHoverDiv:true,
      loading:true
    },function(){
      const self = this;
      $.get('/member/' + this.props.item.member_id + '/tooltip/', function (res) {
        self.setState({userData:res.data,loading:false});
      });
    });
  }

  handleMouseOut(){
    this.setState({showHoverDiv:false});
  }

  handleImageLoaded(){
    this.getImageElementDimensions();
  }

  render(){

    const i = this.props.item;

    let userCreatedAt;
    if (i.created_at){
      userCreatedAt = window.appHelpers.formatDate(i.created_at);
    }
    let byDisplay;
    if (this.props.selectedIndex === 2){
      byDisplay = <span className="by">by</span>;
    }

    let userHoverDivDisplay;
    if (this.state.showHoverDiv){
      let infoDisplay;
      if (this.state.loading){
        infoDisplay = (
          <div className="user-hover-info">
            <div className="ajax-loader"></div>
          </div>
        )
      } else {

        const userData = this.state.userData;

        let locationDisplay;
        if (userData.countrycity){
          locationDisplay = (
            <span>
              <span className="glyphicon glyphicon-map-marker"></span>
              {userData.countrycity}
            </span>
          );
        }

        infoDisplay = (
          <div className="user-hover-info">
            <span className="username">
              {i.username} {locationDisplay}
            </span>
            <span>{userData.cntProjects} products</span>
            <span>{userData.totalComments} comments</span>
            <span>Liked {userData.cntLikesGave} products</span>
            <span>Got {userData.cntLikesGot} Likes <span className="glyphicon glyphicon-heart"></span></span>
            <span>Last time active: {userData.lastactive_at}</span>
            <span>Member since: {userData.created_at}</span>
          </div>
        )
      }


      const userHoverDivStyle = {
        "left":this.state.imgWidth + "px",
        "marginTop":(this.state.imgHeight / 2) + "px"
      }


      let userHoverCssClass = "";
      if (this.state.loading){
        userHoverCssClass = "loading-user"
      }

      userHoverDivDisplay = (
        <div className={"user-hover-display " + userHoverCssClass} style={userHoverDivStyle}>
          {infoDisplay}
        </div>
      );
    }

    return(
      <a href={"/u/"+i.username+"/"} className="user-display-container">
        <div className="user">
          <figure
            ref={ (divElement) => this.divElement = divElement}
            onMouseOver={(e) => this.handleMouseIn(e)}
            onMouseOut={this.handleMouseOut}>
            <img
              onLoad={this.handleImageLoaded}
              src={i.profile_image_url}/>
          </figure>
          <span className="username">{byDisplay}{i.username}</span>
          <span className="user-created">{userCreatedAt}</span>
        </div>
        {userHoverDivDisplay}
      </a>
    );
  }
}

class CommunityListItemScoreDisplay extends React.Component {
  constructor(props){
  	super(props);
  	this.state = {};
    this.handleMouseIn = this.handleMouseIn.bind(this);
    this.handleMouseOut = this.handleMouseOut.bind(this);
  }

  handleMouseIn(){
    this.setState({
      showHoverDiv:true,
      loading:true
    },function(){
      const self = this;
      $.get('/plings/tooltip/id/' + this.props.item.project_id, function (res) {
        console.log(res);
        self.setState({scoreUsers:res.data,loading:false});
      });
    });
  }

  handleMouseOut(){
    console.log('bye');
  }

  render(){

    let scoreUsersHoverDiv;
    if (this.state.showHoverDiv){
      let scoreUsersDisplay,
          scoreUsersHoverDivHeight;
      if (this.state.loading){
        scoreUsersDisplay = (
          <div className="score-users-display">
            <div className="ajax-loader"></div>
          </div>
        );
      } else {
        const scoreUsers = this.state.scoreUsers.map((su,index) => (
          <div className="score-user" key={index}>
            <figure>
              <img src={su.profile_image_url}/>
            </figure>
            <span>{su.username}</span>
          </div>
        ));
        const scoreUserNumRows = Math.ceil(this.state.scoreUsers.length / 4);
        scoreUsersHoverDivHeight = scoreUserNumRows * 70;
        scoreUsersDisplay = (
          <div className="score-users-display">
            {scoreUsers}
          </div>
        );
      }
      scoreUsersHoverDiv = (
        <div className="score-hover-container" style={{"top":"-" + scoreUsersHoverDivHeight / 2}}>
          {scoreUsersDisplay}
        </div>
      )
    }

    return(
      <div
        className="score-container"
        ref={ (divElement) => this.divElement = divElement}
        onMouseOver={this.handleMouseIn}
        onMouseOut={this.handleMouseOut}>
        <span className="score">
          <img src="/images/system/pling-btn-active.png"/>
          {this.props.item.laplace_score}
        </span>
        {scoreUsersHoverDiv}
      </div>
    );
  }
}

ReactDOM.render(
    <CommunityPage />,
    document.getElementById('community-page-container')
);
