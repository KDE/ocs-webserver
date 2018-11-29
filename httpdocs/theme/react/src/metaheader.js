import '@babel/polyfill';
import React from 'react';
import ReactDOM from 'react-dom';

// Still need jQuery?
import $ from 'jquery';

// Use this object for config data instead of window.domains,
// window.baseUrl, window.etc... so don't set variables in global scope.
// Please see initConfig()
let config = {};

async function initConfig(target) {
  // API https://www.opendesktop.org/home/metamenujs should send
  // JSON data with CORS.
  // Please see config-dummy.php.

  // Also this API call sends cookie of www.opendesktop.org/cc
  // by fetch() with option "credentials: 'include'", so
  // www.opendesktop.org/cc possible detect user session.
  // Can we consider if include user information into JSON data of
  // API response instead of cookie set each external site?

  let url = '';

  if (location.hostname.endsWith('opendesktop.org')) {
    url = `https://www.opendesktop.org/home/metamenubundlejs?target=${target}`;
  }
  else if (location.hostname.endsWith('opendesktop.cc')) {
    url = `https://www.opendesktop.cc/home/metamenubundlejs?target=${target}`;
  }
  else if (location.hostname.endsWith('localhost')) {
    url = `http://localhost:${location.port}/config-dummy.php`;
  }else if (location.hostname.endsWith('pling.local')) {
    url = `http://pling.local/home/metamenubundlejs?target=${target}`;
  }

  try {
    const response = await fetch(url, {
      mode: 'cors',
      credentials: 'include'
    });
    if (!response.ok) {
      throw new Error('Network response error');
    }
    config = await response.json();
    return true;
  }
  catch (error) {
    console.error(error);
    return false;
  }
}

window.appHelpers = function () {

  function generateMenuGroupsArray(domains) {
    let menuGroups = [];
    domains.forEach(function (domain, index) {
      if (menuGroups.indexOf(domain.menugroup) === -1) {
        menuGroups.push(domain.menugroup);
      }
    });
    return menuGroups;
  }

  function getDeviceFromWidth(width) {
    let device;
    if (width >= 910) {
      device = "large";
    } else if (width < 910 && width >= 610) {
      device = "mid";
    } else if (width < 610) {
      device = "tablet";
    }
    return device;
  }

  function generatePopupLinks() {

    let pLink = {};
    pLink.plingListUrl = "/#plingList", pLink.ocsapiContentUrl = "/#ocsapiContent", pLink.aboutContentUrl = "/#aboutContent", pLink.linkTarget = "_blank";

    if (window.location.hostname.indexOf('opendesktop') === -1 || window.location.hostname === "git.opendesktop.org" || window.location.hostname === "git.opendesktop.cc" || window.location.hostname === "forum.opendesktop.org" || window.location.hostname === "forum.opendesktop.cc" || window.location.hostname === "my.opendesktop.org" || window.location.hostname === "my.opendesktop.cc") {
      pLink.plingListUrl = "/plings";
      pLink.ocsapiContentUrl = "/partials/ocsapicontent.phtml";
      pLink.aboutContentUrl = "/partials/about.phtml";
      pLink.linkTarget = "";
    }
    return pLink;
  }

  function getPopupUrl(key, isExternal, baseUrl) {
    let url = baseUrl;
    return url;
  }

  return {
    generateMenuGroupsArray,
    getDeviceFromWidth,
    generatePopupLinks,
    getPopupUrl
  };
}();

class MetaHeader extends React.Component {
  constructor(props){
  	super(props);
  	this.state = {
      domains:config.domains,
      baseUrl:config.baseUrl,
      blogUrl:config.blogUrl,
      forumUrl:config.forumUrl,
      loginUrl:config.loginUrl,
      logoutUrl:config.logoutUrl,
      gitlabUrl:config.gitlabUrl,
      sName:config.sName,
      isExternal:config.isExternal,
      user:config.user,
      showModal:false,
      modalUrl:'',
      isAdmin:config.json_isAdmin
    };
    this.initMetaHeader = this.initMetaHeader.bind(this);
    this.updateDimensions = this.updateDimensions.bind(this);
    //this.getUser = this.getUser.bind(this);
  }


