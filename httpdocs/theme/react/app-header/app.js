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
      template:window.json_template
    };
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

    return (
      <section id="site-header" style={this.state.template.header}>
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

ReactDOM.render(
    <SiteHeader />,
    document.getElementById('site-header-container')
);
