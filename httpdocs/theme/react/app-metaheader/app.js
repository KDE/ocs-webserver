class MetaHeader extends React.Component {
  constructor(props){
  	super(props);
  	this.state = {
      baseUrl:"https://www.opendesktop.cc",
      blogUrl:"https://blog.opendesktop.org",
      loginUrl:"https://www.opendesktop.cc/login/redirect/TFVIFZfgicowyCW5clpDz3sfM1rVUJsb_GwOHCL1oRyPOkMMVswIRPd2kvVz5oQW",
      user:user,
      sName:sName,
      loading:false
    };
    this.getUser = this.getUser.bind(this);
    this.getDomains = this.getDomains.bind(this);
    this.getUrls = this.getUrls.bind(this);
  }

  componentDidMount() {
    console.log('component did mount');
    this.getUser();
    this.getDomains();
    this.getUrls();
  }

  getUser(){
    console.log('get user');
    const userQueryUrl = appHelpers.getUserQueryUrl(window.location.hostname);
    console.log(userQueryUrl);
    const self = this;
    $.ajax({
      url:userQueryUrl,
      method:'get',
      dataType: 'jsonp',
      done: function(response){
        console.log('done');
        console.log(response);
      },
      error: function(response){
        console.log('get user');
        console.log(response)
        const res = JSON.parse(response.responseText);
        if (res.status === "success"){
          self.setState({user:res.data,loading:false});
        } else {
          self.setState({loading:false});
        }
      },
      success: function(response){
        console.log('success');
        console.log(response);
      }
    });
  }

  getUrls(){
    const self = this;

    const forumQueryUrl = appHelpers.getForumQueryUrl(window.location.hostname);
    $.ajax({
      url:forumQueryUrl,
      method:'get',
      dataType: 'jsonp',
      error: function(response){
        console.log('get forum');
        console.log(response);
        const res = JSON.parse(response.responseText);
        if (res.status === "success"){
          self.setState({forumUrl:res.data.url_forum});
        }
      }
    });

    const blogQueryUrl = appHelpers.getBlogQueryUrl(window.location.hostname);
    $.ajax({
      url:blogQueryUrl,
      method:'get',
      dataType: 'jsonp',
      error: function(response){
        console.log('get blog');
        console.log(response);
        const res = JSON.parse(response.responseText);
        if (res.status === "success"){
          self.setState({blogUrl:res.data.url_blog});
        }
      }
    });

    const baseQueryUrl = appHelpers.getBaseQueryUrl(window.location.hostname);
    $.ajax({
      url:baseQueryUrl,
      method:'get',
      dataType: 'jsonp',
      error: function(response){
        console.log('get base')
        console.log('error');
        console.log(response);
        const res = JSON.parse(response.responseText);
        if (res.status === "success"){
          let baseUrl = res.data.base_url;
          if (res.data.base_url.indexOf('http') === -1){
            baseUrl = "http://" + res.data.base_url;
          }
          self.setState({baseUrl:baseUrl});
        }
      }
    });

    const storeQueryUrl = appHelpers.getStoreQueryUrl(window.location.hostname);
    $.ajax({
      url:storeQueryUrl,
      method:'get',
      dataType: 'jsonp',
      error: function(response){
        console.log('get store')
        console.log('error');
        console.log(response);
        const res = JSON.parse(response.responseText);
        if (res.status === "success"){
          self.setState({sName:res.data.store_name});
        }
      }
    });

  }

  getDomains(){
    const self = this;
    const domainsQueryUrl = appHelpers.getDomainsQueryUrl(window.location.hostname);
    $.ajax({
      url:domainsQueryUrl,
      method:'get',
      dataType: 'jsonp',
      error: function(response){
        console.log('get domains');
        console.log(response);
        const res = JSON.parse(response.responseText);
        if (res.status === "success"){
          self.setState({domains:res.data});
        }
      }
    });

  }

  render(){
    let metaMenuDisplay;
    if (!this.state.loading){
      let domains = this.state.domains;
      if (!this.state.doamins) {
        domains = appHelpers.getDomainsArray();
      }
      metaMenuDisplay = (
        <div className="metamenu">
          <DomainsMenu
            domains={domains}
            baseUrl={this.state.baseUrl}
            sName={this.state.sName}
          />
          <UserMenu
            user={this.state.user}
            blogUrl={this.state.blogUrl}
            loginUrl={this.state.loginUrl}
          />
        </div>
      );
    }

    return (
      <nav id="metaheader-nav" className="metaheader">
        {metaMenuDisplay}
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
    if (this.props.user.member_id){
      userDropdownDisplay = (
        <UserLoginMenuContainer
          user={this.props.user}
        />
      );
      userAppsContextDisplay = (
        <UserContextMenuContainer
          user={this.props.user}
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
      loading:true,
      ariaExpanded:false
    };
    this.toggleDropdown = this.toggleDropdown.bind(this);
  }

  componentWillMount() {

    window.MutationObserver = window.MutationObserver || window.WebKitMutationObserver || window.MozMutationObserver;
    // Find the element that you want to "watch"
    var target = document.getElementById('#user-context-menu-container'),
    // create an observer instance
    observer = new MutationObserver(function(mutation) {
      console.log('hi');
      console.log(mutation);
     /** this is the callback where you
         do what you need to do.
         The argument is an array of MutationRecords where the affected attribute is
         named "attributeName". There is a few other properties in a record
         but I'll let you work it out yourself.
      **/
    }),
    // configuration of the observer:
    config = {
        attributes: true // this is to watch for attribute changes.
    };
    // pass in the element you wanna watch as well as the options
    observer.observe(target, config);
    // later, you can stop observing
    // observer.disconnect();

  }

  componentDidMount() {
    const self = this;
    $.ajax({url: "https://gitlab.opencode.net/api/v4/users?username="+this.props.user.username,cache: false}).done(function(response){
      const gitlabLink = "https://gitlab.opencode.net/dashboard/issues?assignee_id="+response[0].id;
      self.setState({gitlabLink:gitlabLink,loading:false});
    });
  }

  toggleDropdown(e){
    const ariaExpanded = this.state.ariaExpanded === true ? false : true;
    this.setState({ariaExpanded:ariaExpanded});
  }

  render(){

    const messagesLink = "https://forum.opendesktop.org/u/"+this.props.user.username+"/messages";

    return (
      <li id="user-context-menu-container">
        <div className="user-dropdown">
          <button
            className="btn btn-default dropdown-toggle"
            type="button"
            id="dropdownMenu2"
            data-toggle="dropdown"
            aria-haspopup="true"
            aria-expanded={this.state.ariaExpanded}
            onClick={this.toggleDropdown}>
            <span className="th-icon"></span>
          </button>
          <ul className="dropdown-menu dropdown-menu-right" aria-labelledby="dropdownMenu2">
            <li id="opencode-link-item">
              <a href="https://gitlab.opencode.net/dashboard/projects">
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
              <a href={messagesLink}>
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
  }

  render(){
    return (
      <li id="user-login-menu-container">
        <div className="user-dropdown">
          <button
            className="btn btn-default dropdown-toggle"
            type="button"
            id="userLoginDropdown"
            data-toggle="dropdown"
            aria-haspopup="true"
            aria-expanded="true">
            <img src={this.props.user.avatar}/>
          </button>
          <ul className="dropdown-menu dropdown-menu-right" aria-labelledby="userLoginDropdown">
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
            <li id="main-seperator" role="separator" className="divider"></li>
            <li className="buttons">
              <a href="https://www.opendesktop.cc/settings/" className="btn btn-default btn-metaheader">Settings</a>
              <a href="https://www.opendesktop.cc/logout/" className="btn btn-default pull-right btn-metaheader">Logout</a>
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