  componentWillMount() {
    this.updateDimensions();
  }

  componentDidMount() {
    console.log(config);
    console.log(window.location);
    this.initMetaHeader();
  }

  componentWillUnmount(){
    window.removeEventListener("resize", this.updateDimensions);
    window.removeEventListener("orientationchange",this.updateDimensions);

  }

  initMetaHeader(){
    window.addEventListener("resize", this.updateDimensions);
    window.addEventListener("orientationchange",this.updateDimensions);
    //this.getUser();
  }

  getUser(){
    const decodedCookie = decodeURIComponent(document.cookie);
    let ocs_data = decodedCookie.split('ocs_data=')[1];
    if (ocs_data){
      if (ocs_data.indexOf(';') > -1){ ocs_data = ocs_data.split(';')[0]; }
      const user = JSON.parse(ocs_data);
      this.setState({user:user});
    }
  }

  updateDimensions(){
    const width = window.innerWidth;
    let device;
    if (width >= 910){
      device = "large";
    } else if (width < 910 && width >= 610){
      device = "mid";
    } else if (width < 610){
      device = "tablet";
    }
    this.setState({device:device});
  }

  render(){

    let domainsMenuDisplay;
    if (this.state.device === "tablet"){
      domainsMenuDisplay = (
        <MobileLeftMenu
          device={this.state.device}
          domains={this.state.domains}
          user={this.state.user}
          baseUrl={this.state.baseUrl}
          blogUrl={this.state.blogUrl}
          forumUrl={this.state.forumUrl}
          sName={this.state.sName}
          isAdmin={this.state.isAdmin}
        />
      )
    } else {
      domainsMenuDisplay = (
        <DomainsMenu
          device={this.state.device}
          domains={this.state.domains}
          user={this.state.user}
          baseUrl={this.state.baseUrl}
          blogUrl={this.state.blogUrl}
          forumUrl={this.state.forumUrl}
          sName={this.state.sName}
          isAdmin={this.state.isAdmin}
        />
      )
    }

    return (
      <nav id="metaheader-nav" className="metaheader">
        <div className="metamenu">
          {domainsMenuDisplay}
          <UserMenu
            device={this.state.device}
            user={this.state.user}
            baseUrl={this.state.baseUrl}
            blogUrl={this.state.blogUrl}
            forumUrl={this.state.forumUrl}
            loginUrl={this.state.loginUrl}
            logoutUrl={this.state.logoutUrl}
            gitlabUrl={this.state.gitlabUrl}
          />
        </div>
      </nav>
    )
  }
}

class DomainsMenu extends React.Component {
  constructor(props){
  	super(props);
  	this.state = {
    };
  }

  render(){

    let moreMenuItemDisplay;
    if (this.props.device !== "large"){
      moreMenuItemDisplay = (
        <MoreDropDownMenu
          domains={this.props.domains}
          baseUrl={this.props.baseUrl}
          blogUrl={this.props.blogUrl}
        />
      )
    }

    let adminsDropDownMenuDisplay, myOpendesktopMenuDisplay;
    if (this.props.isAdmin === true){
      adminsDropDownMenuDisplay = (
        <AdminsDropDownMenu
          user={this.props.user}
          baseUrl={this.props.baseUrl}
          gitlabUrl={this.props.gitlabUrl}
        />
      );
      myOpendesktopMenuDisplay = (
        <CloudsServicesDropDownMenu />
      );
    }

    return (
      <ul className="metaheader-menu left" id="domains-menu">
        <li className="active">
          <a href={this.props.baseUrl}>
            <img src={this.props.baseUrl + "/images/system/ocs-logo-rounded-16x16.png"} className="logo"/>
            openDesktop.org :
          </a>
        </li>
        <DomainsDropDownMenu
          domains={this.props.domains}
        />
        {adminsDropDownMenuDisplay}
        {myOpendesktopMenuDisplay}
        <DiscussionBoardsDropDownMenu
          forumUrl={this.props.forumUrl}
        />
        {moreMenuItemDisplay}
      </ul>
    )
  }
}

