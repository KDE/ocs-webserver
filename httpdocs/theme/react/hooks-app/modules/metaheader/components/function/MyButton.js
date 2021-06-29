import React from 'react';
function MyButton(props)
{
  let style={}; 
  if(props.fontStyleItalic)
  {
    style={fontStyle:'italic'};
  }
  return (
    <li id={props.id} style={style}>
      <a href={props.url}>
        <div className="icon"></div>
        <span>{props.label}</span>
      </a>
    </li>
  );
}
export default MyButton;
