import React from 'react';
import SwitchItem from './SwitchItem';
class UserLoginMenuContainer extends React.Component {
  constructor(props){
    super(props);
    this.state = {};
    this.handleClick = this.handleClick.bind(this);
    this.loadNotification = this.loadNotification.bind(this);
  }

  componentWillMount() {
    document.addEventListener('click',this.handleClick, false);
  }

  componentWillUnmount() {
    document.removeEventListener('click',this.handleClick, false);
  }

  componentDidMount(){
    this.loadNotification();
   }

  loadNotification(){
    if(this.props.user){
      let url = this.props.baseUrl+'/membersetting/notification';
      fetch(url,{
                 mode: 'cors',
                 credentials: 'include'
                 })
      .then(response => response.json())
      .then(data => {
          const nots = data.notifications.filter(note => note.read==false);
          if(nots.length>0 && this.state.notification_count !== nots.length)
          {
              this.setState(prevState => ({ notification: true, notification_count:nots.length }))
          }
       });
     }
  }

  handleClick(e){
    let dropdownClass = "";
    if (this.node.contains(e.target)){
      if (this.state.dropdownClass === "open"){
        if (e.target.className === "th-icon" || e.target.className === "btn btn-default dropdown-toggle"){
          dropdownClass = "";
        } else {
          dropdownClass = "open";
        }
      } else {
        dropdownClass = "open";
      }
    }
    this.setState({dropdownClass:dropdownClass});

  }

  render(){
    const theme = this.props.onSwitchStyleChecked?"Metaheader theme dark":"Metaheader theme light";
    let badgeNot;
    if(this.state.notification)
    {
      badgeNot = (<span className="badge-notification">{this.state.notification_count}</span>);
    }
    let urlEnding;
    if(this.props.baseUrl.endsWith("cc"))
    {
      urlEnding = "cc";
    }else{
      urlEnding = "org";
    }

    let contextMenuDisplay;
    if (this.props.isAdmin){
      contextMenuDisplay = (
        <ul className="user-context-menu-container">

          <li id="messages-link-item">
            <a href={this.props.forumUrl+"/u/"+this.props.user.username+"/messages"}>
              <div className="icon"></div>
              <span>Messages</span>
              {badgeNot}
            </a>
          </li>
          <li id="storage-link-item">
            <a href={"https://my.opendesktop." + urlEnding}>
              <div className="icon"></div>
              <span>Storage</span>
            </a>
          </li>
          <li id="contacts-link-item">
            <a href={"https://my.opendesktop." + urlEnding + "/index.php/apps/contacts/"}>
              <div className="icon"></div>
              <span>Contacts</span>
            </a>
          </li>
          <li id="calendar-link-item">
            <a href={"https://my.opendesktop." + urlEnding + "/index.php/apps/calendar/"}>
              <div className="icon"></div>
              <span>Calendar</span>
            </a>
          </li>
          <li id="docs-link-item">
            <a href={"https://docs.opendesktop." + urlEnding}>
              <div className="icon"></div>
              <span>Docs</span>
            </a>
          </li>


          <li id="music-link-item">
            <a href={"https://music.opendesktop." + urlEnding}>
              <div className="icon"></div>
              <span>Music</span>
            </a>
          </li>

        </ul>
      );
    } else {
      contextMenuDisplay = (
        <ul  className="user-context-menu-container">
          <li id="messages-link-item" >
            <a href={this.props.forumUrl+"/u/"+this.props.user.username+"/messages"}>
              <div className="icon"></div>
              <span>Messages</span>
              {badgeNot}
            </a>
          </li>

          <li id="storage-link-item">
            <a href={"https://my.opendesktop." + urlEnding}>
              <div className="icon"></div>
              <span>Storage</span>
            </a>
          </li>
          <li id="contacts-link-item">
            <a href={"https://my.opendesktop." + urlEnding + "/index.php/apps/contacts/"}>
              <div className="icon"></div>
              <span>Contacts</span>
            </a>
          </li>
          <li id="calendar-link-item">
            <a href={"https://my.opendesktop." + urlEnding + "/index.php/apps/calendar/"}>
              <div className="icon"></div>
              <span>Calendar</span>
            </a>
          </li>

        </ul>
      );
    }

    return (
      <li id="user-login-menu-container" ref={node => this.node = node}>
        <div className={"user-dropdown " + this.state.dropdownClass}>
          <button
            className="btn btn-default dropdown-toggle"
            type="button"
            id="userLoginDropdown">
            <img className="th-icon" src={this.props.user.avatar}/>
            {badgeNot}
          </button>
          <ul className="dropdown-menu dropdown-menu-right">
            <li id="user-info-menu-item">
              <div id="user-info-section">
                <div className="user-avatar">
                  <div className="no-avatar-user-letter">
                    <img src={this.props.user.avatar}/>
                  </div>
                </div>
                <div className="user-details">
                  <ul>
                    <li id="user-details-username"><b>{this.props.user.username}</b></li>
                    <li id="user-details-email">{this.props.user.mail}</li>
                  </ul>
                </div>
              </div>
            </li>
            <li className="user-context-menu">
              {contextMenuDisplay}
            </li>
            <li className="user-settings-item">
             <span className="user-settings-item-title">Metaheader theme light</span>
               <SwitchItem onSwitchStyle={this.props.onSwitchStyle}
                        onSwitchStyleChecked={this.props.onSwitchStyleChecked}/>
              <span className="user-settings-item-title">dark</span>
            </li>
            <li className="buttons">
              <a href={this.props.baseUrl + "/settings/"} className="btn btn-default btn-metaheader"><span>Settings</span></a>
              <a href={this.props.baseUrl + "/settings/profile"} className="btn btn-default btn-metaheader"><span>Profile</span></a>
              <a href={this.props.logoutUrl} className="btn btn-default pull-right btn-metaheader"><span>Logout</span></a>
            </li>

          </ul>
        </div>
      </li>
    )
  }
}

export default UserLoginMenuContainer;
