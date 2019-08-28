import React from 'react';
function DownloadSection(props)
{
  return (
    <div className="user-pling-section-container">
        <div className="title">Download counter this month</div>
        { props.section.map((mg,i) => (
            <div className="section"><div className="section-name">{mg.name}:</div><div className="section-value"> {mg.dls}</div></div>
          ))
        }
    </div>
  );
}
export default DownloadSection;
