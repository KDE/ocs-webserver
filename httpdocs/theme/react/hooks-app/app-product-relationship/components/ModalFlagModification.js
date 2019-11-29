import React from 'react'

const ModalFlagModification = (props) => {
    return (
        <div className="modal fade " id="productFlagModificationPanel" tabindex="-1" data-keyboard="false" role="dialog" aria-labelledby="productRelationshipPanelModalLabel" aria-hidden="true">
        <div className="modal-dialog " role="document" style={{width:'1000px'}}>
        <form name="form-flag-modification" onSubmit={props.handleSubmit}>
          <div className="modal-content">
            <div className="modal-header">              
              <button type="button" className="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
              </button>
              <h5 className="modal-title" id="productRelationshipPanelModalLabel">Flag Modification</h5>
            </div>
            <div className="modal-body">
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
                                  <div className="col-lg-12"><h6>Extern Original Url:</h6></div>
                                  <div className="col-lg-12">
                                    <input name="externalurl" id="externalurl" value={props.externalurl} onChange={props.handleChangeExternalurl} required></input>
                                </div>
                        </div>                                              
                        <div className="row">
                                  <div className="col-lg-12"><h6>Additional message: </h6> </div>
                                  <div className="col-lg-12">
                                    <textarea name="message" id="message" value={props.message} 
                                        onChange={props.handleChangeMessage}
                                      style={{width:'310px'}} />
                                </div>
                        </div>

                        <div className="row"> 
                        <div className="col-lg-12" style={{paddingTop:'20px'}}>
                          <span>For pointing to an original existing product on this site
                            , use <a onClick={props.triggerAddRelationship} style={{display: 'inherit',fontStyle:'italic',cursor:'pointer'}}>
                                    Add Relationship
                                  </a> option instead. </span>                      
                          </div>
                        </div>
                      
                      </div>
                    
                      
                  
                  </div>
                </div>
                

            </div>
            <div className="modal-footer">
                {props.succeed && 
                <>
                  <p>Thank you. The credits have been submitted.</p><p>It can take some time to appear while we verify it.</p>
                </>
                }

              <button type="button" className="btn btn-primary" data-dismiss="modal">Close</button>              
              {!props.succeed &&
              <button type="submit" className="btn btn-primary" >Flag Modification</button>             
              }
            </div>
          </div>
        </form>
        </div>
      </div>
    )
}


export default ModalFlagModification;