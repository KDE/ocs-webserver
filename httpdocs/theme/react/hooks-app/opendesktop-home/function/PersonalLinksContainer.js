import React from 'react';
function PersonalLinksContainer(props)
{
  return (
    <ul id="personal-links-container">
      <li id="storage-link-item">
        <a href={props.myopendesktopUrl+'/files'}  >
          <div className="icon"></div>
          <span>Files</span>
        </a>
      </li>
      <li id="calendar-link-item">
        <a href={props.myopendesktopUrl+"/calendar/"} >
          <div className="icon"></div>
          <span>Calendar</span>
        </a>
      </li>
      <li id="contacts-link-item">
        <a href={props.myopendesktopUrl+"/contacts/"} >
          <div className="icon"></div>
          <span>Contacts</span>
        </a>
      </li>

      <li id="messages-link-item">
        <a href={props.forumUrl+"/u/"+props.user.username+"/messages"} >
          <div className="icon"></div>
          <span>DM</span>
        </a>
      </li>

      <li id="music-link-item">
        <a href={props.musicopendesktopUrl}>
          <div className="icon"></div>
          <span>Music</span>
        </a>
      </li>

      { props.user.isAdmin &&
        <React.Fragment>
        <li id="mail-link-item">
          <a href={props.myopendesktopUrl+"/rainloop/"} >
            <div className="icon"></div>
            <span style={{fontStyle:'italic'}}>Mail</span>
          </a>
        </li>      
        <li id="maps-link-item">
          <a href={props.myopendesktopUrl+"/maps/"} >
            <div className="icon"></div>
            <span style={{fontStyle:'italic'}}>Maps</span>
          </a>
        </li>     

        <li id="mastodon-link-item">
          <a href={props.mastodonUrl+'/auth/sign_in'} >
            <div className="icon"></div>
            <span style={{fontStyle:'italic'}}>Social</span>
          </a>
        </li> 
        </React.Fragment>
      }
      <li id="chat-link-item">
        <a href={props.riotUrl} >
          <div className="icon"></div>
          <span>Chat</span>
        </a>
      </li>
      <li id="pling-link-item">
        <a href={props.baseUrlStore+"/u/"+props.user.username+"/products"} >
          <div className="icon"></div>
          <span>Products</span>
        </a>
      </li>
      <li id="opencode-link-item">
        <a href={props.gitlabUrl + "/dashboard/projects"} >
          <div className="icon"></div>
          <span>Projects</span>
        </a>
      </li>
    </ul>
  );
}

export default PersonalLinksContainer;
