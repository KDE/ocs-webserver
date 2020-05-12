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
      </div>
    </div>
  )
}

export default Creator;
