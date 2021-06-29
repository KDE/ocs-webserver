import React from 'react';
function IntroductionDetailPersonal(props){
  return(
    <div className="detail-personal detail">
      <span className="arrow-down"></span>
      <div className="icon-container">
        <a href="https://cloud.opendesktop.org/">
          <div className="icon"></div>
        </a>
      </div>
      <div className="description">
            <h2>
              Nextcloud
            </h2>
            <div>
             <p>Nextcloud offers file storage, contacts, calendar and online Office document editing technology. Keep your data private.
              </p>
            <a href="https://my.opendesktop.org"> https://my.opendesktop.org </a>
            </div>
      </div>
    </div>
  )
}

export default IntroductionDetailPersonal;
