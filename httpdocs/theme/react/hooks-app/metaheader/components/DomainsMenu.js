import React from 'react';
import MoreDropDownMenu from './MoreDropDownMenu';

class DomainsMenu extends React.Component {
  constructor(props) {
    super(props);
    this.state = {
    };
  }



  render() {

    let moreMenuItemDisplay, adminsDropDownMenuDisplay, myOpendesktopMenuDisplay;
    if (this.props.device !== "large") {
      moreMenuItemDisplay = (
        <MoreDropDownMenu
          domains={this.props.domains}
          baseUrl={this.props.baseUrl}
          blogUrl={this.props.blogUrl}
          isAdmin={this.props.isAdmin}
          user={this.props.user}
          gitlabUrl={this.props.gitlabUrl}
          isExternal={this.props.isExternal}
          baseUrlStore={this.props.baseUrlStore}
        />
      )
    }

    let dT;
    if (this.props.target) {
      switch (this.props.target.target) {
        case 'opendesktop':
          dT =(
            <>
              <li className="active">
                <a id="opendesktop-logo" href={this.props.baseUrl}>
                  <img src={this.props.baseUrl + "/images/system/ocs-logo-rounded-16x16.png"} className="logo" />
                  openDesktop.org 
                </a>
              </li>
              <li><a href={this.props.baseUrlStore}>Pling</a></li>
              <li><a href={this.props.gitlabUrl + "/explore/projects"}>Opencode</a></li>
            </>
          );
          break;
          case 'pling':
            dT =(
              <>
                <li  className="active">
                  <a id="pling-logo" href={this.props.baseUrlStore}>
                    <img src={this.props.baseUrlStore + "/theme/react/assets/img/logo-pling.png"} className="logo" />                    
                  </a>
                </li>
                <li><a href={this.props.baseUrl}>openDesktop.org</a></li>
                <li><a href={this.props.gitlabUrl + "/explore/projects"}>Opencode</a></li>
              </>
            );
            break;
          case 'gitlab':
            dT =(
              <>
                <li  className="active">
                  <a id="gitlab-logo" href={this.props.gitlabUrl + "/explore/projects"}>
                    <img src={this.props.baseUrl + "/theme/react/assets/img/logo-opencode.png"} className="logo" />
                    Opencode 
                  </a>
                </li>
                
                <li><a href={this.props.baseUrlStore}>Pling</a></li>
                <li><a href={this.props.baseUrl}>openDesktop.org</a></li>
                
              </>
            );
            break;
        default:
            dT =(
              <>
                <li className="active">
                  <a id="opendesktop-logo" href={this.props.baseUrl}>
                    <img src={this.props.baseUrl + "/images/system/ocs-logo-rounded-16x16.png"} className="logo" />
                    openDesktop.org : {this.props.target.target}
                  </a>
                </li>
                <li><a href={this.props.baseUrlStore}>Pling</a></li>
                <li><a href={this.props.gitlabUrl + "/explore/projects"}>Opencode</a></li>
              </>
            );
          break;
      }
    }else{
      dT =(
        <>
          <li className="active">
            <a id="opendesktop-logo" href={this.props.baseUrl}>
              <img src={this.props.baseUrl + "/images/system/ocs-logo-rounded-16x16.png"} className="logo" />
              openDesktop.org 
            </a>
          </li>
          <li><a href={this.props.baseUrlStore}>Pling</a></li>
          <li><a href={this.props.gitlabUrl + "/explore/projects"}>Opencode</a></li>
        </>
      );
    }

    return (
      <ul className="metaheader-menu left" id="domains-menu">
        {dT}
        {moreMenuItemDisplay}
      </ul>
    )
  }
}
export default DomainsMenu;
