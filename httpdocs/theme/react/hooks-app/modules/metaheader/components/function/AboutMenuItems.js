import React , {useContext} from 'react'
import {MetaheaderContext} from '../../contexts/MetaheaderContext';

function AboutMenuItems()
{
  const {state} = useContext(MetaheaderContext);
  return (
    <React.Fragment>
      <li><a href={state.blogUrl} target="_blank">Blog</a></li>
      { state.isAdmin &&
        <li><a href={state.baseUrl+"/plings"}>Plings (admin only)</a></li>
      }
      <li><a  href={state.baseUrlStore +"/faq-pling"}>FAQ Pling</a></li>
      <li><a  href={state.baseUrl +"/faq-opencode"}>FAQ Opencode</a></li>
      <li><a  href={state.baseUrl +"/ocs-api"}>API</a></li>
      <li><a  href={state.baseUrl +"/about"}>About</a></li>
    </React.Fragment>
  );
}

export default AboutMenuItems;
