import React from 'react';
import MoreDropDownMenu from './MoreDropDownMenu';
import DomainsDropDownMenu from './DomainsDropDownMenu';
import DiscussionBoardsDropDownMenu from './DiscussionBoardsDropDownMenu';
import DevelopmentDropDownMenu from './DevelopmentDropDownMenu';
class DomainsMenu extends React.Component {
  constructor(props){
    super(props);
    this.state = {
    };
  }



  render(){

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
        <DomainsDropDownMenu
          domains={this.props.domains}
        />
        <DiscussionBoardsDropDownMenu
          forumUrl={this.props.forumUrl}
          user={this.props.user}
          baseUrl={this.props.baseUrl}
        />
        <DevelopmentDropDownMenu
          user={this.props.user}
          baseUrl={this.props.baseUrl}
          gitlabUrl={this.props.gitlabUrl}
          isAdmin={this.props.isAdmin}
          isExternal = {this.props.isExternal}
        />
        {moreMenuItemDisplay}
      </ul>
    )
  }
}
export default DomainsMenu;
