window.appHelpers = function () {

  function getHostNameSuffix() {
    let hostNameSuffix = "org";
    if (location.hostname.endsWith('cc')) {
      hostNameSuffix = "cc";
    } else if (location.hostname.endsWith('localhost')) {
      hostNameSuffix = "localhost";
    }
    return hostNameSuffix;
  }

  function generateTabsMenuArray() {
    const baseUrl = "https://www.opendesktop." + this.getHostNameSuffix();
    const tabsMenuArray = [{
      title: "Supporters",
      url: baseUrl + "/community/getjson?e=supporters"
    }, {
      title: "Most plinged Creators",
      url: baseUrl + "/community/getjson?e=mostplingedcreators"
    }, {
      title: "Most plinged Products",
      url: baseUrl + "/community/getjson?e=mostplingedproducts"
    }, {
      title: "Recently plinged Products",
      url: baseUrl + "/community/getjson?e=plingedprojects"
    }, {
      title: "New Members",
      url: baseUrl + "/community/getjson?e=newmembers"
    }, {
      title: "Top Members",
      url: baseUrl + "/community/getjson?e=topmembers"
    }, {
      title: "Top List Members",
      url: baseUrl + "/community/getjson?e=toplistmembers"
    }];
  }

  return {
    getHostNameSuffix
  };
}();

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
      tabs info top members https://www.opendesktop.cc
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
    this.renderTabs = this.renderTabs.bind(this);
    this.handleTabMenuItemClick = this.handleTabMenuItemClick.bind(this);
  }

  componentDidMount() {
    this.renderTabs();
  }

  renderTabs(selectedIndex) {
    if (!selectedIndex) {
      selectedIndex = 0;
    }
    const tabs = window.appHelpers.generateTabsMenuArray();
    this.setState({ tabs: tabs, selectedIndex: selectedIndex });
  }

  handleTabMenuItemClick(itemIndex) {
    console.log(this.state.tabs[itemIndex]);
  }

  render() {

    let tabsMenu;
    if (this.state.tabs) {
      const selectedIndex = this.state.selectedIndex;
      const tabsMenuDisplay = this.state.tabs.map((t, index) => React.createElement(CommunityPageTabMenuItem, {
        key: index,
        index: index,
        selectedIndex: selectedIndex,
        tab: t,
        onTabMenuItemClick: this.handleTabMenuItemClick
      }));
      tabsMenu = React.createElement(
        "ul",
        null,
        tabsMenuDisplay
      );
    }

    return React.createElement(
      "div",
      { id: "community-page-tabs-container" },
      React.createElement(
        "div",
        { id: "tabs-menu" },
        tabsMenu
      ),
      React.createElement(
        "div",
        { id: "tabs-content" },
        "content"
      )
    );
  }
}

class CommunityPageTabMenuItem extends React.Component {
  constructor(props) {
    super(props);
    this.state = {};
    this.onTabMenuItemClick = this.onTabMenuItemClick.bind(this);
  }

  onTabMenuItemClick() {
    this.props.onTabMenuItemClick(this.props.index);
  }

  render() {
    return React.createElement(
      "li",
      null,
      React.createElement(
        "a",
        { onClick: this.onTabMenuItemClick },
        this.props.title
      )
    );
  }
}

ReactDOM.render(React.createElement(CommunityPage, null), document.getElementById('community-page-container'));
