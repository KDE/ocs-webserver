class SiteHeader extends React.Component {
  constructor(props){
  	super(props);
  	this.state = {
      baseUrl:window.json_baseurl,
      cat_title:window.json_cat_title,
      hasIdentity:window.json_hasIdentity,
      is_show_title:window.json_is_show_title,
      redirectString:window.json_redirectString,
      serverUrl:window.json_serverUrl,
      serverUri:window.json_serverUri,
      store:{
        sName:window.json_sname,
        name:window.json_store_name,
        order:window.json_store_order,
        last_char_store_order:window.last_char_store_order,
      },
      user:window.json_member,
      logo:window.json_logoWidth,
      cat_title_left:window.json_cat_title_left,
      tabs_left:window.tabs_left,
      template:window.json_template,
      status:""
    };
    this.updateDimensions = this.updateDimensions.bind(this);
  }

  componentWillMount() {
    this.updateDimensions();
  }

  componentDidMount() {
    window.addEventListener("resize", this.updateDimensions);
    window.addEventListener("orientationchange",this.updateDimensions);
  }

  componentWillUnmount(){
    window.removeEventListener("resize", this.updateDimensions);
    window.removeEventListener("orientationchange",this.updateDimensions);
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

    let userMenuDisplay, loginMenuDisplay, siteHeaderTopRightCssClass;
    if (this.state.user){
      userMenuDisplay = (
        <SiteHeaderUserMenu
          serverUrl={this.state.serverUrl}
          baseUrl={this.state.baseUrl}
          user={this.state.user}
        />
      );
      siteHeaderTopRightCssClass = "w-user";
    } else {
      loginMenuDisplay = (
        <SiteHeaderLoginMenu
          baseUrl={this.state.baseUrl}
          redirectString={this.state.redirectString}
          template={this.state.template}
        />
      );
    }

    let logoLink = this.state.serverUrl;
    if (this.state.serverUri.indexOf('/s/') > -1){
      logoLink += "/s/" + this.state.store.name;
    }

    let siteHeaderStoreNameDisplay;
    if (this.state.is_show_title === "1"){
      siteHeaderStoreNameDisplay = (
        <div id="site-header-store-name-container">
          <a href={logoLink}>
            {this.state.store.name}
          </a>
        </div>
      );
    }

    let HeaderDisplay;
    if (this.state.device !== "tablet"){
      HeaderDisplay = (
        <section id="site-header-wrapper" style={{"paddingLeft":this.state.template['header-logo']['width']}}>
          <div id="siter-header-left">
            <div id="site-header-logo-container" style={this.state.template['header-logo']}>
              <a href={logoLink}>
                <img src={this.state.template['header-logo']['image-src']}/>
              </a>
            </div>
            {siteHeaderStoreNameDisplay}
          </div>
          <div id="site-header-right">
            <div id="site-header-right-top" className={siteHeaderTopRightCssClass}>
              <SiteHeaderSearchForm
                baseUrl={this.state.baseUrl}
              />
              {userMenuDisplay}
            </div>
            <div id="site-header-right-bottom">
              {loginMenuDisplay}
            </div>
          </div>
        </section>
      );
    } else {
      HeaderDisplay = (
        <MobileSiteHeader
          logoLink={logoLink}
          template={this.state.template}
          user={this.state.user}
          baseUrl={this.state.baseUrl}
          store={this.state.store}
        />
      )
    }

    return (
      <section id="site-header" style={this.state.template.header}>
        {HeaderDisplay}
      </section>
    )
  }
}

class SiteHeaderSearchForm extends React.Component {
  constructor(props){
  	super(props);
  	this.state = {
      searchText:''
    };
    this.onSearchTextChange = this.onSearchTextChange.bind(this);
    this.onSearchFormSubmit = this.onSearchFormSubmit.bind(this);
  }

  onSearchTextChange(e){
    this.setState({searchText:e.target.value});
  }

  onSearchFormSubmit(e){
    e.preventDefault();
    window.location.href = this.props.baseUrl + "/search?projectSearchText=" + this.state.searchText;
  }

  render(){
    return (
      <div id="site-header-search-form">
        <form id="search-form" onSubmit={this.onSearchFormSubmit}>
          <input onChange={this.onSearchTextChange} value={this.state.searchText} type="text" name="projectSearchText" />
          <a onClick={this.onSearchFormSubmit}></a>
        </form>
      </div>
    )
  }
}

