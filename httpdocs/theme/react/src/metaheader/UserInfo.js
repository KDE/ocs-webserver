import React from 'react';
function UserInfo(props){
  return(
    <div className="userinfo">
      <div className="header">{props.userinfo.username} {props.userinfo.countrycity}</div>
      <div className="statistic">
        <div className="statisticRow"><span className="statisticRowTitle">{props.userinfo.cntProjects} </span> products </div>
        <div className="statisticRow"><span className="statisticRowTitle">{props.userinfo.totalComments} </span> comments </div>
        <div className="statisticRow">Likes <span className="statisticRowTitle">{props.userinfo.cntLikesGave} </span> products </div>
        <div className="statisticRow">Got <span className="statisticRowTitle">{props.userinfo.cntLikesGot} </span>Likes </div>
        <div className="statisticRow">Last time active :{props.userinfo.lastactive_at}  </div>
        <div className="statisticRow">Member since : {props.userinfo.created_at}  </div>
      </div>
    </div>
  )
}
export default UserInfo;
