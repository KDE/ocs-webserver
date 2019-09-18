import React from 'react';

class SiteHeaderUserMenu extends React.Component {
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
        if (e.target.className === "profile-menu-toggle" ||
            e.target.className === "profile-menu-image" || 
            e.target.className === "profile-menu-username"){
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

    return (
      <ul id="site-header-user-menu-container">
        <li ref={node => this.node = node} id="user-menu-toggle" className={this.state.dropdownClass}>
          <a className="profile-menu-toggle">
            <img className="profile-menu-image" src={window.json_member_avatar}/>
            <span className="profile-menu-username">{this.props.user.username}</span>
          </a>
          <ul id="user-profile-menu" >
            <div className="dropdown-header"></div>
            <li><a href={window.json_baseurl + "product/add"}>Add Product</a></li>
            <li><a href={window.json_baseurl + "u/" + this.props.user.username + "/products"}>Products</a></li>
            <li><a href={window.json_baseurl + "u/" + this.props.user.username + "/payout"}>Payout</a></li>
            <li><a href={window.json_baseurl + "settings"}>Settings</a></li>
            <li><a href={window.json_logouturl}>Logout</a></li>
          </ul>
        </li>
      </ul>
    )
  }
}
export default SiteHeaderUserMenu;
