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
        <span><img style={{width:'15px',height:'15px', float:'left'}} src={props.baseUrlStore+'/images/system/pling-btn-active.png'}></img>
        {props.creator.cnt}{' ('+props.creator.sum_plings_all+')'}</span>      
      </div>
    </div>
  )
}

export default Creator;
