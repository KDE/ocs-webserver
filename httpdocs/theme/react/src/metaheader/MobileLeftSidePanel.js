import React from 'react';

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


    let faqLinkItem, apiLinkItem, aboutLinkItem,aboutPlingItem, aboutopencodeItem;

    aboutPlingItem = (<li><a id="faq" href={this.props.baseUrl +"/faq-pling"}>FAQ Pling</a></li>);
    aboutopencodeItem = (<li><a id="faq" href={this.props.baseUrl +"/faq-opencode"}>FAQ Opencode</a></li>);
    aboutLinkItem = (<li><a id="about" href={this.props.baseUrl +"/about"}>About</a></li>);
    apiLinkItem = (<li><a  href={this.props.baseUrl +"/ocs-api"}>API</a></li>);
    // if (this.props.isExternal === false){
    //   apiLinkItem = (<li><a className="popuppanel" id="api" href={"/partials/ocsapicontent.phtml"}>API</a></li>);
    // } else {
    //   apiLinkItem = (<li><a className="popuppanel" target="_blank" id="api" href={this.props.baseUrl + "/#api"}>API</a></li>);
    // }
    if (this.props.isAdmin ){
      faqLinkItem = (<li><a className="popuppanel" id="faq" href={"/plings"}>Plings (admin only)</a></li>);
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
            <li><a href={this.props.baseUrlStore}>Publish</a></li>
            <li><a href={this.props.gitlabUrl}>Code</a></li>
            <li>
              <a className="groupname"><b>Community</b></a>
              <ul>
                <li><a href={this.props.baseUrl + "/community"}>Members</a></li>
                <li><a href={this.props.forumUrl}>Discussion</a></li>
              </ul>
            </li>
            <li><a href={this.props.baseUrl + "/support"}>Supporter</a></li>
            <li>
              <a className="groupname"><b>About</b></a>
              <ul>
                <li><a href={this.props.blogUrl} target="_blank">Blog</a></li>
                {faqLinkItem}
                {apiLinkItem}
                {aboutPlingItem}
                {aboutopencodeItem}
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
