import React from 'react';
import UserLoginMenuContainer from './UserLoginMenuContainer';
import UserContextMenuContainer from './UserContextMenuContainer';
//import UserLoginMenuContainerVersionTwo from './UserLoginMenuContainerVersionTwo';
import DevelopmentAppMenu from './DevelopmentAppMenu';
import SwitchItem from './SwitchItem';
import AboutMenu from './AboutMenu';
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
        <React.Fragment>
        <li id="user-login-container"><a href={this.props.loginUrl} className="btn btn-metaheader">Login</a></li>
        <li id="user-register-container">or <a href={this.props.baseUrl + "/register"}>Register</a></li>
        </React.Fragment>
    )
    }

    let userMenuContainerDisplay;
    if (this.props.device === "large"){



      let switchItem;

      if (this.props.user && this.props.user.member_id ){
      switchItem =(<li><SwitchItem onSwitchStyle={this.props.onSwitchStyle}
                  onSwitchStyleChecked={this.props.onSwitchStyleChecked}/></li>);
      }

      const aboutMenu = <AboutMenu blogUrl={this.props.blogUrl}
                                  isExternal={this.props.isExternal}
                                  baseUrl={this.props.baseUrl}
                                  />

       let chatItem;
       const urlEnding = this.props.baseUrl.split('opendesktop.')[1];
       if (this.props.user && this.props.user.member_id ){
         chatItem=(<li id="chat-link-item"><a href={"https://chat.opendesktop."+urlEnding}>
           <img src={this.props.baseUrl+"/theme/react/assets/img/logo-riot.svg"} className="riotIcon"></img>Chat
         </a></li>);
       }
      userMenuContainerDisplay = (
        <ul className="metaheader-menu" id="user-menu">
          <li><a href={this.props.baseUrl + "/community"}>Community</a></li>
          <li><a href={this.props.baseUrl + "/support"}>Support</a></li>
          {aboutMenu}
          {chatItem}
          {switchItem}
          {userAppsContextDisplay}
          {developmentAppMenuDisplay}
          {userDropdownDisplay}
        </ul>
      );
    } else {
      userMenuContainerDisplay = (
        <ul className="metaheader-menu" id="user-menu">
          {userAppsContextDisplay}
          {developmentAppMenuDisplay}
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
