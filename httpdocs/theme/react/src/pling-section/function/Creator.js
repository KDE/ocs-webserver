import React from 'react';
function Creator(props){
  return(
    <div className="creatorrow row">
      <div className="col-lg-12">
        <a href={props.baseUrlStore+'/u/'+props.creator.username} >
          <figure >
            <img className="productimg" src={props.creator.profile_image_url} />
          </figure>
        </a>
      </div>
    </div>
  )
}

export default Creator;
