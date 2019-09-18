import React, { Component } from 'react';
class Header extends Component {
  constructor(props){
  	super(props);
  }

  render(){

    return(
      <div className="pling-section-header">
          <div className="header-title">
            <span>{this.props.section ? this.props.section.name:''}</span>
          </div>
          <div className="score-container">
            <span>Goal:</span>
            <div className="score-bar-container">
              <div className={"score-bar"} style={{"width":(this.props.amount_factor/this.props.goal)*100 + "%"}}>
                {this.props.amount_factor+(this.props.isAdmin?'('+this.props.amount+')':'')}
              </div>
            </div>
            <span>{ this.props.goal}</span>

          </div>
      </div>
    )
  }
}

export default Header;
