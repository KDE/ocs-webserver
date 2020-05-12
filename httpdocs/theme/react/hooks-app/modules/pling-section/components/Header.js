import React from 'react';
import {filterDuplicated} from './util';
const Header = (props) => {
  const s = props.supporters.filter(filterDuplicated).length;  
  let goal = Math.ceil((s/50))*50;
  if(goal==0){goal = 50;}
  return (
      <div className="pling-section-header">
          <div className="header-title">
            <span>{props.section ? props.section.name:''}</span>
          </div>
          <div className="score-container">
            <span>Goal:</span>
            <div className="score-bar-container">
              <div className={"score-bar"} style={{"width":(s/goal)*100 + "%"}}>
                {s}
              </div>
            </div>
            <span>{goal}</span>
          </div>
      </div>
  )
}
export default Header

