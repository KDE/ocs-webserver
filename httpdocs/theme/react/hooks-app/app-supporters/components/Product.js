import React from 'react';

const Product = (props) => {
  const projectUrl = props.baseUrlStore+"/p/"+props.product.project_id;
  return (
    <div className="productrow row">
        <div className="col-lg-2">
          <a href={projectUrl} >
            <figure >
              <img className="productimg" src={props.product.image_small} />
            </figure>
          </a>
        </div>
        <div className="col-lg-7">
          <div className="product-info">
            <span className="product-info-title"><a href={projectUrl} >{props.product.title}</a></span>
            <span className="product-info-category" style={{color:'#ccc'}}>{props.product.catTitle}</span>
            <span className="product-info-date">{props.product.updated_at}</span>
          </div>
        </div>
        <div className="col-lg-3">
          <div className="score-info">
            <div className="score-number">
               {(props.product.laplace_score/10).toFixed(1) + "%"}
            </div>
            <div className="score-bar-container">
              <div className={"score-bar"} style={{"width":props.product.laplace_score/10 + "%"}}></div>
              <span><img style={{width:'15px',height:'15px', float:'left'}} src={props.baseUrlStore+'/images/system/pling-btn-active.png'}></img>
                            {props.product.sum_plings}
                            {props.product.sum_plings_all ?'['+props.product.sum_plings_all+']':''}</span>
            </div>
          </div>
        </div>
      </div>
  )
}

export default Product
