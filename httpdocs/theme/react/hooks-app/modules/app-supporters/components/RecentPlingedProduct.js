import React  from 'react'

const RecentPlingedProduct = (props) => {
  const projectUrl = props.baseUrlStore + "/p/" + props.product.project_id;
  const memberUrl = props.baseUrlStore + "/u/" + props.product.username;
  const cStyle = {
    backgroundImage: 'url(' + props.product.image_small + ')',
    backgroundRepeat: 'no-repeat',
    backgroundSize: '115px'
  }
  const stylePlaceholder={
    position: 'absolute',
    top: '0px',
    cursor: 'pointer',
    width:'100%',
    height:'50px',
  }
  

  const handleOnClick =()=>{
     window.location.href=projectUrl;
  }
 
  return (
    <div className="product-wrap" style={cStyle}>
      <div style={stylePlaceholder} onClick={handleOnClick}>

      </div>
      <h3 >{props.product.title}</h3>
      <h3 style={{ color: '#ccc',fontSize:'small' }}>{props.product.catTitle}</h3>

      <span className="small">
        <a className="tooltipuserplings" data-tooltip-content="#tooltip_content" data-user={props.product.project_id} >
          <img style={{ width: '15px', height: '15px', float: 'left' }} src={props.baseUrlStore + '/images/system/pling-btn-active.png'}></img>
        </a>
        {props.product.sum_plings}
        <span style={{color:'ccc'}}>{props.product.sum_plings_all ? '[' + props.product.sum_plings_all + ']' : ''}
        </span>
      </span>
    </div>
  )
}


export default RecentPlingedProduct
