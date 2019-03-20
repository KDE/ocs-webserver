import React from 'react';

function Introduction(props) {
  return (
    <div className="introduction">
      <h1> Welcome to opendesktop</h1>
      <h4> Opendesktop is community based portal, dedicated to libre content, opencode, free software and operating system.</h4>
      <ul>
        <li><a href="https://opendesktop.org">Pling.com</a></li>
        <li><a href="https://git.opendesktop.org">Opencode</a></li>
        <li><a href="https://forum.opendesktop.org">Discourse</a></li>
        <li><a href="https://chat.opendesktop.org">Riot</a></li>
        <li>Mastodon</li>
        <li><a href="https://my.opendesktop.org">Nextcloud</a></li>
      </ul>
    </div>
  )
}

export default Introduction;
