class MetaHeader extends React.Component {
  constructor(props){
  	super(props);
  	this.state = {
      baseUrl:baseUrl,
      blogUrl:blogUrl,
      loginUrl:loginUrl,
      domains:domains,
      user:user,
      sName:sName,
      loading:false
    };
  }

  componentDidMount() {
    console.log(this.state);
  }

  render(){

    return (
      <nav id="metaheader-nav" className="metaheader">
        <div className="metamenu">
          <DomainsMenu
            domains={this.state.domains}
            baseUrl={this.state.baseUrl}
            sName={this.state.sName}
          />
          <UserMenu
            user={this.state.user}
            blogUrl={this.state.blogUrl}
            loginUrl={this.state.loginUrl}
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
    let menuGroupsDisplay;
    if (this.state.menuGroups){
      menuGroupsDisplay = this.state.menuGroups.map((mg,i) => (
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
            <img src="/images/system/ocs-logo-rounded-16x16.png" className="logo"/>
            openDesktop.org :
          </a>
        </li>
        <li id="domains-dropdown-menu">
          <a id="dropdownMenu3"
          data-toggle="dropdown"
          aria-haspopup="true"
          aria-expanded="true">Themes & Apps</a>
          <ul className="dropdown-menu dropdown-menu-right" aria-labelledby="dropdownMenu3">
            {menuGroupsDisplay}
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
    const domainsDisplay = this.props.domains.filter(this.filterDomainsByMenuGroup).map((domain,index) => (
      <li key={index}>
        <a href={"http://" + domain.menuhref}>{domain.name}</a>
      </li>
    ));

    return (
      <li>
        <a href="#"><b>{this.props.menuGroup}</b></a>
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
    let userDropdownDisplay;
    if (this.props.user){
      userDropdownDisplay = (
        <UserLoginMenuContainer
          user={this.props.user}
        />
      );
    } else {
      userDropdownDisplay = (
        <li id="user-login-container"><a href={this.props.loginUrl} className="btn btn-metaheader">Login</a></li>
      )
    }

    return (
      <div id="user-menu-container" className="right">
        <ul className="metaheader-menu" id="user-menu">
          <li><a href="/community">Community</a></li>
          <li><a href={this.props.blogUrl} target="_blank">Blog</a></li>
          <li><a id="plingList" className="popuppanel" href="/plings">What are Plings?</a></li>
          <li><a id="ocsapiContent" className="popuppanel" href="/partials/ocsapicontent.phtml">API</a></li>
          <li><a id="aboutContent" className="popuppanel" href="/partials/about.phtml" >About</a></li>
          <UserContextMenuContainer/>
          {userDropdownDisplay}
        </ul>
      </div>
    )
  }
}

class UserContextMenuContainer extends React.Component {
  constructor(props){
  	super(props);
  	this.state = {};
  }

  render(){
    return (
      <li id="user-context-menu-container">
        <div className="user-dropdown">
          <button
            className="btn btn-default dropdown-toggle"
            type="button"
            id="dropdownMenu2"
            data-toggle="dropdown"
            aria-haspopup="true"
            aria-expanded="true">
            <span className="glyphicon glyphicon-th"></span>
          </button>
          <ul className="dropdown-menu dropdown-menu-right" aria-labelledby="dropdownMenu2">
            <li id="opendesktop-link-item">
              <a href="http://www.opendesktop.org">
                <div className="icon"></div>
                <span>Themes <br/> & Apps</span>
              </a>
            </li>
            <li id="discourse-link-item">
              <a href="http://discourse.opendesktop.org/">
                <div className="icon"></div>
                <span>Discussion <br/> Boards</span>
              </a>
            </li>
            <li id="opencode-link-item">
              <a href="https://www.opencode.net/">
                <div className="icon"></div>
                <span>Coding <br/> Tools</span>
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
            <img src={this.props.user.profile_image_url}/>
          </button>
          <ul className="dropdown-menu dropdown-menu-right" aria-labelledby="userLoginDropdown">
            <li id="user-info-menu-item">
              <div id="user-info-section">
                <div className="user-avatar">
                  <div className="no-avatar-user-letter">
                    <img src={this.props.user.profile_image_url}/>
                    <a className="change-profile-pic">
                      Change
                    </a>
                  </div>
                </div>
                <div className="user-details">
                  <ul>
                    <li><b>{this.props.user.username}</b></li>
                    <li>{this.props.user.mail}</li>
                    <li></li>
                    <li><a>Profile</a> - <a>Privacy</a></li>
                    <li><button className="btn btn-default btn-metaheader">Account</button></li>
                  </ul>
                </div>
              </div>
            </li>
            <li id="main-seperator" role="separator" className="divider"></li>
            <li className="buttons">
              <button className="btn btn-default btn-metaheader">Add Account</button>
              <button className="btn btn-default pull-right btn-metaheader"><a href="/register">Sign Up</a></button>
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
