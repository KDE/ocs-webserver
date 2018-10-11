class MetaHeader extends React.Component {
  constructor(props){
  	super(props);
  	this.state = {
      domains:window.domains,
      baseUrl:window.baseUrl,
      blogUrl:window.blogUrl,
      forumUrl:window.forumUrl,
      loginUrl:window.loginUrl,
      logoutUrl:window.logoutUrl,
      sName:window.sName,
      user:{},
    };
    this.getUser = this.getUser.bind(this);
  }

  componentDidMount() {
    console.log(this.state);
    this.getUser();
  }

  getUser(){
    console.log('get user');
    const decodedCookie = decodeURIComponent(document.cookie);
    let ocs_data = decodedCookie.split('ocs_data=')[1];
    console.log(ocs_data);
    if (ocs_data.indexOf(';') > -1){
      ocs_data = ocs_data.split(';')[0];
    }
    const user = JSON.parse(ocs_data);
    console.log(user);
    this.setState({user:user});
  }

  render(){
    return (
      <nav id="metaheader-nav" className="metaheader">
        <div className="metamenu">
          <DomainsMenu
            domains={domains}
            baseUrl={this.state.baseUrl}
            sName={this.state.sName}
          />
          <UserMenu
            user={this.state.user}
            blogUrl={this.state.blogUrl}
            forumUrl={this.state.forumUrl}
            loginUrl={this.state.loginUrl}
            logoutUrl={this.state.logoutUrl}
          />
        </div>
      </nav>
    )
  }
}

class DomainsMenu extends React.Component {
  constructor(props){
  	super(props);
  	this.state = {};
  }

  componentDidMount() {
    const menuGroups = appHelpers.generateMenuGroupsArray(this.props.domains);
    this.setState({menuGroups:menuGroups});
  }

  render(){
    let menuGroupsDisplayLeft, menuGroupsDisplayRight;
    if (this.state.menuGroups){
      menuGroupsDisplayLeft = this.state.menuGroups.slice(0,2).map((mg,i) => (
        <DomainsMenuGroup
          key={i}
          domains={this.props.domains}
          menuGroup={mg}
          sName={this.props.sName}
        />
      ));
      menuGroupsDisplayRight = this.state.menuGroups.slice(2).map((mg,i) => (
        <DomainsMenuGroup
          key={i}
          domains={this.props.domains}
          menuGroup={mg}
          sName={this.props.sName}
        />
      ));
    }

    return (
      <ul className="metaheader-menu left" id="domains-menu">
        <li className="active">
          <a href={this.props.baseUrl}>
            <img src={this.props.baseUrl + "/images/system/ocs-logo-rounded-16x16.png"} className="logo"/>
            openDesktop.org :
          </a>
        </li>
        <li id="domains-dropdown-menu" className="dropdown">
          <a id="dropdownMenu3"
          data-toggle="dropdown"
          aria-haspopup="true"
          aria-expanded="true">Themes & Apps</a>
          <ul className="dropdown-menu dropdown-menu-right" aria-labelledby="dropdownMenu3">
            <li className="submenu-container">
              <ul>
                {menuGroupsDisplayLeft}
              </ul>
            </li>
            <li className="submenu-container">
              <ul>
                {menuGroupsDisplayRight}
              </ul>
            </li>
          </ul>
        </li>
        <li id="discussion-boards" className="dropdown">
          <a id="dropdownMenu4"
          data-toggle="dropdown"
          aria-haspopup="true"
          aria-expanded="true">Discussion Boards</a>
          <ul className="dropdown-menu dropdown-menu-right" aria-labelledby="dropdownMenu4">
            <li><a href="https://forum.opendesktop.org/c/general">General</a></li>
            <li><a href="https://forum.opendesktop.org/c/themes-and-apps">Themes & Apps</a></li>
            <li><a href="https://www.opencode.net/">Coding</a></li>
          </ul>
        </li>
      </ul>
    )
  }
}

class DomainsMenuGroup extends React.Component {
  constructor(props){
  	super(props);
  	this.state = {};
    this.filterDomainsByMenuGroup = this.filterDomainsByMenuGroup.bind(this);
  }

  filterDomainsByMenuGroup(domain){
    if (domain.menugroup === this.props.menuGroup){
      return domain;
    }
  }

  render(){
      const domainsDisplay = this.props.domains.filter(this.filterDomainsByMenuGroup).map((domain,index) => {
        let domainPrefix = "http://";
        if (domain.menuhref.indexOf('pling.cc') === -1 && domain.menuhref.indexOf('www') === -1){
          domainPrefix += "www.";
        }
        return (
          <li key={index}>
            <a href={domainPrefix + domain.menuhref}>{domain.name}</a>
          </li>
        );
      });

    return (
      <li>
        <a className="groupname"><b>{this.props.menuGroup}</b></a>
        <ul className="domains-sub-menu">
          {domainsDisplay}
        </ul>
      </li>
    )
  }
}

class UserMenu extends React.Component {
  constructor(props){
  	super(props);
  	this.state = {};
  }

