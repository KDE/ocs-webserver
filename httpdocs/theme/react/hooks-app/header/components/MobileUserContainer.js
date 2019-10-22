import React from 'react';
import SiteHeaderLoginMenu from './SiteHeaderLoginMenu';
class MobileUserContainer extends React.Component {
  constructor(props){
  	super(props);
  	this.state = {};
  }

  render(){

    let userDisplay;
    if (this.props.user){
      // userDisplay = (
      //   <SiteHeaderUserMenu
      //     serverUrl={this.state.serverUrl}
      //     baseUrl={this.state.baseUrl}
      //     user={this.props.user}
      //   />
      // );
    } else {
      userDisplay = (
        <SiteHeaderLoginMenu
          user={this.props.user}
          baseUrl={this.props.baseUrl}
          template={this.props.template}
          redirectString={this.props.redirectString}
        />
      );
    }

    return (
      <div id="mobile-user-container">
        {userDisplay}
      </div>
    )
  }
}

export default MobileUserContainer;
