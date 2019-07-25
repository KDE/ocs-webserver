import React from 'react';
import MyButton from './MyButton';

class DevelopmentAppMenu extends React.Component {
  constructor(props){
    super(props);
    this.state = {
      gitlabLink:this.props.gitlabUrl+"/dashboard/issues?assignee_id="
    };
    this.handleClick = this.handleClick.bind(this);
  }

  componentWillMount() {
    //document.addEventListener('mousedown',this.handleClick, false);
    document.addEventListener('click',this.handleClick, false);
  }

  componentWillUnmount() {
    document.removeEventListener('click',this.handleClick, false);
  }

  componentDidMount() {
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

      // if (this.state.dropdownClass === "open"){
      //   if (e.target.className === "th-icon" || e.target.className === "btn btn-default dropdown-toggle"){
      //     dropdownClass = "";
      //   } else {
      //     dropdownClass = "open";
      //   }
      // } else {
      //   dropdownClass = "open";
      // }
    }
    this.setState({dropdownClass:dropdownClass});
  }

  render(){

    let badgeNot;
    if(this.state.notification)
    {
      badgeNot = (<span className="badge-notification">{this.state.notification_count}</span>);
    }
    let personalMenuDisplay=(
        <React.Fragment>
          <MyButton id="addproduct-link-item" url={this.props.baseUrlStore+"/product/add"} label="Add Product" />
          <MyButton id="addproduct-link-item" url={this.props.baseUrlStore+"/collection/add"} label="Add Collection" />
          <MyButton id="addproduct-link-item" url={this.props.baseUrlStore+"/projects/new"} label="Add Project" />

          <MyButton id="listproduct-link-item" url={this.props.baseUrlStore + "/u/" + this.props.user.username + "/products"} label="Products" />
          <MyButton id="listproduct-link-item" url={this.props.baseUrlStore + "/u/" + this.props.user.username + "/collections"} label="Collections" />
          <MyButton id="opencode-link-item" url={this.props.gitlabUrl+"/dashboard/projects"} label="Projects" />

          <MyButton id="plings-link-item" url={this.props.baseUrlStore + "/u/" + this.props.user.username + "/payout"} label="Payout" />
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
         <MyButton id="messages-link-item"
                 url={this.props.forumUrl+"/u/"+this.props.user.username+"/messages"}
                 label="Messages"
                 badge={badgeNot}
                  />

            {this.props.isAdmin  &&
              <React.Fragment>
                <MyButton id="docs-link-item"
                        url={this.props.docsopendesktopUrl}
                        label="Docs"/>
                <MyButton id="music-link-item"
                        url={this.props.musicopendesktopUrl}
                        label="Music" />
              </React.Fragment>
            }
        </React.Fragment>
        );

    return (
      <li ref={node => this.node = node} id="development-app-menu-container">
        <div className={"user-dropdown " + this.state.dropdownClass}>
          <button
            className="btn btn-default dropdown-toggle" type="button" onClick={this.toggleDropDown}>
            <span className="th-icon"></span>
          </button>
          <ul id="user-context-dropdown" className="dropdown-menu dropdown-menu-right">

              {contextMenuDisplay}
            <li className="section-seperator"></li>  
              {personalMenuDisplay}

          </ul>

        </div>
      </li>
    )
  }
}

export default DevelopmentAppMenu;