class DomainsDropDownMenu extends React.Component {
  constructor(props){
  	super(props);
  	this.state = {};
    this.handleClick = this.handleClick.bind(this);
  }

  componentDidMount() {
    let menuGroups = [];
    this.props.domains.forEach(function(domain,index){
      if (menuGroups.indexOf(domain.menugroup) === -1){
        menuGroups.push(domain.menugroup);
      }
    });
    this.setState({menuGroups:menuGroups});
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
        if (e.target.className === "domains-menu-link-item"){
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
      <li ref={node => this.node = node} id="domains-dropdown-menu" className={this.state.dropdownClass}>
        <a className="domains-menu-link-item">Themes & Apps</a>
        <ul className="dropdown-menu dropdown-menu-right">
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
    );
  }
}

class DiscussionBoardsDropDownMenu extends React.Component {
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
        if (e.target.className === "discussion-menu-link-item"){
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
      <li ref={node => this.node = node}  id="discussion-boards" className={this.state.dropdownClass}>

        <a className="discussion-menu-link-item">Discussion Boards</a>
        <ul className="discussion-menu dropdown-menu dropdown-menu-right">
          <li><a href={this.props.forumUrl + "/c/general"}>General</a></li>
          <li><a href={this.props.forumUrl + "/c/themes-and-apps"}>Themes & Apps</a></li>
          <li><a href={this.props.forumUrl + "/c/coding"}>Coding</a></li>
        </ul>
      </li>
    );
  }

}

