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

  function formatDate(dateString) {
    const monthNames = ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"];

    const date = dateString.split(' ')[0];
    const year = date.split('-')[0];
    const month = date.split('-')[1];
    const day = date.split('-')[2];
    const monthNameIndex = parseInt(month) - 1;
    const monthName = monthNames[monthNameIndex];

    return monthName + ' ' + day + ' ' + year;
  }

  return {
    getHostNameSuffix,
    generateTabsMenuArray,
    formatDate
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
      { id: "community-page-header", className: "head-wrap" },
      React.createElement(
        "h1",
        null,
        "Community"
      ),
      React.createElement(
        "div",
        { id: "community-page-header-banner", className: "col-lg-5 col-md-5 col-sm-6 col-xs-8" },
        React.createElement(
          "div",
          { id: "header-banner-top" },
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
              { className: "btn btn-native", href: "/register" },
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
      { id: "community-page-tabs-container", className: "body-wrap" },
      React.createElement(
        "div",
        { id: "tabs-menu" },
        tabsMenuDisplay
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
    const activeCssClass = this.props.index === this.props.selectedIndex ? "active" : "";
    return React.createElement(
      "div",
      { className: "tab-menu-item " + activeCssClass },
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
      const selectedIndex = this.props.selectedIndex;
      usersDisplay = this.props.items.map((user, index) => React.createElement(CommunityListItem, {
        key: index,
        index: index,
        selectedIndex: selectedIndex,
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
      const selectedIndex = this.props.selectedIndex;
      creatorsDisplay = this.props.items.map((creator, index) => React.createElement(CommunityListItem, {
        key: index,
        item: creator,
        type: 'creator',
        index: index,
        selectedIndex: selectedIndex
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
      const selectedIndex = this.props.selectedIndex;
      products = this.props.items.map((product, index) => React.createElement(CommunityListItem, {
        key: index,
        item: product,
        type: 'product',
        index: index,
        selectedIndex: selectedIndex
      }));
    }

    let productsDisplay, tabContainerCssClass;
    if (this.props.selectedIndex === 2) {
      productsDisplay = React.createElement(
        "ol",
        null,
        products
      );
      tabContainerCssClass = "top-list-display";
    } else if (this.props.selectedIndex === 3) {
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
      const selectedIndex = this.props.selectedIndex;
      members = this.props.items.map((member, index) => React.createElement(CommunityListItem, {
        key: index,
        item: member,
        type: 'score',
        index: index,
        selectedIndex: selectedIndex
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
    this.state = {};
  }

  render() {

    const i = this.props.item;

    /* USER DISPLAY */
    let userCreatedAt;
    if (i.created_at) {
      userCreatedAt = window.appHelpers.formatDate(i.created_at);
    }
    let byDisplay;
    if (this.props.selectedIndex === 2) {
      byDisplay = React.createElement(
        "span",
        { className: "by" },
        "by"
      );
    }
    const userDisplay = React.createElement(
      "a",
      { href: "/u/" + i.username + "/", className: "user-display-container" },
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
          byDisplay,
          i.username
        ),
        React.createElement(
          "span",
          { className: "user-created" },
          userCreatedAt
        )
      )
    );
    /* /USER DISPLAY */

    /* PROJECT DISPLAY */
    const projectDisplay = React.createElement(
      "a",
      { href: "/p/" + i.project_id },
      React.createElement(
        "div",
        { className: "project" },
        React.createElement(
          "figure",
          null,
          React.createElement("img", { src: i.image_small })
        ),
        React.createElement(
          "div",
          { className: "project-info" },
          React.createElement(
            "h3",
            { className: "project-title" },
            i.title
          ),
          React.createElement(
            "span",
            { className: "cat-title" },
            i.catTitle
          )
        )
      )
    );
    /* /PROJECT DISPLAY */

    let displayTemplate;
    if (this.props.selectedIndex === 0 || this.props.selectedIndex === 4) {
      displayTemplate = React.createElement(
        "div",
        { className: "list-item-template" },
        userDisplay
      );
    } else if (this.props.selectedIndex === 1) {
      displayTemplate = React.createElement(
        "div",
        { className: "list-item-template" },
        React.createElement(
          "div",
          { className: "creator-wrapper" },
          React.createElement(
            "div",
            { className: "list-ranking" },
            this.props.index + 1
          ),
          userDisplay,
          React.createElement(
            "div",
            { className: "score-container" },
            React.createElement(
              "span",
              { className: "score" },
              React.createElement("img", { src: "/images/system/pling-btn-active.png" }),
              i.cnt
            )
          )
        )
      );
    } else if (this.props.selectedIndex === 2 || this.props.selectedIndex === 3) {
      console.log('what the fuck');
      displayTemplate = React.createElement(
        "div",
        { className: "list-item-template" },
        React.createElement(
          "div",
          { className: "creator-wrapper" },
          React.createElement(
            "div",
            { className: "list-ranking" },
            this.props.index + 1
          ),
          projectDisplay,
          userDisplay,
          React.createElement(
            "div",
            { className: "score-container" },
            React.createElement(
              "span",
              { className: "score" },
              React.createElement("img", { src: "/images/system/pling-btn-active.png" }),
              i.laplace_score
            )
          )
        )
      );
    } else if (this.props.selectedIndex === 5 || this.props.selectedIndex === 6) {
      // displayTemplate
    }

    return React.createElement(
      "li",
      { className: "list-item" },
      displayTemplate
    );
  }
}

ReactDOM.render(React.createElement(CommunityPage, null), document.getElementById('community-page-container'));
