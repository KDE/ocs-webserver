import React from 'react';
import MoreDropDownMenu from './MoreDropDownMenu';
import DomainsMenu_subdomain from './DomainsMenu_subdomain';

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
          forumUrl = {this.props.forumUrl}
        />
      )
    }
        
    let dT;    
    let cls =(this.props.onSwitchStyleChecked?'dark':'active');
    if(this.props.target)
    {
      if(this.props.target.target=='gitlab' )
      {        
        if(cls!='dark'){
          cls = 'active';
        }else{
          cls = 'gitlab';
        }        
      }    
    }

   

    if (this.props.target) {
      switch (this.props.target.target) {
        case 'opendesktop':
          dT =(
            <>
              <li className={cls}>              
                <a id="opendesktop-logo-single" href={this.props.baseUrl} >
                  <img src={this.props.baseUrl + "/images/system/ocs-logo-rounded-16x16.png"} className="logo" />
                  openDesktop.org : 
                </a>   
                <DomainsMenu_subdomain domains={this.props.domains} 
                                        baseUrlStore={this.props.baseUrlStore}
                                        storeConfig ={this.props.storeConfig}
                                        />           
              </li>
              
              <li><a href={this.props.baseUrlStore}>Pling</a></li>
              <li><a href={this.props.gitlabUrl + "/explore/projects"}>Opencode</a></li>
            </>
          );
          break;
          case 'pling':
            dT =(
              <>
                <li className={cls}>
                  <a id="pling-logo" href={this.props.baseUrlStore}>
                    <span><img src={this.props.baseUrlStore + "/theme/react/assets/img/logo-pling.png"} className="logo" />
                  </span> :                    
                  </a>  
                  <DomainsMenu_subdomain domains={this.props.domains} 
                                        baseUrlStore={this.props.baseUrlStore}
                                        storeConfig ={this.props.storeConfig}
                                        />                   
                </li>
                <li><a href={this.props.baseUrl}>openDesktop.org</a></li>
                <li><a href={this.props.gitlabUrl + "/explore/projects"}>Opencode</a></li>
              </>
            );
            break;
          case 'kde-store':
            dT =(
              <>
                <li className={cls}>
                  <a id="kdeStore-logo" href={this.props.target.link}>
                    <img src={this.props.baseUrlStore + "/images_sys/store_kde/logo.png"} className="logo" />
                    { this.props.target.logoLabel }
                  </a>
                </li>
                <li><a href={this.props.baseUrlStore}>Pling</a></li>
                <li><a href={this.props.baseUrl}>openDesktop.org</a></li>
                <li><a href={this.props.gitlabUrl + "/explore/projects"}>Opencode</a></li>
              </>
            );
            break;
          case 'gitlab':
            dT =(
              <>
                <li className={cls}>
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
                <li className={cls}>                 
                    <a id="opendesktop-logo" href={this.props.baseUrl} >
                      <img src={this.props.baseUrl + "/images/system/ocs-logo-rounded-16x16.png"} className="logo" />
                      openDesktop.org : 
                    </a>
                    <a href={this.props.target.link} >
                      <span className="target">{ this.props.target.logoLabel }</span>
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
          <li className={cls}>
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
