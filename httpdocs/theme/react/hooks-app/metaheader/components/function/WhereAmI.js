import React from 'react';
function WhereAmI(props)
{
  return (
    <div id="whereami">
      <a href={props.target.link} >
        <div className={props.target.logo + " icon"}></div>
        <span>{props.target.logoLabel}</span>
      </a>
    </div>
  );
}

export default WhereAmI;
