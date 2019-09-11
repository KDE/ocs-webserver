import React from 'react';
function Creator(props){
  return(
    <div className="creatorrow row">
      <div className="col-lg-4">
        <a href={props.baseUrlStore+'/u/'+props.creator.username} >
          <figure >
            <img className="productimg" src={props.creator.profile_image_url} />
          </figure>
        </a>
      </div>
      <div className="col-lg-8 userinfo">
        <div className="userinfo-title">{props.creator.username}</div>
        <span>${props.creator.probably_payout_amount_factor} {props.isAdmin?"($"+props.creator.probably_payout_amount+")":""}</span>
        {/*
        <ul className="userinfo-detail">
        <li>{props.creator.userinfo.cntProjects} products </li>
        <li>{props.creator.userinfo.totalComments} comments </li>
        <li>Like {props.creator.userinfo.cntLikesGave} products </li>
        <li>Got {props.creator.userinfo.cntLikesGot} likes </li>
        <li>Last time active {props.creator.userinfo.lastactive_at} </li>
        <li>Member since {props.creator.userinfo.created_at} </li>
        </ul>
        */}
      </div>
    </div>
  )
}

export default Creator;
