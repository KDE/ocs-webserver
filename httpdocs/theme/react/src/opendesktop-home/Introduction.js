import React from 'react';

function Introduction(props) {
  return (
    <div className="introduction">
      <h1> Welcome to opendesktop</h1>
      <h4> Opendesktop is community based portal, dedicated to libre content, opencode, free software and operating system.</h4>
      <div className="intro-container">
        <div className="code-container sub-container"><a href="https://opencode.net">Code</a></div>
        <div className="pub-container sub-container"><a href="https://opendesktop.org">Publish</a></div>
        <div className="comm-container sub-container"><a href="https://forum.opendesktop.org">Community</a></div>
        <div className="personal-container sub-container"><a href="https://my.opendesktop.org">Personal</a></div>
      </div>

    </div>
  )
}

/*
<ul>
  <li><a href="https://opendesktop.org">Pling.com</a></li>
  <li><a href="https://opencode.net">Opencode</a></li>
  <li><a href="https://forum.opendesktop.org">Discourse</a></li>
  <li><a href="https://chat.opendesktop.org">Riot</a></li>
  <li>Mastodon</li>
  <li><a href="https://my.opendesktop.org">Nextcloud</a></li>
</ul>
*/
export default Introduction;