class SiteHeaderLoginMenu extends React.Component {
  constructor(props){
  	super(props);
  	this.state = {};
  }
  render(){

    let registerButtonCssClass,
        loginButtonCssClass;

    if (window.location.href.indexOf('/register') > -1){
      registerButtonCssClass = "active";
    }

    if (window.location.href.indexOf('/login') > -1){
      loginButtonCssClass = "active";
    }

    const menuItemCssClass = {
      "borderColor":this.props.template['header-nav-tabs']['border-color'],
      "backgroundColor":this.props.template['header-nav-tabs']['background-color']
    }

    return (
      <div id="site-header-login-menu">
        <ul>
          <li style={menuItemCssClass} className={registerButtonCssClass}><a href={this.props.baseUrl + "/register"}>Register</a></li>
          <li style={menuItemCssClass} className={loginButtonCssClass}><a href={this.props.baseUrl + "/login" + this.props.redirectString}>Login</a></li>
        </ul>
      </div>
    )
  }
}

class SiteHeaderUserMenu extends React.Component {
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
        if (e.target.className === "profile-menu-toggle" ||
            e.target.className === "profile-menu-image" || 
            e.target.className === "profile-menu-username"){
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
      <ul id="site-header-user-menu-container">
        <li ref={node => this.node = node} id="user-menu-toggle" className={this.state.dropdownClass}>
          <a className="profile-menu-toggle">
            <img className="profile-menu-image" src={window.json_member_avatar}/>
            <span className="profile-menu-username">{this.props.user.username}</span>
          </a>
          <ul id="user-profile-menu" >
            <div className="dropdown-header"></div>
            <li><a href="/product/add">Add Product</a></li>
            <li><a href={this.props.baseUrl + "/u/" + this.props.user.username + "/products"}>Products</a></li>
            <li><a href={this.props.baseUrl + "/u/" + this.props.user.username + "/plings"}>Plings</a></li>
            <li><a href="/settings">Settings</a></li>
            <li><a href="/logout">Logout</a></li>
          </ul>
        </li>
      </ul>
    )
  }
}

class MobileSiteHeader extends React.Component {
  constructor(props){
  	super(props);
  	this.state = {
      status:"switch"
    };
    this.showMobileUserMenu = this.showMobileUserMenu.bind(this);
    this.showMobileSearchForm = this.showMobileSearchForm.bind(this);
    this.showMobileSwitchMenu = this.showMobileSwitchMenu.bind(this);
  }

  showMobileUserMenu(){
    this.setState({status:"user"});
  }

  showMobileSearchForm(){
    this.setState({status:"search"});
  }

  showMobileSwitchMenu(){
    this.setState({status:"switch"});
  }

  render(){

    const menuItemCssClass = {
      "borderColor":this.props.template['header-nav-tabs']['border-color'],
      "backgroundColor":this.props.template['header-nav-tabs']['background-color']
    }

    const closeMenuElementDisplay = (
      <a className="menu-item" style={menuItemCssClass} onClick={this.showMobileSwitchMenu}>
        <span className="glyphicon glyphicon-remove"></span>
      </a>
    );

    let mobileMenuDisplay;
    if (this.state.status === "switch"){
      mobileMenuDisplay = (
        <div id="switch-menu">
          <a className="menu-item" style={menuItemCssClass} onClick={this.showMobileSearchForm} id="user-menu-switch">
            <span className="glyphicon glyphicon-search"></span>
          </a>
          <a className="menu-item" style={menuItemCssClass} onClick={this.showMobileUserMenu} id="search-menu-switch">
            <span className="glyphicon glyphicon-option-horizontal"></span>
          </a>
        </div>
      );
    } else if (this.state.status === "user"){
      mobileMenuDisplay = (
        <div id="mobile-user-menu">
          <div className="menu-content-wrapper">
            <MobileUserContainer
              user={this.props.user}
              baseUrl={this.props.baseUrl}              
            />
          </div>
          {closeMenuElementDisplay}
        </div>
      )
    } else if (this.state.status === "search"){
      mobileMenuDisplay = (
        <div id="mobile-search-menu">
          <div className="menu-content-wrapper">
            <SiteHeaderSearchForm
              baseUrl={this.props.baseUrl}
            />
          </div>
          {closeMenuElementDisplay}
        </div>
      )
    }

    let logoElementCssClass;
    if (this.state.status !== "switch"){
      logoElementCssClass = "mini-version " + this.props.store.name;
    }

    return(
      <section id="mobile-site-header">
        <div id="mobile-site-header-logo" className={logoElementCssClass}>
          <a href={this.props.logoLink}>
            <img src={this.props.template['header-logo']['image-src']}/>
          </a>
        </div>
        <div id="mobile-site-header-menus-container">
          {mobileMenuDisplay}
        </div>
      </section>
    );
  }
}

class MobileUserContainer extends React.Component {
  constructor(props){
  	super(props);
  	this.state = {};
  }

  render(){

    let userDisplay;
    if (this.props.user){
      userDisplay = <p>user</p>
    } else {
      userDisplay = (
        <SiteHeaderLoginMenu
          baseUrl={this.props.baseUrl}
        />
      );
    }

    return (
      <div id="mobile-user-container">
        {userDisplay}
      </div>
    )
  }
}

ReactDOM.render(
    <SiteHeader />,
    document.getElementById('site-header-container')
);
