import React from 'react';

function Introduction(props) {
  return (
    <div className="introduction">
      <div className="intro-desc">
        <h1> Welcome to opendesktop.org</h1>
        <h4>
          OpenDesktop is a portal providing 100% libre services for coding, publishing, messenging and personal data storage.
        </h4>
      </div>
      <ul>
        <li>
            <a href="https://opencode.net"><span className="link-code link-image"></span></a>
            <span><a className="link-code-showmore">Show more</a></span>
        </li>
        <li>

            <a href="https://pling.com"><span className="link-publish link-image"></span></a>
            <span><a className="link-publish-showmore">Show more</a></span>

        </li>
        <li>

            <a href="https://forum.opendesktop.org"><span className="link-community link-image"></span></a>
            <span>Show more</span>

        </li>
        <li>

            <a href="https://my.opendesktop.org"><span className="link-personal link-image"></span></a>
            <span>Show more</span>

        </li>
      </ul>
    </div>
  )
}

export default Introduction;
