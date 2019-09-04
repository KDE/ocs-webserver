import React, { Component } from 'react';

class Support extends React.Component {
  render(){

    return (
      <div className="support-container">
        <div className="tier-container">
          <a href={this.props.baseUrlStore+'/support-predefined'}>Join $0.99 Tier</a>
        </div>
      </div>
    )
  }
}

export default Support;
