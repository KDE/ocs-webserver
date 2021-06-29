import React from 'react'

const ModalFlagModification = (props) => {
    return (      
      <form name="form-flag-modification" onSubmit={props.handleSubmit}>
          <div style={{display:'flex'}}>
                <div style={{borderRight:'1px solid #ccc',textAlign:'center', paddingRight:'20px',minHeight:'300px'}}>
                    <h4>
                      {props.product.title}
                    </h4>
                    <img src={props.product.image_small_absolute} style={{width:'400px'}}></img>                      
                    <span style={{ fontSize: '11px', color: '#ccc',lineHeight:'15px',display:'inherit' }}>
                    {props.product.cat_title}</span>    
                </div>
                <div>                    
                    <div className="container" style={{width:'500px'}}>  
                    <div className="row">
                                <div className="col-lg-12"><h6>URL to external product*:</h6></div>
                                <div className="col-lg-12">
                                  <input name="externalurl" id="externalurl" value={props.externalurl} onChange={props.handleChangeExternalurl} required></input>
                              </div>
                      </div>                                              
                      <div className="row">
                                <div className="col-lg-12"><h6>Message (optional): </h6> </div>
                                <div className="col-lg-12">
                                  <textarea name="message" id="message" value={props.message} 
                                      onChange={props.handleChangeMessage}
                                    style={{width:'310px'}} />
                              </div>
                      </div>

                      <div className="row"> 
                      <div className="col-lg-12" style={{paddingTop:'20px'}}>
                        <span>*For pointing to an original product on this site, please use 
                            use <a onClick={props.triggerAddRelationship} style={{display: 'inherit',fontStyle:'italic',cursor:'pointer'}}>
                                  Add Relationship
                                </a> option instead. </span>                      
                        </div>
                      </div>
                    
                    </div>
                  
                    
                
                </div>
              </div>
          <div className="modal-footer">
            <span dangerouslySetInnerHTML={{__html: props.response.message}}></span>  
            <button type="button" onClick={props.handleOnCloseModal} className="btn btn-primary">Close</button>              
            {!props.succeed &&
              <button type="submit" className="btn btn-primary" >Flag Modification</button>             
            }
          </div>
      </form>
    )
}


export default ModalFlagModification;