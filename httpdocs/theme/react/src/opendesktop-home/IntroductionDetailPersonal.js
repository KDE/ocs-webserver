import React from 'react';
function IntroductionDetailPersonal(props){
  return(
    <div className="detail-personal detail">
      <div className="icon-container">
        <a href="https://cloud.opendesktop.org/">
          <div className="icon"></div>
        </a>
      </div>
      <div className="description">
            <h2>
              Cloud Storage
            </h2>
            <div>
             Cloud service
            <p>Powered by nextcloud.</p>
            <a href="https://cloud.opendesktop.org/"> https://cloud.opendesktop.org/ </a>
            </div>
      </div>
    </div>
  )
}

export default IntroductionDetailPersonal;
