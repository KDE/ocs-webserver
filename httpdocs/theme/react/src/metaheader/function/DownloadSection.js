import React from 'react';
function DownloadSection(props)
{
  return (
    <div className="user-pling-section-container">
        <div className="title">Download Sections</div>
        { props.section.map((mg,i) => (
            <div className="section">{mg.name}: {mg.dls}</div>
          ))
        }
    </div>
  );
}
export default DownloadSection;
