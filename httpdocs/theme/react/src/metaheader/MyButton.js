import React from 'react';
function MyButton(props)
{
  return (
    <li id={props.id}>
      <a href={props.url}>
        <div className="icon"></div>
        <span>{props.label}</span>
      
      </a>
    </li>
  );
}
export default MyButton;
