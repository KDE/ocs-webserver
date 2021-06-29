import React from 'react';
function Creator(props){
  return(
    <div className="creatorrow row">
      <div className="col-lg-2">
        <a href={props.baseUrlStore+'/u/'+props.creator.username} >
          <figure >
            <img className="productimg" src={props.creator.profile_image_url} />
          </figure>
        </a>
      </div>
      <div className="col-lg-6 userinfo">
        <div className="userinfo-title">{props.creator.username}</div>             
      </div>
      <div className="col-lg-4">
      <span><img style={{width:'15px',height:'15px', float:'left',marginRight:'2px'}} src={props.baseUrlStore+'/images/system/pling-btn-active.png'}></img>
        {props.creator.cnt}
        <span className="colorGrey">{' ('+props.creator.sum_plings_all+')'}</span>
        </span>  

      </div>
    </div>
  )
}

export default Creator;
