class SiteHeader extends React.Component {
  constructor(props) {
    super(props);
    this.state = {
      baseUrl: window.json_baseurl,
      cat_title: window.json_cat_title,
      hasIdentity: window.json_hasIdentity,
      is_show_title: window.json_is_show_title,
      redirectString: window.json_redirectString,
      serverUrl: window.json_serverUrl,
      serverUri: window.json_serverUri,
      store: {
        sName: window.json_sname,
        name: window.json_store_name,
        order: window.json_store_order,
        last_char_store_order: window.last_char_store_order
      },
      user: window.json_member,
      logo: window.json_logoWidth,
      cat_title_left: window.json_cat_title_left,
      tabs_left: window.tabs_left,
      template: window.json_template
    };
  }

  componentDidMount() {
    console.log(this.state);
  }

  render() {

    let userMenuDisplay, loginMenuDisplay;
    if (this.state.user) {
      userMenuDisplay = React.createElement(SiteHeaderUserMenu, {
        user: this.state.user
      });
    } else {
      loginMenuDisplay = React.createElement(SiteHeaderLoginMenu, null);
    }

    return React.createElement(
      "section",
      { id: "site-header", style: this.state.template.header },
      React.createElement(
        "div",
        { id: "site-header-logo-container", style: this.state.template.header - logo },
        React.createElement(
          "a",
          { href: this.state.serverUrl + this.state.serverUri },
          React.createElement("img", { src: this.state.template.logo })
        )
      ),
      React.createElement(
        "div",
        { id: "site-header-store-name-container" },
        React.createElement(
          "a",
          { href: this.state.serverUrl + this.state.serverUri },
          this.state.store.name
        )
      ),
      React.createElement(
        "div",
        { id: "site-header-right" },
        React.createElement(
          "div",
          { id: "site-header-right-top" },
          React.createElement(SiteHeaderSearchForm, null),
          userMenuDisplay
        ),
        React.createElement(
          "div",
          { id: "site-header-right-bottom" },
          loginMenuDisplay
        )
      )
    );
  }
}

class SiteHeaderSearchForm extends React.Component {
  constructor(props) {
    super(props);
    this.state = {};
  }
  render() {
    return React.createElement(
      "div",
      { id: "site-header-search-form" },
      "search form"
    );
  }
}

class SiteHeaderLoginMenu extends React.Component {
  constructor(props) {
    super(props);
    this.state = {};
  }
  render() {
    return React.createElement(
      "div",
      { id: "site-header-login-menu" },
      "login menu"
    );
  }
}

class SiteHeaderUserMenu extends React.Component {
  constructor(props) {
    super(props);
    this.state = {};
  }
  render() {
    return React.createElement(
      "div",
      { id: "site-header-user-menu-container" },
      "user menu container"
    );
  }
}

ReactDOM.render(React.createElement(SiteHeader, null), document.getElementById('site-header-container'));
