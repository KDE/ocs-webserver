class MetaHeader extends React.Component {
  constructor(props) {
    super(props);
    this.state = {};
  }

  componentDidMount() {
    console.log('component did mount');
  }

  render() {
    return React.createElement(
      "nav",
      { id: "metaheader-nav" },
      React.createElement(DomainsMenu, null),
      React.createElement(UserMenu, {
        user: this.state.user
      })
    );
  }
}

class DomainsMenu extends React.Component {
  constructor(props) {
    super(props);
    this.state = {};
  }

  render() {
    return React.createElement(
      "ul",
      { className: "metaheader-menu left", id: "domains-menu" },
      React.createElement(
        "li",
        null,
        React.createElement(
          "a",
          { href: "#" },
          "test"
        )
      ),
      React.createElement(
        "li",
        null,
        React.createElement(
          "a",
          { href: "#" },
          "test"
        )
      ),
      React.createElement(
        "li",
        null,
        React.createElement(
          "a",
          { href: "#" },
          "test"
        )
      ),
      React.createElement(
        "li",
        null,
        React.createElement(
          "a",
          { href: "#" },
          "test"
        )
      ),
      React.createElement(
        "li",
        null,
        React.createElement(
          "a",
          { href: "#" },
          "test"
        )
      ),
      React.createElement(
        "li",
        null,
        React.createElement(
          "a",
          { href: "#" },
          "test"
        )
      )
    );
  }
}

class UserMenu extends React.Component {
  constructor(props) {
    super(props);
    this.state = {};
  }
  render() {
    let userDropdownDisplay;
    if (this.props.user) {
      userDropdownDisplay = React.createElement(
        "li",
        null,
        "User"
      );
    } else {
      userDropdownDisplay = React.createElement(
        "li",
        { id: "user-login-container" },
        React.createElement(
          "a",
          { className: "btn btn-metaheader" },
          "Login"
        )
      );
    }
    return React.createElement(
      "div",
      { id: "user-menu-container", className: "right" },
      React.createElement(
        "ul",
        { className: "metaheader-menu right", id: "user-menu" },
        React.createElement(
          "li",
          null,
          React.createElement(
            "a",
            { href: "/community" },
            "Community"
          )
        ),
        React.createElement(
          "li",
          null,
          React.createElement(
            "a",
            { href: "<?=$url_blog?>", target: "_blank" },
            "Blog"
          )
        ),
        React.createElement(
          "li",
          null,
          React.createElement(
            "a",
            { id: "plingList", className: "popuppanel", href: "/plings" },
            "What are Plings?"
          )
        ),
        React.createElement(
          "li",
          null,
          React.createElement(
            "a",
            { id: "ocsapiContent", className: "popuppanel", href: "/partials/ocsapicontent.phtml" },
            "API"
          )
        ),
        React.createElement(
          "li",
          null,
          React.createElement(
            "a",
            { id: "aboutContent", className: "popuppanel", href: "/partials/about.phtml" },
            "About"
          )
        ),
        React.createElement(
          "li",
          null,
          React.createElement("span", { className: "glyphicon glyphicon-th" })
        ),
        userDropdownDisplay
      )
    );
  }
}

ReactDOM.render(React.createElement(MetaHeader, null), document.getElementById('metaheader'));
