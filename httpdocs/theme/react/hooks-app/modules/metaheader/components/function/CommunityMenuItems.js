import React,{useContext} from 'react';
import {MetaheaderContext} from '../../contexts/MetaheaderContext';
function CommunityMenuItems()
{
  const {state} = useContext(MetaheaderContext);
  
  return (
    <React.Fragment>
    <li><a href={state.baseUrlStore + "/community"}>Community</a></li> 
    <li><a href={state.forumUrl}>Discussion</a></li>
    <li><a href={state.baseUrlStore + "/supporters"}>Community Funding</a></li>    
    <li><a href={state.baseUrlStore + "/support"}>Become a Supporter</a></li>
    </React.Fragment>
  );
}

export default CommunityMenuItems;
