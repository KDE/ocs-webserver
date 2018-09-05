class MetaHeader extends React.Component {
  constructor(props){
  	super(props);
  	this.state = {loading:true};
  }

  componentDidMount() {
    this.setState({
      baseUrl:baseUrl,
      blogUrl:blogUrl,
      domains:domains,
      loading:false
    });
  }

  render(){
    let navDisplay;
    if (!this.state.loading){
      navDisplay = (
        <div className="metamenu">
          <DomainsMenu
            domains={this.state.domains}
            baseUrl={this.state.baseUrl}
          />
          <UserMenu
            user={this.state.user}
            blogUrl={this.state.blogUrl}
          />
        </div>
      );
    }
    return (
      <nav id="metaheader-nav" className="metaheader">
        {navDisplay}
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
        {menuGroupsDisplay}
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
        <a href={domain.menuhref}>{domain.name}</a>
      </li>
    ));

    return (
      <li className="dropdown">
        <a href="#">{this.props.menuGroup}</a>
        <ul className="dropdown-menu">
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
        <li>User</li>
      );
    } else {
      userDropdownDisplay = (
        <li id="user-login-container"><a href="/login" className="btn btn-metaheader">Login</a></li>
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
                <span>Themes & Apps</span>
              </a>
            </li>
            <li id="discourse-link-item">
              <a href="http://discourse.opendesktop.org/">
                <div className="icon"></div>
                <span>Discussion Boards</span>
              </a>
            </li>
            <li id="opencode-link-item">
              <a href="https://www.opencode.net/">
                <div className="icon"></div>
                <span>Coding Tools</span>
              </a>
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
