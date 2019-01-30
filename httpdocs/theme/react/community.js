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
    return tabsMenuArray;
  }

  return {
    getHostNameSuffix,
    generateTabsMenuArray
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
    this.state = {
      loading: true,
      tabs: window.appHelpers.generateTabsMenuArray(),
      selectedIndex: 0
    };
    this.renderTabs = this.renderTabs.bind(this);
    this.getSelectedTabData = this.getSelectedTabData.bind(this);
    this.handleTabMenuItemClick = this.handleTabMenuItemClick.bind(this);
  }

  componentDidMount() {
    this.getSelectedTabData();
  }

  renderTabs(selectedIndex) {
    if (!selectedIndex) {
      selectedIndex = 0;
    }
    const tabs = window.appHelpers.generateTabsMenuArray();
    this.setState({
      tabs: tabs,
      selectedIndex: selectedIndex
    }, function () {
      this.getSelectedTabData();
    });
  }

  getSelectedTabData() {
    // get selected tab thing
    const self = this;
    const selectedTab = self.state.tabs[self.state.selectedIndex];
    $.ajax({ url: selectedTab.url, cache: false }).done(function (response) {
      self.setState({
        tabContent: {
          title: selectedTab.title,
          data: response.data
        },
        loading: false
      });
    });
  }

  handleTabMenuItemClick(itemIndex) {
    this.setState({ loading: true }, function () {
      this.renderTabs(itemIndex);
    });
  }

  render() {
    const selectedIndex = this.state.selectedIndex;
    const tabsMenuDisplay = this.state.tabs.map((t, index) => React.createElement(CommunityPageTabMenuItem, {
      key: index,
      index: index,
      selectedIndex: selectedIndex,
      tab: t,
      onTabMenuItemClick: this.handleTabMenuItemClick
    }));

    let tabContent;
    if (this.state.loading) {

      tabContent = React.createElement(
        "div",
        null,
        "loading"
      );
    } else if (this.state.loading === false) {

      const data = this.state.tabContent.data;

      if (this.state.selectedIndex === 0 || this.state.selectedIndex === 4) {
        tabContent = React.createElement(UsersTab, { selectedIndex: this.state.selectedIndex, items: data });
      } else if (this.state.selectedIndex === 1) {
        tabContent = React.createElement(CreatorsTab, { selectedIndex: this.state.selectedIndex, items: data });
      } else if (this.state.selectedIndex === 2 || this.state.selectedIndex === 3) {
        tabContent = React.createElement(PlingedProductsTab, { selectedIndex: this.state.selectedIndex, items: data });
      } else if (this.state.selectedIndex === 5 || this.state.selectedIndex === 6) {
        tabContent = React.createElement(MemberScoresTab, { selectedIndex: this.state.selectedIndex, items: data });
      }
    }

    return React.createElement(
      "div",
      { id: "community-page-tabs-container" },
      React.createElement(
        "div",
        { id: "tabs-menu" },
        React.createElement(
          "ul",
          null,
          tabsMenuDisplay
        )
      ),
      React.createElement(
        "div",
        { id: "tabs-content" },
        tabContent
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
        this.props.tab.title
      )
    );
  }
}

class UsersTab extends React.Component {
  constructor(props) {
    super(props);
    this.state = {};
  }

  render() {
    let usersDisplay;
    if (this.props.items && this.props.items.length > 0) {
      usersDisplay = this.props.items.map((user, index) => React.createElement(CommunityListItem, {
        key: index,
        item: user,
        type: 'user'
      }));
    }
    return React.createElement(
      "div",
      { className: "community-tab card-list-display", id: "supporters-tab" },
      React.createElement(
        "ul",
        null,
        usersDisplay
      )
    );
  }
}

class CreatorsTab extends React.Component {
  constructor(props) {
    super(props);
    this.state = {};
  }

  render() {
    let creatorsDisplay;
    if (this.props.items && this.props.items.length > 0) {
      creatorsDisplay = this.props.items.map((creator, index) => React.createElement(CommunityListItem, {
        key: index,
        item: creator,
        type: 'creator'
      }));
    }
    return React.createElement(
      "div",
      { className: "community-tab top-list-display", id: "most-pling-creators-tab" },
      React.createElement(
        "ol",
        null,
        creatorsDisplay
      )
    );
  }
}

class PlingedProductsTab extends React.Component {
  constructor(props) {
    super(props);
    this.state = {};
  }

  render() {
    let products;
    if (this.props.items && this.props.items.length > 0) {
      products = this.props.items.map((product, index) => React.createElement(CommunityListItem, {
        key: index,
        item: product,
        type: 'product'
      }));
    }

    let productsDisplay, tabContainerCssClass;
    if (this.props.selectedIndex === 3) {
      productsDisplay = React.createElement(
        "ol",
        null,
        products
      );
      tabContainerCssClass = "top-list-display";
    } else if (this.props.selectedIndex === 4) {
      productsDisplay = React.createElement(
        "ul",
        null,
        products
      );
      tabContainerCssClass = "card-list-display";
    }
    return React.createElement(
      "div",
      { className: "community-tab " + tabContainerCssClass, id: "most-pling-creators-tab" },
      productsDisplay
    );
  }
}

class MemberScoresTab extends React.Component {
  constructor(props) {
    super(props);
    this.state = {};
  }

  render() {
    let members;
    if (this.props.items && this.props.items.length > 0) {
      members = this.props.items.map((member, index) => React.createElement(CommunityListItem, {
        key: index,
        item: member,
        type: 'score'
      }));
    }

    let membersDisplay, tabContainerCssClass;
    if (this.props.selectedIndex === 6) {
      membersDisplay = React.createElement(
        "ol",
        null,
        members
      );
      tabContainerCssClass = "top-list-display";
    } else if (this.props.selectedIndex === 5) {
      membersDisplay = React.createElement(
        "ul",
        null,
        members
      );
      tabContainerCssClass = "card-list-display";
    }

    return React.createElement(
      "div",
      { className: "community-tab " + tabContainerCssClass, id: "supporters-tab" },
      membersDisplay
    );
  }
}

class CommunityListItem extends React.Component {
  constructor(props) {
    super(props);
    console.log(this.props.item);
    this.state = {};
  }

  render() {

    const i = this.props.item;
    let score;
    if (this.props.type === 'user') {
      // score = '';
    } else if (this.props.type === 'creator') {
      score = i.cnt;
    } else if (this.props.type === 'product') {
      score = i.laplace_score;
    } else if (this.props.type === 'score') {
      score = i.score;
    }

    const usersDisplay = React.createElement(
      "div",
      { className: "user-display-container" },
      React.createElement(
        "div",
        { className: "user" },
        React.createElement(
          "figure",
          null,
          React.createElement("img", { src: i.profile_image_url })
        ),
        React.createElement(
          "span",
          { className: "username" },
          React.createElement(
            "a",
            { href: "/u/" + i.username + "/" },
            i.username
          )
        ),
        React.createElement(
          "span",
          { className: "user-created" },
          i.created_at
        )
      )
    );

    const project = {
      id: i.project_id,
      title: i.title,
      cat_title: i.catTitle,
      image_url: i.image_small
    };

    return React.createElement(
      "li",
      { className: "list-item" },
      usersDisplay
    );
  }
}

ReactDOM.render(React.createElement(CommunityPage, null), document.getElementById('community-page-container'));
