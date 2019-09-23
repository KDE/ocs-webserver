import React from 'react';
function Header(props){
  return(
    <div className="pling-section-header">
        <div className="header-title">
          <span>{props.section ? props.section.name:''}</span>
        </div>
        <div className="score-container">
          <span>Goal:</span> 
          <div className="score-bar-container">
            <div className={"score-bar"} style={{"width": (props.amount_factor/props.goal*100)+ "%"}}>{props.amount_factor}</div>
          </div>
          <span>{ props.goal}</span>

        </div>
    </div>
  )
}

export default Header;
