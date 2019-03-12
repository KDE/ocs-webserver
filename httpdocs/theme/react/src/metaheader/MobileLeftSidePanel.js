import React from 'react';
import DomainsMenuGroup from './DomainsMenuGroup';
import DevelopmentDropDownMenu from './DevelopmentDropDownMenu';
class MobileLeftSidePanel extends React.Component {
  constructor(props){
    super(props);
    this.state = {};
  }

  componentDidMount() {
    let menuGroups = [];
    this.props.domains.forEach(function(domain,index){
      if (menuGroups.indexOf(domain.menugroup) === -1){
        menuGroups.push(domain.menugroup);
      }
    });
    this.setState({menuGroups:menuGroups});
  }

  render(){
    let panelMenuGroupsDisplay;
    if (this.state.menuGroups){
      panelMenuGroupsDisplay = this.state.menuGroups.map((mg,i) => (
        <DomainsMenuGroup
          key={i}
          domains={this.props.domains}
          menuGroup={mg}
          sName={this.props.sName}
        />
      ));
    }

    let faqLinkItem, apiLinkItem, aboutLinkItem;
    if (this.props.isExternal === false){
      faqLinkItem = (<li><a className="popuppanel" id="faq" href={"/plings"}>FAQ</a></li>);
      apiLinkItem = (<li><a className="popuppanel" id="api" href={"/partials/ocsapicontent.phtml"}>API</a></li>);
      aboutLinkItem = (<li><a className="popuppanel" id="about" href={"/partials/about.phtml"}>About</a></li>);
    } else {
      faqLinkItem = (<li><a className="popuppanel" target="_blank" id="faq" href={this.props.baseUrl + "/#faq"}>FAQ</a></li>);
      apiLinkItem = (<li><a className="popuppanel" target="_blank" id="api" href={this.props.baseUrl + "/#api"}>API</a></li>);
      aboutLinkItem = (<li><a className="popuppanel" target="_blank" id="about" href={this.props.baseUrl + "/#about"}>About</a></li>);
    }

    return (
      <div id="left-side-panel">
        <div id="panel-header">
          <a href={this.props.baseUrl}>
            <img src={this.props.baseUrl + "/images/system/opendesktop-logo.png"} className="logo"/>
            openDesktop.org
          </a>
        </div>
        <div id="panel-menu">
          <ul>
            {panelMenuGroupsDisplay}
            <li>
              <a className="groupname"><b>Discussion Boards</b></a>
              <ul>
                <li><a href={this.props.forumUrl }>General</a></li>
                <li><a href={this.props.forumUrl + "/c/themes"}>Themes</a></li>
                <li><a href={this.props.forumUrl + "/c/apps"}>Apps</a></li>
                <li><a href={this.props.forumUrl + "/c/coding"}>Coding</a></li>
              </ul>
            </li>
            <DevelopmentDropDownMenu
              user={this.props.user}
              baseUrl={this.props.baseUrl}
              gitlabUrl={this.props.gitlabUrl}
              isAdmin={this.props.isAdmin}
            />
            <li>
              <a className="groupname"><b>More</b></a>
              <ul>
                <li><a href={this.props.baseUrl + "/community"}>Community</a></li>
                <li><a href={this.props.baseUrl + "/support"}>Support</a></li>
                <li><a href={this.props.blogUrl} target="_blank">Blog</a></li>
                {faqLinkItem}
                {apiLinkItem}
                {aboutLinkItem}
              </ul>
            </li>
          </ul>
        </div>
      </div>
    )
  }
}

export default MobileLeftSidePanel;
