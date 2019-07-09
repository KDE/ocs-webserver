import React from 'react';

function Introduction(props) {
  return (
    <div className="introduction">
      <h1> Welcome to opendesktop.org</h1>
      <h4>      
      OpenDesktop is providing 100% libre services for coding, publishing, messenging and personal data storage for its community.
  </h4>
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
