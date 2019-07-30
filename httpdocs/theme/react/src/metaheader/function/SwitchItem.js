import React from 'react';
function SwitchItem(props){
  return(
    <div className="switchContainer">
     <label className="switch">
     <input type="checkbox" defaultChecked={props.onSwitchStyleChecked} onChange={props.onSwitchStyle}/>
     <span className="slider round"></span>
     </label>
    </div>
  )
}

export default SwitchItem;
