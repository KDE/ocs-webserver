import React from 'react';
import MoreDropDownMenu from './MoreDropDownMenu';

class DomainsMenu extends React.Component {
  constructor(props){
    super(props);
    this.state = {
    };
  }



  render(){


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

        {chatItem}

        {moreMenuItemDisplay}
      </ul>
    )
  }
}
export default DomainsMenu;
