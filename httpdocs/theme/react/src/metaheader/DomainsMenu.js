import React from 'react';
import MoreDropDownMenu from './MoreDropDownMenu';

import DevelopmentAppMenu from './DevelopmentAppMenu';
class DomainsMenu extends React.Component {
  constructor(props){
    super(props);
    this.state = {
    };
  }



  render(){

    let developmentAppMenuDisplay;
      if (this.props.user && this.props.user.member_id){
         developmentAppMenuDisplay = (
        <DevelopmentAppMenu
          user={this.props.user}
          forumUrl={this.props.forumUrl}
          gitlabUrl={this.props.gitlabUrl}
          isAdmin={this.props.isAdmin}
          baseUrl={this.props.baseUrl}
          baseUrlStore={this.props.baseUrlStore}
          myopendesktopUrl={this.props.myopendesktopUrl}
          cloudopendesktopUrl={this.props.cloudopendesktopUrl}
          musicopendesktopUrl={this.props.musicopendesktopUrl}
          docsopendesktopUrl={this.props.docsopendesktopUrl}
        />
      );
    }
    let  chatItem=(<li id="chat-link-item"><a href={this.props.riotUrl}>
        <img src={this.props.baseUrl+"/theme/react/assets/img/chat.jpg"} className="riotIcon"></img>Chat
      </a></li>);

    let moreMenuItemDisplay, adminsDropDownMenuDisplay, myOpendesktopMenuDisplay;
    if (this.props.device !== "large"){
      moreMenuItemDisplay = (
        <MoreDropDownMenu
          domains={this.props.domains}
          baseUrl={this.props.baseUrl}
          blogUrl={this.props.blogUrl}
          isAdmin={this.props.isAdmin}
          user={this.props.user}
          gitlabUrl={this.props.gitlabUrl}
          isExternal = {this.props.isExternal}
          baseUrlStore={this.props.baseUrlStore}
        />
      )
    }

    return (
      <ul className="metaheader-menu left" id="domains-menu">
        <li className="active">
          <a id="opendesktop-logo" href={this.props.baseUrl}>
            <img src={this.props.baseUrl + "/images/system/ocs-logo-rounded-16x16.png"} className="logo"/>
            openDesktop.org :
          </a>
        </li>
        {developmentAppMenuDisplay}
        {chatItem}

        {moreMenuItemDisplay}
      </ul>
    )
  }
}
export default DomainsMenu;