  render(){
    let userDropdownDisplay, userAppsContextDisplay;
    if (this.props.user && this.props.user.member_id){
      userDropdownDisplay = (
        <UserLoginMenuContainer
          user={this.props.user}
          logoutUrl={this.props.logoutUrl}
        />
      );
      userAppsContextDisplay = (
        <UserContextMenuContainer
          user={this.props.user}
          forumUrl={this.props.forumUrl}
        />
      )
    } else {
      userDropdownDisplay = (
        <li id="user-login-container"><a href={this.props.loginUrl} className="btn btn-metaheader">Login</a></li>
      )
    }


    let plingListUrl = "https://www.opendesktop.cc/#plingList",
        ocsapiContentUrl = "https://www.opendesktop.cc/#ocsapiContent",
        aboutContentUrl = "https://www.opendesktop.cc/#aboutContent",
        linkTarget = "_blank";

    if (window.location.hostname === "www.opendesktop.cc"){
      plingListUrl = "https://www.opendesktop.cc/plings";
      ocsapiContentUrl = "https://www.opendesktop.cc/partials/ocsapicontent.phtml";
      aboutContentUrl = "https://www.opendesktop.cc/partials/about.phtml";
      linkTarget = "";
    }

    return (
      <div id="user-menu-container" className="right">
        <ul className="metaheader-menu" id="user-menu">
          <li><a href="https://www.opendesktop.cc/community">Community</a></li>
          <li><a href={this.props.blogUrl} target="_blank">Blog</a></li>
          <li><a id="plingList" className="popuppanel" target={linkTarget} href={plingListUrl}>What are Plings?</a></li>
          <li><a id="ocsapiContent" className="popuppanel" target={linkTarget} href={ocsapiContentUrl}>API</a></li>
          <li><a id="aboutContent" className="popuppanel" target={linkTarget} href={aboutContentUrl} >About</a></li>
          {userAppsContextDisplay}
          {userDropdownDisplay}
        </ul>
      </div>
    )
  }
}

class UserContextMenuContainer extends React.Component {
  constructor(props){
  	super(props);
  	this.state = {
      gitlabLink:window.gitlabUrl+"/dashboard/issues?assignee_id="
    };
    this.handleClick = this.handleClick.bind(this);
  }

  componentWillMount() {
    document.addEventListener('mousedown',this.handleClick, false);
  }

  componentWillUnmount() {
    document.removeEventListener('mousedown',this.handleClick, false);
  }

  componentDidMount() {
    console.log('get git lab id');
    const self = this;
    $.ajax({url: window.gitlabUrl+"/api/v4/users?username="+this.props.user.username,cache: false})
      .done(function(response){
        console.log('git lab id:' + response[0].id);
        const gitlabLink = self.state.gitlabLink + response[0].id;
        self.setState({gitlabLink:gitlabLink,loading:false});
      });
  }

  handleClick(e){
    let dropdownClass = "";
    if (this.node.contains(e.target)){
      if (this.state.dropdownClass === "open"){
        if (e.target.className === "th-icon" || e.target.className === "btn btn-default dropdown-toggle"){
          dropdownClass = "";
        } else {
          dropdownClass = "open";
        }
      } else {
        dropdownClass = "open";
      }
    }
    this.setState({dropdownClass:dropdownClass});
  }

  render(){

    return (
      <li ref={node => this.node = node} id="user-context-menu-container">
        <div className={"user-dropdown " + this.state.dropdownClass}>
          <button
            className="btn btn-default dropdown-toggle" type="button" onClick={this.toggleDropDown}>
            <span className="th-icon"></span>
          </button>
          <ul id="user-context-dropdown" className="dropdown-menu dropdown-menu-right">
            <li id="opencode-link-item">
              <a href={window.gitlabUrl+"/dashboard/projects"}>
                <div className="icon"></div>
                <span>Projects</span>
              </a>
            </li>
            <li id="issues-link-item">
              <a href={this.state.gitlabLink}>
                <div className="icon"></div>
                <span>Issues</span>
              </a>
            </li>
            <li id="messages-link-item">
              <a href={window.forumUrl+"/u/"+this.props.user.username+"/messages"}>
                <div className="icon"></div>
                <span>Messages</span>
              </a>
            </li>
          </ul>
        </div>
      </li>
    )
  }
}

class UserLoginMenuContainer extends React.Component {
  constructor(props){
  	super(props);
  	this.state = {};
    this.handleClick = this.handleClick.bind(this);
  }

  componentWillMount() {
    document.addEventListener('mousedown',this.handleClick, false);
  }

  componentWillUnmount() {
    document.removeEventListener('mousedown',this.handleClick, false);
  }

  handleClick(e){
    let dropdownClass = "";
    if (this.node.contains(e.target)){
      if (this.state.dropdownClass === "open"){
        console.log(e.target.className);
        if (e.target.className === "th-icon" || e.target.className === "btn btn-default dropdown-toggle"){
          dropdownClass = "";
        } else {
          dropdownClass = "open";
        }
      } else {
        dropdownClass = "open";
      }
    }
    this.setState({dropdownClass:dropdownClass})
  }


  render(){
    return (
      <li id="user-login-menu-container" ref={node => this.node = node}>
        <div className={"user-dropdown " + this.state.dropdownClass}>
          <button
            className="btn btn-default dropdown-toggle"
            type="button"
            id="userLoginDropdown">
            <img className="th-icon" src={this.props.user.avatar}/>
          </button>
          <ul className="dropdown-menu dropdown-menu-right">
            <li id="user-info-menu-item">
              <div id="user-info-section">
                <div className="user-avatar">
                  <div className="no-avatar-user-letter">
                    <img src={this.props.user.avatar}/>
                  </div>
                </div>
                <div className="user-details">
                  <ul>
                    <li><b>{this.props.user.username}</b></li>
                    <li>{this.props.user.mail}</li>
                  </ul>
                </div>
              </div>
            </li>
            <li className="buttons">
              <a href="https://www.opendesktop.cc/settings/" className="btn btn-default btn-metaheader">Settings</a>
              <a href={this.props.logoutUrl} className="btn btn-default pull-right btn-metaheader">Logout</a>
            </li>
          </ul>
        </div>
      </li>
    )
  }
}

ReactDOM.render(
    <MetaHeader />,
    document.getElementById('metaheader')
);
