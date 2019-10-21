import React from 'react';
function CommunityMenuItems(props)
{
  return (
    <React.Fragment>
    <li><a href={props.baseUrl + "/community"}>Members</a></li>
    <li><a href={props.forumUrl}>Discussion</a></li>
    <li><a href={props.baseUrlStore + "/support"}>Become a Supporter</a></li>
    </React.Fragment>
  );
}

export default CommunityMenuItems;
