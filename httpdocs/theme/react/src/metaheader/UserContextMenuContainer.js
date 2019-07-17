import React from 'react';
class UserContextMenuContainer extends React.Component {
  constructor(props){
    super(props);
    this.state = {
      /*gitlabLink:this.props.gitlabUrl+"/dashboard/issues?assignee_id="*/
    };
    this.handleClick = this.handleClick.bind(this);
  }

  componentWillMount() {
    //document.addEventListener('mousedown',this.handleClick, false);
    document.addEventListener('click',this.handleClick,false);
  }

  componentWillUnmount() {
    document.removeEventListener('click',this.handleClick, false);
  }

  componentDidMount() {
    /*const self = this;
    const xhttp = new XMLHttpRequest();
    xhttp.onreadystatechange = function() {
      if (this.readyState === 4 && this.status === 200) {
        const res = JSON.parse(this.response);
        const gitlabLink = self.state.gitlabLink + res[0].id;
        self.setState({gitlabLink:gitlabLink,loading:false});
      }
    };
    xhttp.open("GET", this.props.gitlabUrl+"/api/v4/users?username="+this.props.user.username, true);
    xhttp.send();*/
  }

  handleClick(e){
    // let dropdownClass = "";
    // if (this.node.contains(e.target)){
    //   if (this.state.dropdownClass === "open"){
    //     if (e.target.className === "th-icon" || e.target.className === "btn btn-default dropdown-toggle"){
    //       dropdownClass = "";
    //     } else {
    //       dropdownClass = "open";
    //     }
    //   } else {
    //     dropdownClass = "open";
    //   }
    // }
    // this.setState({dropdownClass:dropdownClass});
    let dropdownClass = "";
    if (this.node.contains(e.target)){
      if(e.target.className === "btn btn-default dropdown-toggle"
          || e.target.className === "th-icon")
      {
        // only btn click open dropdown
        if (this.state.dropdownClass === "open"){
          dropdownClass = "";
        }else{
          dropdownClass = "open";
        }
      }else{
        dropdownClass = "";
      }
    }
    this.setState({dropdownClass:dropdownClass});
  }

  render(){

    /*
    // BU CODE
    <li id="chat-link-item">
      <a href={"https://chat.opendesktop." + urlEnding}>
        <div className="icon"></div>
        <span>Chat</span>
      </a>
    </li>
    */
    
    let urlEnding;
    if(this.props.baseUrl.endsWith("cc"))
    {
      urlEnding = "cc";
    }else if(this.props.baseUrl.endsWith("com")){
      urlEnding = "com";
    }else{
      urlEnding = "com";
    }

    let contextMenuDisplay;
    if (this.props.isAdmin){
      contextMenuDisplay = (
        <ul id="user-context-dropdown" className="dropdown-menu dropdown-menu-right">

          <li id="messages-link-item">
            <a href={this.props.forumUrl+"/u/"+this.props.user.username+"/messages"}>
              <div className="icon"></div>
              <span>Messages</span>
            </a>
          </li>
          <li id="contacts-link-item">
            <a href={"https://cloud.opendesktop." + urlEnding + "/index.php/apps/contacts/"}>
              <div className="icon"></div>
              <span>Contacts</span>
            </a>
          </li>

          <li id="storage-link-item">
            <a href={"https://cloud.opendesktop." + urlEnding}>
              <div className="icon"></div>
              <span>Storage</span>
            </a>
          </li>
          <li id="docs-link-item">
            <a href={"https://docs.opendesktop." + urlEnding}>
              <div className="icon"></div>
              <span>Docs</span>
            </a>
          </li>

          <li id="calendar-link-item">
            <a href={"https://cloud.opendesktop." + urlEnding + "/index.php/apps/calendar/"}>
              <div className="icon"></div>
              <span>Calendar</span>
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
        <ul id="user-context-dropdown" className="dropdown-menu dropdown-menu-right">
          <li id="messages-link-item" >
            <a href={this.props.forumUrl+"/u/"+this.props.user.username+"/messages"}>
              <div className="icon"></div>
              <span>Messages</span>
            </a>
          </li>

        </ul>
      );
    }

    return (
      <li ref={node => this.node = node} id="user-context-menu-container">
        <div className={"user-dropdown " + this.state.dropdownClass}>
          <button
            className="btn btn-default dropdown-toggle" type="button" onClick={this.toggleDropDown}>
            <span className="th-icon"></span>
          </button>
          {contextMenuDisplay}
        </div>
      </li>
    )
  }
}

export default UserContextMenuContainer;
