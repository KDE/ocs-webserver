import React, { useContext} from 'react';
import MoreDropDownMenu from './MoreDropDownMenu';
import DomainsMenu_subdomain from './DomainsMenu_subdomain';

import {MetaheaderContext} from '../contexts/MetaheaderContext';

const DomainsMenu = (props) => {
  const {state} = useContext(MetaheaderContext);
  let moreMenuItemDisplay;
  if (props.device !== "large") {
    moreMenuItemDisplay = (
      <MoreDropDownMenu  />
    )
  }

    
  let cls =(props.onSwitchStyleChecked?'dark':'active');
  if(state.target && state.target.target=='gitlab')
  {      
      cls = cls=='dark' ? 'gitlab':'active';        
  }

  let dT;  
  if (state.target) {
    switch (state.target.target) {
      case 'opendesktop':
        dT =(
          <>
            <li className={cls}>              
              <a id="opendesktop-logo-single" href={state.baseUrl} >
                <img src={state.baseUrl + "/images/system/ocs-logo-rounded-16x16.png"} className="logo" />
                openDesktop.org  
              </a>            
                       
            </li>            
            <li><a href={state.baseUrlStore}>Pling</a></li>
            <li><a href={state.gitlabUrl + "/explore/projects"}>Opencode</a></li>
          </>
        );
        break;
        case 'pling':
          dT =(
            <>
              <li className={cls}>
                <a id="pling-logo" href={state.baseUrlStore}>
                  <span><img src={state.baseUrlStore + "/theme/react/assets/img/logo-pling.png"} className="logo" />
                </span> :                    
                </a>  
                <DomainsMenu_subdomain domains={state.domains} 
                                      baseUrlStore={state.baseUrlStore}
                                      storeConfig ={state.storeConfig}
                                      />                   
              </li>
              <li><a href={state.baseUrl}>openDesktop.org</a></li>
              <li><a href={state.gitlabUrl + "/explore/projects"}>Opencode</a></li>
            </>
          );
          break;
        case 'kde-store':
          dT =(
            <>
              <li className={cls}>
                <a id="kdeStore-logo" href={state.target.link}>
                  <img src={state.baseUrlStore + "/images_sys/store_kde/logo.png"} className="logo" />
                  { state.target.logoLabel }
                </a>
              </li>
              <li><a href={state.baseUrlStore}>Pling</a></li>
              <li><a href={state.baseUrl}>openDesktop.org</a></li>
              <li><a href={state.gitlabUrl + "/explore/projects"}>Opencode</a></li>
            </>
          );
          break;
        case 'gitlab':
          dT =(
            <>
              <li className={cls}>
                <a id="gitlab-logo" href={state.gitlabUrl + "/explore/projects"}>
                  <img src={state.baseUrl + "/theme/react/assets/img/logo-opencode.png"} className="logo" />
                  Opencode 
                </a>
              </li>
              
              <li><a href={state.baseUrlStore}>Pling</a></li>
              <li><a href={state.baseUrl}>openDesktop.org</a></li>
              
            </>
          );
          break;
        case 'forum':
            
            let logoLabel =  window.location.href.indexOf('messages')>0 ? 'PM' : state.target.logoLabel;
            
            dT =(
              <>
                <li className={cls}>                 
                    <a id="opendesktop-logo" href={state.baseUrl} >
                      <img src={state.baseUrl + "/images/system/ocs-logo-rounded-16x16.png"} className="logo" />
                      openDesktop.org : 
                    </a>
                    <a href={state.target.link} style={{paddingLeft:'0px',marginLeft:'0px'}} >
                      <span className="target">{logoLabel}</span>
                    </a>                  
                </li>
                <li><a href={state.baseUrlStore}>Pling</a></li>
                <li><a href={state.gitlabUrl + "/explore/projects"}>Opencode</a></li>
              </>
            );
            break;
      default:          
          dT =(
            <>
              <li className={cls}>                 
                  <a id="opendesktop-logo" href={state.baseUrl} >
                    <img src={state.baseUrl + "/images/system/ocs-logo-rounded-16x16.png"} className="logo" />
                    openDesktop.org : 
                  </a>
                  <a href={state.target.link} style={{paddingLeft:'0px',marginLeft:'0px'}}>
                    <span className="target">{ state.target.logoLabel }</span>
                  </a>                  
              </li>
              <li><a href={state.baseUrlStore}>Pling</a></li>
              <li><a href={state.gitlabUrl + "/explore/projects"}>Opencode</a></li>
            </>
          );
        break;
    }
  }else{
    dT =(
      <>
        <li className={cls}>
          <a id="opendesktop-logo" href={state.baseUrl}>
            <img src={state.baseUrl + "/images/system/ocs-logo-rounded-16x16.png"} className="logo" />
            openDesktop.org 
          </a>
        </li>
        <li><a href={state.baseUrlStore}>Pling</a></li>
        <li><a href={state.gitlabUrl + "/explore/projects"}>Opencode</a></li>
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

export default DomainsMenu