class AdminsDropDownMenu extends React.Component {
  constructor(props){
  	super(props);
  	this.state = {};
    this.state = {
      gitlabLink:config.gitlabUrl+"/dashboard/issues?assignee_id="
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
    const self = this;
    $.ajax({url: config.gitlabUrl+"/api/v4/users?username="+this.props.user.username,cache: false})
      .done(function(response){
        const gitlabLink = self.state.gitlabLink + response[0].id;
        self.setState({gitlabLink:gitlabLink,loading:false});
    });
  }

  handleClick(e){
    let dropdownClass = "";
    if (this.node.contains(e.target)){
      if (this.state.dropdownClass === "open"){
        if (e.target.className === "admins-menu-link-item"){
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
      <li ref={node => this.node = node} id="admins-dropdown-menu" className={this.state.dropdownClass}>
        <a className="admins-menu-link-item">Development</a>
        <ul className="dropdown-menu dropdown-menu-right">
          <li><a href={config.gitlabUrl+"/dashboard/projects"}>Projects</a></li>
          <li><a href={this.state.gitlabLink}>Issues</a></li>
        </ul>
      </li>
    )
  }
}

class CloudsServicesDropDownMenu extends React.Component {
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
        if (e.target.className === "cd-menu-link-item"){
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

    const urlEnding = config.baseUrl.split('opendesktop.')[1];

    return (
      <li ref={node => this.node = node} id="cd-dropdown-menu" className={this.state.dropdownClass}>
        <a className="cd-menu-link-item">Clouds & Services</a>
        <ul className="dropdown-menu dropdown-menu-right">
          <li><a href={"https://my.opendesktop." + urlEnding}>Storage</a></li>
          <li><a href={"https://music.opendesktop." + urlEnding}>Music</a></li>
          <li><a href={"https://docs.opendesktop." + urlEnding}>Docs</a></li>
        </ul>
      </li>
    )
  }
}

class MoreDropDownMenu extends React.Component {
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
        if (e.target.className === "more-menu-link-item"){
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

    let faqLinkItem, apiLinkItem, aboutLinkItem;
    if (config.isExternal === false){
      faqLinkItem = (<li><a className="popuppanel" id="faq" href={"/plings"}>FAQ</a></li>);
      apiLinkItem = (<li><a className="popuppanel" id="api" href={"/partials/ocsapicontent.phtml"}>API</a></li>);
      aboutLinkItem = (<li><a className="popuppanel" id="about" href={"/partials/about.phtml"}>About</a></li>);
    } else {
      faqLinkItem = (<li><a className="popuppanel" target="_blank" id="faq" href={config.baseUrl + "/#faq"}>FAQ</a></li>);
      apiLinkItem = (<li><a className="popuppanel" target="_blank" id="api" href={config.baseUrl + "/#api"}>API</a></li>);
      aboutLinkItem = (<li><a className="popuppanel" target="_blank" id="about" href={config.baseUrl + "/#about"}>About</a></li>);
    }

    return(
      <li ref={node => this.node = node} id="more-dropdown-menu" className={this.state.dropdownClass}>
        <a className="more-menu-link-item">More</a>
        <ul className="dropdown-menu">
          <li><a href={this.props.baseUrl + "/community"}>Community</a></li>
          <li><a href={this.props.blogUrl} target="_blank">Blog</a></li>
          {faqLinkItem}
          {apiLinkItem}
          {aboutLinkItem}
        </ul>
      </li>
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
        let domainPrefix = "";
        if (domain.menuhref.indexOf('https://') === -1 && domain.menuhref.indexOf('http://') === -1){
          domainPrefix += "http://";
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
          baseUrl={this.props.baseUrl}
        />
      );
      userAppsContextDisplay = (
        <UserContextMenuContainer
          user={this.props.user}
          forumUrl={this.props.forumUrl}
          gitlabUrl={this.props.gitlabUrl}
        />
      )
    } else {
      userDropdownDisplay = (
        <li id="user-login-container"><a href={this.props.loginUrl} className="btn btn-metaheader">Login</a></li>
      )
    }

    let userMenuContainerDisplay;
    if (this.props.device === "large"){

      let faqLinkItem, apiLinkItem, aboutLinkItem;
      if (config.isExternal === false){
        faqLinkItem = (<li><a className="popuppanel" id="faq" href={"/plings"}>FAQ</a></li>);
        apiLinkItem = (<li><a className="popuppanel" id="api" href={"/partials/ocsapicontent.phtml"}>API</a></li>);
        aboutLinkItem = (<li><a className="popuppanel" id="about" href={"/partials/about.phtml"}>About</a></li>);
      } else {
        faqLinkItem = (<li><a className="popuppanel" target="_blank" id="faq" href={config.baseUrl + "/#faq"}>FAQ</a></li>);
        apiLinkItem = (<li><a className="popuppanel" target="_blank" id="api" href={config.baseUrl + "/#api"}>API</a></li>);
        aboutLinkItem = (<li><a className="popuppanel" target="_blank" id="about" href={config.baseUrl + "/#about"}>About</a></li>);
      }

      userMenuContainerDisplay = (
        <ul className="metaheader-menu" id="user-menu">
          <li><a href={this.props.baseUrl + "/community"}>Community</a></li>
          <li><a href={this.props.blogUrl} target="_blank">Blog</a></li>
          {faqLinkItem}
          {apiLinkItem}
          {aboutLinkItem}
          {userAppsContextDisplay}
          {userDropdownDisplay}
        </ul>
      );
    } else {
      userMenuContainerDisplay = (
        <ul className="metaheader-menu" id="user-menu">
          {userAppsContextDisplay}
          {userDropdownDisplay}
        </ul>
      );
    }


    return (
      <div id="user-menu-container" className="right">
        {userMenuContainerDisplay}
      </div>
    )
  }
}

class UserContextMenuContainer extends React.Component {
  constructor(props){
  	super(props);
  	this.state = {
      gitlabLink:config.gitlabUrl+"/dashboard/issues?assignee_id="
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
    const self = this;
    /*$.ajax({url: config.gitlabUrl+"/api/v4/users?username="+this.props.user.username,cache: false})
      .done(function(response){
        const gitlabLink = self.state.gitlabLink + response[0].id;
        self.setState({gitlabLink:gitlabLink,loading:false});
    });*/
    console.log('component did mount');
    var xhttp = new XMLHttpRequest();
    xhttp.onreadystatechange = function() {
      console.log(this);
      /*if (this.readyState == 4 && this.status == 200) {
           // Typical action to be performed when the document is ready:
           document.getElementById("demo").innerHTML = xhttp.responseText;
      }*/
    };

    xhttp.open("GET", config.gitlabUrl+"/api/v4/users?username="+this.props.user.username, true);
    xhttp.send();

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
              <a href={this.props.gitlabUrl+"/dashboard/projects"}>
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
              <a href={this.props.forumUrl+"/u/"+this.props.user.username+"/messages"}>
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
              <a href={this.props.baseUrl + "/settings/"} className="btn btn-default btn-metaheader"><span>Settings</span></a>
              <a href={this.props.logoutUrl} className="btn btn-default pull-right btn-metaheader"><span>Logout</span></a>
            </li>
          </ul>
        </div>
      </li>
    )
  }
}

/** MOBILE SPECIFIC **/

class MobileLeftMenu extends React.Component {
  constructor(props){
  	super(props);
  	this.state = {
      overlayClass:""
    };
    this.toggleLeftSideOverlay = this.toggleLeftSideOverlay.bind(this);
    this.handleClick = this.handleClick.bind(this);
  }

  componentWillMount() {
    window.addEventListener('mousedown',this.handleClick, false);
    window.addEventListener('touchend', this.handleClick, false);
  }

  componentWillUnmount() {
    window.removeEventListener('mousedown',this.handleClick, false);
    window.addEventListener('touchend', this.handleClick, false);
  }

  toggleLeftSideOverlay(){
    let overlayClass = "open";
    if (this.state.overlayClass === "open") {
      overlayClass = "";
    }
    this.setState({overlayClass:overlayClass});
  }

  handleClick(e){
    let overlayClass = "";
    if (this.node.contains(e.target)){
      if (this.state.overlayClass === "open"){
        if (e.target.id === "left-side-overlay" || e.target.id === "menu-toggle-item"){
          overlayClass = "";
        } else {
          overlayClass = "open";
        }
      } else {
        overlayClass = "open";
      }
    }
    this.setState({overlayClass:overlayClass});
  }

  render(){
    return (
      <div ref={node => this.node = node}  id="metaheader-left-mobile" className={this.state.overlayClass}>
        <a className="menu-toggle" id="menu-toggle-item"></a>
        <div id="left-side-overlay">
          <MobileLeftSidePanel
            baseUrl={this.props.baseUrl}
            domains={this.props.domains}
            baseUrl={this.props.baseUrl}
            blogUrl={this.props.blogUrl}
            forumUrl={this.props.forumUrl}
          />
        </div>
      </div>
    );
  }
}

class MobileLeftSidePanel extends React.Component {
  constructor(props){
  	super(props);
  	this.state = {};
  }

  componentDidMount() {
    let menuGroups = [];
    this.props.domains.forEach(function(domain,index){
      if (menuGroups.indexOf(domain.menugroup) === -1){
        menuGroups.push(domain.menugroup);
      }
    });
    this.setState({menuGroups:menuGroups});
  }

  render(){
    let panelMenuGroupsDisplay;
    if (this.state.menuGroups){
      panelMenuGroupsDisplay = this.state.menuGroups.map((mg,i) => (
        <DomainsMenuGroup
          key={i}
          domains={this.props.domains}
          menuGroup={mg}
          sName={this.props.sName}
        />
      ));
    }

    let faqLinkItem, apiLinkItem, aboutLinkItem;
    if (config.isExternal === false){
      faqLinkItem = (<li><a className="popuppanel" id="faq" href={"/plings"}>FAQ</a></li>);
      apiLinkItem = (<li><a className="popuppanel" id="api" href={"/partials/ocsapicontent.phtml"}>API</a></li>);
      aboutLinkItem = (<li><a className="popuppanel" id="about" href={"/partials/about.phtml"}>About</a></li>);
    } else {
      faqLinkItem = (<li><a className="popuppanel" target="_blank" id="faq" href={config.baseUrl + "/#faq"}>FAQ</a></li>);
      apiLinkItem = (<li><a className="popuppanel" target="_blank" id="api" href={config.baseUrl + "/#api"}>API</a></li>);
      aboutLinkItem = (<li><a className="popuppanel" target="_blank" id="about" href={config.baseUrl + "/#about"}>About</a></li>);
    }

    return (
      <div id="left-side-panel">
        <div id="panel-header">
          <a href={this.props.baseUrl}>
            <img src={this.props.baseUrl + "/images/system/opendesktop-logo.png"} className="logo"/> openDesktop.org
          </a>
        </div>
        <div id="panel-menu">
          <ul>
            {panelMenuGroupsDisplay}
            <li>
              <a className="groupname"><b>Discussion Boards</b></a>
              <ul>
                <li><a href={this.props.forumUrl + "/c/general"}>General</a></li>
                <li><a href={this.props.forumUrl + "/c/themes-and-apps"}>Themes & Apps</a></li>
                <li><a href={this.props.forumUrl + "/c/coding"}>Coding</a></li>
              </ul>
            </li>
            <li>
              <a className="groupname"><b>More</b></a>
              <ul>
                <li><a href={this.props.baseUrl + "/community"}>Community</a></li>
                <li><a href={this.props.blogUrl} target="_blank">Blog</a></li>
                {faqLinkItem}
                {apiLinkItem}
                {aboutLinkItem}
              </ul>
            </li>
          </ul>
        </div>
      </div>
    )
  }
}

customElements.define('opendesktop-metaheader', class extends HTMLElement {
  constructor() {
    super();
    this.buildComponent();
  }

  async buildComponent() {
    await initConfig(this.getAttribute('config-target'));

    const metaheaderElement = document.createElement('div');
    metaheaderElement.id = 'metaheader';
    ReactDOM.render(React.createElement(MetaHeader, null), metaheaderElement);

    const stylesheetElement = document.createElement('link');
    stylesheetElement.rel = 'stylesheet';
    if (location.hostname.endsWith('opendesktop.org')) {
      stylesheetElement.href = 'https://www.opendesktop.org/theme/react/assets/css/metaheader.css';
    }
    else if (location.hostname.endsWith('opendesktop.cc')) {
      stylesheetElement.href = 'https://www.opendesktop.cc/theme/react/assets/css/metaheader.css';
    }
    else if (location.hostname.endsWith('localhost')) {
      stylesheetElement.href = 'https://www.opendesktop.cc/theme/react/assets/css/metaheader.css';
    }else{
       stylesheetElement.href = 'https://www.opendesktop.org/theme/react/assets/css/metaheader.css';
    }

    // Component must be capsule within Shadow DOM, and don't hack
    // context/scope of external sites.
    /*
    this.attachShadow({mode: 'open'});
    this.shadowRoot.appendChild(stylesheetElement);
    this.shadowRoot.appendChild(metaheaderElement);
    */

    // However, make this as Light DOM for now, because current
    // implementation is not real component design yet.
    // Need solve event handling, scoped CSS.
    this.appendChild(stylesheetElement);
    this.appendChild(metaheaderElement);
  }
});
