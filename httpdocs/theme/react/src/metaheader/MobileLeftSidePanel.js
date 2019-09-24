import React from 'react';
import AboutMenuItems from './function/AboutMenuItems';
import CommunityMenuItems from './function/CommunityMenuItems';
class MobileLeftSidePanel extends React.Component {
  constructor(props){
    super(props);
    this.state = {};
  }

  componentDidMount() {
    // let menuGroups = [];
    // this.props.domains.forEach(function(domain,index){
    //   if (menuGroups.indexOf(domain.menugroup) === -1){
    //     menuGroups.push(domain.menugroup);
    //   }
    // });
    // this.setState({menuGroups:menuGroups});
  }

  render(){
    // let panelMenuGroupsDisplay;
    // if (this.state.menuGroups){
    //   panelMenuGroupsDisplay = this.state.menuGroups.map((mg,i) => (
    //     <DomainsMenuGroup
    //       key={i}
    //       domains={this.props.domains}
    //       menuGroup={mg}
    //       sName={this.props.sName}
    //     />
    //   ));
    // }


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
            <li><a href={this.props.baseUrlStore}>Pling</a></li>
            <li><a href={this.props.gitlabUrl}>Opencode</a></li>
            <li>
              <a className="groupname"><b>Community</b></a>
              <ul>
                <CommunityMenuItems baseUrl={this.props.baseUrl}
                                    baseUrlStore = {this.props.baseUrlStore}
                                    forumUrl = {this.props.forumUrl}  />
              </ul>
            </li>

            <li>
              <a className="groupname"><b>About</b></a>
              <ul>
                <AboutMenuItems baseUrl={this.props.baseUrlStore}
                                isAdmin={this.props.isAdmin}/>
              </ul>
            </li>

            <li><a href={this.props.riotUrl}>Chat</a></li>
          </ul>
        </div>
      </div>
    )
  }
}

export default MobileLeftSidePanel;
