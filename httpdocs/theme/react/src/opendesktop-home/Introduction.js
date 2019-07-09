import React from 'react';

function Introduction(props) {
  return (
    <div className="introduction">
      <h1> Welcome to opendesktop</h1>
      <h4> Opendesktop is community based portal, dedicated to libre content, opencode, free software and operating system.</h4>
      <ul>
        <li><a href="https://opencode.net">Code</a></li>
        <li><a href="https://pling.com">Publish</a></li>
        <li><a href="https://forum.opendesktop.org">Community</a></li>
        <li><a href="https://my.opendesktop.org">Personal</a></li>
      </ul>
    </div>
  )
}

export default Introduction;
