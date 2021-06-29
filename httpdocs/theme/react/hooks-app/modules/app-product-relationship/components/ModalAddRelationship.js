import React from 'react'
import SearchProductInput from './SearchProductInput';
const ModalAddRelationship = (props) => {
    return (
        <form name="form-add-product-relationship" onSubmit={props.handleSubmit}>
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
                        <SearchProductInput baseUrlStore={window.config.baseUrlStore} 
                            searchbaseurl = {window.config.searchbaseurl}
                            product ={props.product}   
                            project_id={props.project_id}                          
                            setProjectId={props.setProjectId}
                            handleInputProjectIdChange={props.handleInputProjectIdChange}
                            />  

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
                          <span>The product above is </span>
                        <fieldset>
                            <input type="radio" id="mc" name="type" value="is-original" 
                            checked={props.radioType === "is-original"}                           
                            onChange={props.handleRadioChange}
                            />
                            <label htmlFor="mc"> original </label> 
                            <span> or </span>
                            <input type="radio" id="mc2" name="type" value="is-clone" 
                            checked={props.radioType === "is-clone"}   
                            onChange={props.handleRadioChange}                         
                            />
                            <label htmlFor="mc2"> clone </label> 
                            {/*
                            <input type="radio" id="vi" name="type" value="is-sibling" checked={radioType === "is-sibling"}
                              onClick={handleRadioChange}
                            />
                            <label htmlFor="vi"> Is sibling</label> 
                            */}
                            <br></br>                         
                            <span> of the product on left side.</span>
                          </fieldset>
                          </div>
                        </div>
                      </div>
                  </div>
            </div>
            <div className="modal-footer">

                
                <>
                <span dangerouslySetInnerHTML={{__html: props.response.message}}></span>                 
                </>
              

              <button type="button" className="btn btn-primary" data-dismiss="modal" onClick={props.handleOnCloseModal}>Close</button>              
              {!props.succeed &&
              <button type="submit" className="btn btn-primary" >Add relationship</button>             
              }
            </div>
        </form>
    )
}


export default ModalAddRelationship;