import React from 'react';
import UserTabs from './UserTabs';
class UserLoginMenuContainerVersionTwo extends React.Component {
  constructor(props){
    super(props);
    this.state = {};
    this.handleClick = this.handleClick.bind(this);
  }

  componentWillMount() {
    document.addEventListener('mousedown',this.handleClick, false);
  }

  componentWillUnmount() {
    document.removeEventListener('mousedown',this.handleClick, false);
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
    this.setState({dropdownClass:dropdownClass},function(){
      if (dropdownClass === "open"){
        let el = document.body;
        el.classList.add('drawer-open');
      } else {
        let el = document.body;
        el.classList.remove('drawer-open');

      }
    });
  }


  render(){
    return (
      <li id="user-login-menu-container" ref={node => this.node = node}>
        <div className={"user-dropdown " + this.state.dropdownClass}>
          <button
            className="btn btn-default dropdown-toggle"
            type="button"
            id="userLoginDropdown">
            <img className="th-icon" src={this.props.user.avatar}/>
          </button>
          <div id="background-overlay">
            <ul id="right-panel" className="dropdown-menu dropdown-menu-right">
              <li id="user-info-menu-item">
                <div id="user-info-section">
                  <div className="user-avatar">
                    <div className="no-avatar-user-letter">
                      <img src={this.props.user.avatar}/>
                    </div>
                  </div>
                  <div className="user-details">
                    <ul>
                      <li id="user-details-username"><h2>{this.props.user.username}</h2></li>
                      <li id="user-details-email">{this.props.user.mail}</li>
                      <li className="buttons">
                        <a href={this.props.baseUrl + "/settings/"} className="btn btn-default btn-metaheader"><span>Settings</span></a>
                        <a href={this.props.logoutUrl} className="btn btn-default pull-right btn-metaheader"><span>Logout</span></a>
                      </li>
                    </ul>
                  </div>
                </div>
              </li>
              <li id="user-tabs-menu-item">
                <UserTabs user={this.props.user}/>
              </li>
            </ul>
          </div>
        </div>
      </li>
    )
  }
}

export default UserLoginMenuContainerVersionTwo;
