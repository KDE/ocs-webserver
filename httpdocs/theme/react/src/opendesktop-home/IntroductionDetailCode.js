import React from 'react';
function IntroductionDetailCode(props){
  return(
    <div className="detail-code detail">
      <span class="arrow-down"></span>
      <div className="icon-container">
        <a href="https://www.opencode.net/dashboard/projects">
          <div className="icon"></div>
        </a>
      </div>
      <div className="description">
            <h2>
            openCode.net
            </h2>
            <div>
            <p>Develop your projects online for free. Git and CI, powered by Gitlab.</p>            
            <a href="https://www.opencode.net"> www.opencode.net </a>
            </div>
      </div>
    </div>
  )
}

export default IntroductionDetailCode;
