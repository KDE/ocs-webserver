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
          user={this.state.user}
        />
      );
    } else {
      loginMenuDisplay = (
        <SiteHeaderLoginMenu />
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
    this.onSearchText = this.onSearchText.bind(this);
  }

  onSearchText(){
    console.log(this.state.searchText);
  }

  render(){
    return (
      <div id="site-header-search-form">
        <div id="search-form">
          <input value={this.state.searchText} type="text" name="projectSearchText" />
          <a onClick={this.onSearchText}></a>
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
    return (
      <div id="site-header-login-menu">
        login menu
      </div>
    )
  }
}

class SiteHeaderUserMenu extends React.Component {
  constructor(props){
  	super(props);
  	this.state = {};
  }
  render(){
    return (
      <div id="site-header-user-menu-container">
        user menu container
      </div>
    )
  }
}

ReactDOM.render(
    <SiteHeader />,
    document.getElementById('site-header-container')
);
