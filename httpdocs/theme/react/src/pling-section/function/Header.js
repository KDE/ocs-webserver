import React from 'react';
function Header(props){
  return(
    <div className="pling-section-header">
        <div className="header-title">
          <span>{props.section ? props.section.name:''}</span>
        </div>
        <div className="score-container">
          <div className="score-bar-container">
            <div className={"score-bar"} style={{"width": (props.amount/props.goal*100)+ "%"}}>{props.amount}</div>
          </div>
          <div>
            Goal:{ props.goal}
          </div>
        </div>
    </div>
  )
}

export default Header;
