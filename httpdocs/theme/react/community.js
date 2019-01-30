class CommunityPage extends React.Component {
  constructor(props) {
    super(props);
    this.state = {
      headerData: window.json_data.data
    };
  }

  componentDidMount() {
    console.log(this.state);
    /*
      var json_data = <?=json_encode($this->json_data)?>; hier bekommest du die oberteil info
      tabs info top members https://www.opendesktop.cc/community/getjson?e=topmembers
      hier ist supporters https://www.opendesktop.cc/community/getjson?e=supporters
      alle tab events sind hier. /var/www/ocs-webserver/application/modules/default/controllers/CommunityController.php
      getjsonAction
    */
  }

  render() {
    return React.createElement(
      "div",
      { id: "community-page" },
      React.createElement(
        "div",
        { className: "container" },
        React.createElement(CommunityPageHeader, {
          headerData: this.state.headerData
        }),
        React.createElement(CommunityPageTabsContainer, null)
      )
    );
  }
}

class CommunityPageHeader extends React.Component {
  constructor(props) {
    super(props);
    this.state = {};
  }

  render() {
    return React.createElement(
      "div",
      { id: "community-page-header" },
      React.createElement(
        "h1",
        null,
        "Community"
      ),
      React.createElement(
        "div",
        { id: "community-page-header-banner" },
        React.createElement(
          "div",
          { className: "header-banner-row" },
          React.createElement(
            "p",
            null,
            this.props.headerData.countActiveMembers
          ),
          React.createElement(
            "span",
            null,
            "contributors added"
          )
        ),
        React.createElement(
          "div",
          { className: "header-banner-row" },
          React.createElement(
            "p",
            null,
            this.props.headerData.countProjects
          ),
          React.createElement(
            "span",
            null,
            "products"
          )
        ),
        React.createElement(
          "div",
          { id: "header-banner-bottom" },
          React.createElement(
            "div",
            { className: "center" },
            React.createElement(
              "a",
              { href: "/register" },
              "Register"
            ),
            React.createElement(
              "span",
              null,
              "to join the community"
            )
          )
        )
      )
    );
  }
}

class CommunityPageTabsContainer extends React.Component {
  constructor(props) {
    super(props);
    this.state = {};
  }

  render() {
    return React.createElement(
      "div",
      { id: "community-page-tabs-container" },
      React.createElement(
        "div",
        { id: "tabs-menu" },
        "tabs"
      ),
      React.createElement(
        "div",
        { id: "tabs-content" },
        "content"
      )
    );
  }
}

ReactDOM.render(React.createElement(CommunityPage, null), document.getElementById('community-page-container'));
