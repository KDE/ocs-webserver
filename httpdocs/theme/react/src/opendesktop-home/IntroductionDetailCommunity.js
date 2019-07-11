import React from 'react';
function IntroductionDetailCommunity(props){
  return(
    <div className="detail-community detail">
      <span class="arrow-down"></span>
      <div className="icon-container">
        <a href="https://forum.opendesktop.org/">
          <div className="icon"></div>
        </a>
      </div>
      <div className="description">
            <h2>
              Discourse, Matrix/Riot
            </h2>
            <div>
              <p>
             Join openDesktop community, discuss on Discourse or chat with members on Riot chat and Matrix.
             </p>
            <a href="https://forum.opendesktop.org/"> https://forum.opendesktop.org/ </a>
            <a href="https://chat.opendesktop.org/"> https://chat.opendesktop.org </a>
            </div>
      </div>
    </div>
  )
}

export default IntroductionDetailCommunity;
