import React from 'react';
function AdminlLinksContainer(props)
{
  return (
        <ul id="admin-links-container">
         

          <li id="backend-link-item">
            <a href={props.baseUrlStore+"/backend"} >
              <span>Backend</span>
            </a>
          </li>
       
          <li id="piwik-link-item">
            <a href={"https://piwik.opendesktop.org"} >
              <span>Piwik</span>
            </a>
          </li>

          <li id="Nagios-link-item">
            <a href={"http://nagios.opendesktop.org/nagios"} >
              <span>Nagios</span>
            </a>
          </li>
        </ul>
  );
}

export default AdminlLinksContainer;
