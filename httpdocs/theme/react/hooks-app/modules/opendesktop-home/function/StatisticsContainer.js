import React from 'react';
function StatisticsContainer(props)
{
  return (
        <ul id="admin-links-container">          
          <li id="stati-link-item">
            <a href={props.baseUrlStore+"/statistics"} >
              <span>Statistics</span>
            </a>
          </li>         
        </ul>
  );
}

export default StatisticsContainer;
