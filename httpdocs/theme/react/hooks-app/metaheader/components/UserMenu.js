import React,{useContext} from 'react';
import UserLoginMenuContainer from './UserLoginMenuContainer';
import DevelopmentAppMenu from './DevelopmentAppMenu';
import AboutMenu from './AboutMenu';
import DiscussionBoardsDropDownMenu from './DiscussionBoardsDropDownMenu';
import {MetaheaderContext} from '../contexts/MetaheaderContext';

const UserMenu = (props) => {
  const {state} = useContext(MetaheaderContext);
  let userDropdownDisplay, developmentAppMenuDisplay;
  if (state.user && state.user.member_id) {
    userDropdownDisplay = (
      <UserLoginMenuContainer        
        onSwitchStyle={props.onSwitchStyle}
        onSwitchStyleChecked={props.onSwitchStyleChecked}
        onSwitchMetaHeaderStyle={props.onSwitchMetaHeaderStyle}
        siteTheme={siteTheme}
        metamenuTheme={metamenuTheme}
      />
    );


    developmentAppMenuDisplay = (
      <DevelopmentAppMenu />       
    );
  } else {
    userDropdownDisplay = (
      <React.Fragment>
        <li id="user-register-container"><a href={state.baseUrl + "/register"}>Register</a> or</li>
        <li id="user-login-container"><a href={state.loginUrl} className="btn btn-metaheader">Login</a></li>
      </React.Fragment>
    )
  }

  let chatItem = (<li id="chat-link-item"><a href={state.riotUrl}>
    <img src={state.baseUrl + "/theme/react/assets/img/chat.png"} className="riotIcon"></img>Chat
    </a></li>);


  let userMenuContainerDisplay;
  if (props.device === "large") {


    userMenuContainerDisplay = (
      <ul className="metaheader-menu right" id="user-menu">
        <DiscussionBoardsDropDownMenu />
        <AboutMenu />
        {chatItem}
        {developmentAppMenuDisplay}
        {userDropdownDisplay}
      </ul>
    );
  } else {
    userMenuContainerDisplay = (
      <ul className="metaheader-menu right" id="user-menu">
        {props.device === "mid" && chatItem}
        {developmentAppMenuDisplay}
        {userDropdownDisplay}
      </ul>
    );
  }

  return (
    <>
      {userMenuContainerDisplay}
    </>
  )
}

export default UserMenu

