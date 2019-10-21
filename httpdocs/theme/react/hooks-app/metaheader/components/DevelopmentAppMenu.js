import React from 'react';
import MyButton from './function/MyButton';

class DevelopmentAppMenu extends React.Component {
  constructor(props){
    super(props);
    this.state = {
      gitlabLink:this.props.gitlabUrl+"/dashboard/issues?assignee_id="
    };
    this.handleClick = this.handleClick.bind(this);
    this.loadNotification = this.loadNotification.bind(this);
  }

  componentWillMount() {
    //document.addEventListener('mousedown',this.handleClick, false);
    document.addEventListener('click',this.handleClick, false);
  }

  componentWillUnmount() {
    document.removeEventListener('click',this.handleClick, false);
  }

  componentDidMount() {
    this.loadNotification();
    const self = this;
    const xhttp = new XMLHttpRequest();
    xhttp.onreadystatechange = function() {
      if (this.readyState === 4 && this.status === 200) {
        const res = JSON.parse(this.response);
        const gitlabLink = self.state.gitlabLink + res[0].id;
        self.setState({gitlabLink:gitlabLink,loading:false});
      }
    };
    xhttp.open("GET", this.props.gitlabUrl+"/api/v4/users?username="+this.props.user.username, true);
    xhttp.send();
  }

  handleClick(e){
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

  loadNotification(){
    if(this.props.user){
      let url = this.props.baseUrl+'/membersetting/notification';
      fetch(url,{
                 mode: 'cors',
                 credentials: 'include'
                 })
      .then(response => response.json())
      .then(data => {
          if(data.notifications){
            const nots = data.notifications.filter(note => note.read==false);
            if(nots.length>0 && this.state.notification_count !== nots.length)
            {
                this.setState(prevState => ({ notification: true, notification_count:nots.length }))
            }
          }
       });
     }
  }

  render(){

    

    let badgeNot;
    if(this.state.notification)
    {
      badgeNot = (<span className="badge-notification">{this.state.notification_count}</span>);
    }


    let personalMenuDisplay=(
        <React.Fragment>
          {/*
          <MyButton id="addproduct-link-item" url={this.props.baseUrlStore+"/product/add"} label="Add Product" />
          <MyButton id="addproduct-link-item" url={this.props.baseUrlStore+"/collection/add"} label="Add Collection" />
          <MyButton id="addproduct-link-item" url={this.props.baseUrlStore+"/projects/new"} label="Add Project" />
            */}
          <MyButton id="listproduct-link-item" url={this.props.baseUrlStore + "/u/" + this.props.user.username + "/products"} label="Products" />
          {/*
            <MyButton id="listproduct-link-item" url={this.props.baseUrlStore + "/u/" + this.props.user.username + "/collections"} label="Collections" />
            */}
          <MyButton id="opencode-link-item" url={this.props.gitlabUrl+"/dashboard/projects"} label="Projects" />
          <MyButton id="issues-link-item" url={this.state.gitlabLink} label="Issues" />

        </React.Fragment>
    );

    let contextMenuDisplay = (
        <React.Fragment>
         <MyButton id="storage-link-item"
                 url={this.props.myopendesktopUrl}
                 label="Storage" />
         <MyButton id="calendar-link-item"
                 url={this.props.myopendesktopUrl+"/index.php/apps/calendar/"}
                 label="Calendar" />
         <MyButton id="contacts-link-item"
                 url={this.props.myopendesktopUrl+"/index.php/apps/contacts/"}
                 label="Contacts" />
          <li id="messages-link-item">
              <a href={this.props.forumUrl+"/u/"+this.props.user.username+"/messages"}>
                <div className="icon"></div>
                <span>Messages</span>
                  {badgeNot}
              </a>
          </li>
            {this.props.isAdmin  &&
              <React.Fragment>
                <MyButton id="docs-link-item"
                        url={this.props.docsopendesktopUrl}
                        label="Docs"/>
              </React.Fragment>
            }
          <MyButton id="music-link-item"
                        url={this.props.musicopendesktopUrl}
                        label="Music" />
                      {/*
                        <MyButton id="plings-link-item" url={this.props.baseUrlStore + "/u/" + this.props.user.username + "/payout"} label="Payout" />
                        */}

        </React.Fragment>
        );

    return (
      <li ref={node => this.node = node} id="development-app-menu-container">
        <div className={"user-dropdown " + this.state.dropdownClass}>
          <button
            id="developmentDropdownBtn"
            className="btn btn-default dropdown-toggle" type="button" onClick={this.toggleDropDown}>
            <span className="th-icon"></span>
            {badgeNot}
          </button>
          <ul id="user-context-dropdown" className="dropdown-menu dropdown-menu-right">
              {personalMenuDisplay}
            <li className="section-seperator"></li>
              {contextMenuDisplay}

          </ul>

        </div>
      </li>
    )
  }
}

export default DevelopmentAppMenu;
