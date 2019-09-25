import React from 'react';
function AboutMenuItems(props)
{
  return (
    <React.Fragment>
      <li><a href={props.blogUrl} target="_blank">Blog</a></li>
      { props.isAdmin &&
        <li><a className="popuppanel"  href={props.baseUrl+"/plings"}>Plings (admin only)</a></li>
      }
      <li><a  href={props.baseUrlStore +"/faq-pling"}>FAQ Pling</a></li>
      <li><a  href={props.baseUrl +"/faq-opencode"}>FAQ Opencode</a></li>
      <li><a  href={props.baseUrl +"/ocs-api"}>API</a></li>
      <li><a  href={props.baseUrl +"/about"}>About</a></li>
    </React.Fragment>
  );
}

export default AboutMenuItems;
