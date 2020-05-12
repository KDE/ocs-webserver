import React from 'react';
import Creator from './Creator';

const TopCreators = (props) => {
  return (
    <div className="panelContainer">
         <div className="title">Most Plinged Creators</div>
         <ul>{props.creators.map((creator,index) => 
                  <li key={index}>
                    <Creator creator={creator} baseUrlStore={props.baseUrlStore} isAdmin={props.isAdmin}/>
                  </li>
          )}</ul>
       </div>
  )
}
export default TopCreators
