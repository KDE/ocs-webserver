import React from 'react';
import SwitchItem from './function/SwitchItem';

class UserLoginMenuContainer extends React.Component {
  constructor(props){
    super(props);
    //this.state = {};
    this.state = {
      gitlabLink:this.props.gitlabUrl+"/dashboard/issues?assignee_id="
    };
    this.handleClick = this.handleClick.bind(this);


  }

  componentWillMount() {
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

    let downloadSection;
    if (this.state.section){
      downloadSection = <DownloadSection section={this.state.section}/>
    }

    return (
      <li id="user-login-menu-container" ref={node => this.node = node}>
        <div className={"user-dropdown " + this.state.dropdownClass}>
          <button
            className="btn btn-default dropdown-toggle"
            type="button"
            id="userLoginDropdown">
            <img className="th-icon" src={this.props.user.avatar}/>
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

            <li id="user-menu" className="buttons user-info-payout">
                <ul className="payout">
                  <li><a href={this.props.baseUrlStore + "/u/" + this.props.user.username + "/products"}>Products</a></li>
                  <li><a href={this.props.gitlabUrl+"/dashboard/projects"}>Projects</a></li>
                  <li><a href={this.state.gitlabLink}>Issues</a></li>
                </ul>
            </li>

            <li id="user-info-payout" className="buttons user-info-payout">
                <ul className="payout">
                  <li><a href={this.props.baseUrlStore+'/u/'+this.props.user.username+'/payout'}>My Payout</a></li>
                  <li><a href={this.props.baseUrlStore+'/u/'+this.props.user.username+'/funding'}>My Funding</a></li>
                </ul>
            </li>

            <li className="user-settings-item">
             <span className="user-settings-item-title">Metaheader</span>
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
