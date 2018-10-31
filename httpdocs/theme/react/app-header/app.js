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

  componentDidMount() {
    console.log(this.state);
  }

  render(){

    let userMenuDisplay, loginMenuDisplay;
    if (this.state.user){
      userMenuDisplay = (
        <SiteHeaderUserMenu
          baseUrl={this.state.baseUrl}
          user={this.state.user}
        />
      );
    } else {
      loginMenuDisplay = (
        <SiteHeaderLoginMenu
          baseUrl={this.state.baseUrl}
          redirectString={this.state.redirectString}
        />
      );
    }

    let siteHeaderStoreNameDisplay;
    if (this.state.is_show_title === "1"){
      siteHeaderStoreNameDisplay = (
        <div id="site-header-store-name-container">
          <a href={this.state.serverUrl + this.state.serverUri}>
            {this.state.store.name}
          </a>
        </div>
      );
    }

    return (
      <section id="site-header" style={this.state.template.header}>
        <section id="site-header-wrapper" style={{"paddingLeft":this.state.template['header-logo']['width']}}>
          <div id="site-header-logo-container" style={this.state.template['header-logo']}>
            <a href={this.state.serverUrl + this.state.serverUri}>
              <img src={this.state.template['header-logo']['image-src']}/>
            </a>
          </div>
          {siteHeaderStoreNameDisplay}
          <div id="site-header-right">
            <div id="site-header-right-top">
              <SiteHeaderSearchForm />
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

  onSearchFormSubmit(){
    console.log(this.state.searchText);
  }

  render(){
    return (
      <div id="site-header-search-form">
        <div id="search-form">
          <input onChange={this.onSearchTextChange} value={this.state.searchText} type="text" name="projectSearchText" />
          <a onClick={this.onSearchFormSubmit}></a>
        </div>
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

    return (
      <div id="site-header-login-menu">
        <ul>
          <li className={registerButtonCssClass}><a href={this.props.baseUrl + "/register"}>Register</a></li>
          <li className={loginButtonCssClass}><a href={this.props.baseUrl + "/login" + this.props.redirectString}>Login</a></li>
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

  handleClick(e){
    let dropdownClass = "";
    if (this.node.contains(e.target)){
      if (this.state.dropdownClass === "open"){
        if (e.target.className === "profile-menu-toggle"){
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

    let imageBaseUrl;
    const env = appHelpers.getEnv(window.location.href);
    if (env === "live"){
      imageBaseUrl = "https://cn.pling.com/cache/200x200-2/img/";
    } else {
      imageBaseUrl = "https://cn.pling.it/cache/200x200-2/img/";
    }

    return (
      <ul ref={node => this.node = node} id="site-header-user-menu-container">
        <li id="user-menu-toggle">
          <a className="profile-menu-toggle">
            <img src={imageBaseUrl + this.props.user.avatar}/>
            <span>{this.props.user.username}</span>
          </a>
          <ul id="user-profile-menu" className={this.state.dropdownClass}>
            <li><a href="/product/add">Add Product</a></li>
            <li><a href={this.props.baseUrl + "/u/" + this.props.user.username + "/products"}></a></li>
            <li><a href={this.props.baseUrl + "/u/" + this.props.user.username + "/plings"}></a></li>
            <li><a href="/settings"></a></li>
            <li><a href="/logout"></a></li>
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
