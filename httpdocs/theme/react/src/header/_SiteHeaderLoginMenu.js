import React from 'react';

class SiteHeaderLoginMenu extends React.Component {
  constructor(props){
  	super(props);
  	this.state = {};
  }
  render(){

    let registerButtonCssClass,
        loginButtonCssClass;

    if (window.location.href.indexOf('/register') > -1){
      registerButtonCssClass = "active";
    }

    if (window.location.href.indexOf('/login') > -1){
      loginButtonCssClass = "active";
    }

    const menuItemCssClass = {
      "borderColor":this.props.template['header-nav-tabs']['border-color'],
      "backgroundColor":this.props.template['header-nav-tabs']['background-color']
    }
    //<li style={menuItemCssClass} className={registerButtonCssClass}><a href={this.props.baseUrl + "/register"}>Register</a></li>
    return (
      <div id="site-header-login-menu">
        <ul>
          <li style={menuItemCssClass} className={loginButtonCssClass}><a href={this.props.baseUrl + "/login" + this.props.redirectString}>Sign in</a></li>
        </ul>
      </div>
    )
  }
}

export default SiteHeaderLoginMenu;
