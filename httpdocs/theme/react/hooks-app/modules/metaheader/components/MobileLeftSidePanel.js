import React,{useContext} from 'react';
import AboutMenuItems from './function/AboutMenuItems';
import CommunityMenuItems from './function/CommunityMenuItems';
import {MetaheaderContext} from '../contexts/MetaheaderContext';
const MobileLeftSidePanel = () => {
  const {state} = useContext(MetaheaderContext);
  
  return (
    <div id="left-side-panel">
        <div id="panel-header">
          <a href={state.baseUrl}>
            <img src={state.baseUrl + "/images/system/opendesktop-logo.png"} className="logo"/>
            openDesktop.org
          </a>
        </div>
        <div id="panel-menu">
          <ul>
            <li><a href={state.baseUrlStore}>Pling</a></li>
            <li><a href={state.gitlabUrl}>Opencode</a></li>
            <li>
              <a className="groupname"><b>Community</b></a>
              <ul>
                <CommunityMenuItems />
              </ul>
            </li>

            <li>
              <a className="groupname"><b>About</b></a>
              <ul>
                <AboutMenuItems />
              </ul>
            </li>

            <li><a href={state.riotUrl}>Chat</a></li>
          </ul>
        </div>
      </div>
  )
}

export default MobileLeftSidePanel

