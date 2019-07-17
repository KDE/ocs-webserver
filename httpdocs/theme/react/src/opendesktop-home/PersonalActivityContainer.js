import React, { Component } from 'react';
import TimeAgo from 'react-timeago';

class PersonalActivityContainer extends React.Component {
  render(){
    return (
      <div className="personal-activity-container">
         personal activity {this.props.user}
      </div>
    )
  }
}

export default PersonalActivityContainer;
