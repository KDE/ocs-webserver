import React from 'react';
function PersonalLinksContainer(props)
{
  return (
    <ul id="personal-links-container">
      <li id="storage-link-item">
        <a href={props.myopendesktopUrl+'/apps/files'}  >
          <div className="icon"></div>
          <span>Files</span>
        </a>
      </li>
      <li id="calendar-link-item">
        <a href={props.myopendesktopUrl+"/apps/calendar/"} >
          <div className="icon"></div>
          <span>Calendar</span>
        </a>
      </li>
      <li id="contacts-link-item">
        <a href={props.myopendesktopUrl+"/apps/contacts/"} >
          <div className="icon"></div>
          <span>Contacts</span>
        </a>
      </li>

      <li id="messages-link-item">
        <a href={props.forumUrl+"/u/"+props.user.username+"/messages"} >
          <div className="icon"></div>
          <span>Messages</span>
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
          <a href={props.myopendesktopUrl+"/index.php/apps/rainloop/"} >
            <div className="icon"></div>
            <span>Mail</span>
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
    </ul>
  );
}

export default PersonalLinksContainer;
