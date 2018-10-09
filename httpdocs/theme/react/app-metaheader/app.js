class MetaHeader extends React.Component {
  constructor(props){
  	super(props);
  	this.state = {
      baseUrl:"https://wwww.opendesktop.cc",
      blogUrl:"https://blog.opendesktop.org",
      loginUrl:"https://www.opendesktop.cc/login/redirect/TFVIFZfgicowyCW5clpDz3sfM1rVUJsb_GwOHCL1oRyPOkMMVswIRPd2kvVz5oQW",
      user:user,
      sName:sName,
      loading:false
    };
    this.getUser = this.getUser.bind(this);
    this.getUrls = this.getUrls.bind(this);
  }

  componentDidMount() {
    this.getUser();
    this.getUrls();
  }

  getUser(){
    console.log('get user');
    const self = this;
    $.ajax({
      url:'https://www.opendesktop.cc/user/userdataajax',
      method:'get',
      dataType: 'jsonp',
      done: function(response){
        console.log('done');
        console.log(response);
      },
      error: function(response){
        console.log('error');
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
    console.log('get forum');
    $.ajax({
      url:'https://www.opendesktop.cc/home/forumurlajax',
      method:'get',
      dataType: 'jsonp',
      error: function(response){
        console.log('error');
        console.log(response);
        const res = JSON.parse(response.responseText);
        if (res.status === "success"){
          self.setState({forumUrl:res.data.url_forum});
        }
        console.log('get blog');
        $.ajax({
          url:'https://www.opendesktop.cc/home/blogurlajax',
          method:'get',
          dataType: 'jsonp',
          error: function(response){
            console.log('error');
            console.log(response);
            const res = JSON.parse(response.responseText);
            if (res.status === "success"){
              self.setState({blogUrl:res.data.url_blog});
            }
            console.log('get base')
            $.ajax({
              url:'https://www.opendesktop.cc/home/baseurlajax',
              method:'get',
              dataType: 'jsonp',
              error: function(response){
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
          }
        });
      }
    });
  }

  render(){
    let metaMenuDisplay;
    if (!this.state.loading){
      metaMenuDisplay = (
        <div className="metamenu">
          <DomainsMenu
            domains={appHelpers.getDomainsArray()}
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
    console.log(this.state);
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
          <a href={"http://"+this.props.baseUrl}>
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

    return (
      <div id="user-menu-container" className="right">
        <ul className="metaheader-menu" id="user-menu">
          <li><a href="https://www.opendesktop.cc/community">Community</a></li>
          <li><a href={this.props.blogUrl} target="_blank">Blog</a></li>
          <li><a id="plingList" className="popuppanel" href="https://www.opendesktop.cc/plings">What are Plings?</a></li>
          <li><a id="ocsapiContent" className="popuppanel" href="https://www.opendesktop.cc/partials/ocsapicontent.phtml">API</a></li>
          <li><a id="aboutContent" className="popuppanel" href="https://www.opendesktop.cc/partials/about.phtml" >About</a></li>
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
            <span className="glyphicon glyphicon-th"></span>
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
