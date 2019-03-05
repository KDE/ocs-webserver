import React from 'react';
function SwitchItem(props){
  return(
    <div>
     <label className="switch">
     <input type="checkbox" checked={props.onSwitchStyleChecked} onChange={props.onSwitchStyle}/>
     <span className="slider round"></span>
     </label>
    </div>
  )
}

export default SwitchItem;
