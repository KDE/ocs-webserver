import React from 'react';
import UserLoginMenuContainer from './UserLoginMenuContainer';
import UserContextMenuContainer from './UserContextMenuContainer';
import DevelopmentAppMenu from './DevelopmentAppMenu';
import SwitchItem from './SwitchItem';
class UserMenu extends React.Component {
  constructor(props){
    super(props);
    this.state = {};
  }

  render(){
    let userDropdownDisplay, userAppsContextDisplay, developmentAppMenuDisplay;
    if (this.props.user && this.props.user.member_id){
      // userDropdownDisplay = (
      //   <UserLoginMenuContainerVersionTwo
      //     user={this.props.user}
      //     logoutUrl={this.props.logoutUrl}
      //     baseUrl={this.props.baseUrl}
      //   />
      // );

      userDropdownDisplay = (
        <UserLoginMenuContainer
          user={this.props.user}
          logoutUrl={this.props.logoutUrl}
          baseUrl={this.props.baseUrl}
        />
      );

      userAppsContextDisplay = (
        <UserContextMenuContainer
          user={this.props.user}
          forumUrl={this.props.forumUrl}
          gitlabUrl={this.props.gitlabUrl}
          isAdmin={this.props.isAdmin}
          baseUrl={this.props.baseUrl}
        />
      );
      developmentAppMenuDisplay = (
        <DevelopmentAppMenu
          user={this.props.user}
          forumUrl={this.props.forumUrl}
          gitlabUrl={this.props.gitlabUrl}
          isAdmin={this.props.isAdmin}
          baseUrl={this.props.baseUrl}
        />
      );
    } else {
      userDropdownDisplay = (
        <li id="user-login-container"><a href={this.props.loginUrl} className="btn btn-metaheader">Login</a></li>
      )
    }

    let userMenuContainerDisplay;
    if (this.props.device === "large"){

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

      let switchItem;

      if (this.props.user && this.props.user.member_id && this.props.isAdmin ){
      switchItem =(<li><SwitchItem onSwitchStyle={this.props.onSwitchStyle}
                  onSwitchStyleChecked={this.props.onSwitchStyleChecked}/></li>);
      }

      userMenuContainerDisplay = (
        <ul className="metaheader-menu" id="user-menu">
          <li><a href={this.props.baseUrl + "/community"}>Community</a></li>
          <li><a href={this.props.baseUrl + "/support"}>Support</a></li>
          <li><a href={this.props.blogUrl} target="_blank">Blog</a></li>
          {faqLinkItem}
          {apiLinkItem}
          {aboutLinkItem}
          {switchItem}
          {developmentAppMenuDisplay}
          {userAppsContextDisplay}
          {userDropdownDisplay}
        </ul>
      );
    } else {
      userMenuContainerDisplay = (
        <ul className="metaheader-menu" id="user-menu">
          {developmentAppMenuDisplay}
          {userAppsContextDisplay}
          {userDropdownDisplay}
        </ul>
      );
    }


    return (
      <div id="user-menu-container" className="right">
        {userMenuContainerDisplay}
      </div>
    )
  }
}

export default UserMenu;
