import React from 'react';
function WhereAmI(props)
{
  return (
    <div id="whereami"><div className={props.target.logo + " icon"}></div><span>{props.target.logoLabel}</span></div>
  );
}

export default WhereAmI;
