import React from 'react'

const RecentPlingedProduct = (props) => {
    const projectUrl = props.baseUrlStore+"/p/"+props.product.project_id;
    const cStyle = {
        'background-image':'url('+props.product.image_small+')', 
        'background-repeat': 'no-repeat',
        'background-size':'115px'
    }
  return (
    <div className="product-wrap" style={cStyle}>        
          <a href={projectUrl} >           
            <figure>
                <img src={props.product.profile_image_url}></img>
            </figure>                                      
          </a>              
          <h3>by {props.product.username}</h3>
          <h3 style={{color:'#ccc'}}>{props.product.catTitle}</h3>
     
          <span className="small"><img style={{width:'15px',height:'15px', float:'left'}} src={props.baseUrlStore+'/images/system/pling-btn-active.png'}></img>
                            {props.product.sum_plings}
                            {props.product.sum_plings_all ?'['+props.product.sum_plings_all+']':''}</span>
          
       
      </div>
  )
}


export default RecentPlingedProduct
