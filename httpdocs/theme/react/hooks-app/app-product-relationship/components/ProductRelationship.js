import React, {useState,useRef} from 'react';
import SearchProductInput from './SearchProductInput';
import Axios from 'axios';
const ProductRelationship = () => {
    const [product, setProduct] = useState(window.product);
    const [radioType, setRadioType] = useState('is-original');
    const [message, setMessage] = useState('');
    const [project_id, setProjectId] = useState('');

    const [succeed, setSucceed] = useState(false);
   
   
    const handleSubmit =(event)=>{
      event.preventDefault();
     
      const url = window.config.baseUrlStore+'/report/productclone/';   
      const params = new URLSearchParams();
      params.append('pc', project_id);
      params.append('p', product.project_id);
      params.append('t', message);
      
     
      Axios.post(url,params)
      .then(function (response) {
        setSucceed(true);
        console.log(response);
      })
      .catch(function (error) {
        console.log(error);
      });
     

    }


    const handleChangeMessage = event =>{
      setMessage(event.target.value);
    }

    const handleRadioChange = event => {
      if(event.target.value == radioType)
      {
        setRadioType('');      
      }else{
        setRadioType(event.target.value);      
      }
      
    };
    
    
  
    return (
      <form name="form-add-product-relationship" onSubmit={handleSubmit}>
      <div>
       <a data-toggle="modal" data-target="#productRelationshipPanel" style={{fontStyle:'italic',cursor:'pointer'}}>
         Add Relationship
      </a>        
        <div className="modal fade " id="productRelationshipPanel" tabindex="-1" data-keyboard="false" role="dialog" aria-labelledby="productRelationshipPanelModalLabel" aria-hidden="true">
        <div className="modal-dialog " role="document" style={{width:'1000px'}}>
          <div className="modal-content">
            <div className="modal-header">              
              <button type="button" className="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
              </button>
              <h5 className="modal-title" id="productRelationshipPanelModalLabel">Add Relationship</h5>
            </div>
            <div className="modal-body">
                <div style={{display:'flex'}}>
                  <div style={{borderRight:'1px solid #ccc',textAlign:'center', paddingRight:'20px',minHeight:'300px'}}>
                      <h4>
                        {product.title}
                      </h4>
                      <img src={product.image_small_absolute} style={{width:'400px'}}></img>                      
                      <span style={{ fontSize: '11px', color: '#ccc',lineHeight:'15px',display:'inherit' }}>{product.cat_title}</span>    
                  </div>
                  <div>
                    
                      <div className="container" style={{width:'600px'}}>                        
                        <SearchProductInput baseUrlStore={window.config.baseUrlStore} 
                             searchbaseurl = {window.config.searchbaseurl}
                             product ={product}   
                             project_id={project_id}                          
                             setProjectId={setProjectId}
                             />  

                        <div className="row">
                                  <div className="col-lg-12"><h6>Additional message: </h6> </div>
                                  <div className="col-lg-12">
                                    <textarea name="message" id="message" value={message} onChange={handleChangeMessage}
                                      style={{width:'310px'}} />
                                </div>
                        </div>

                        <div className="row"> 
                        <div className="col-lg-12" style={{paddingTop:'20px'}}>
                          <span>The chosen product above </span>
                        <fieldset>
                            <input type="radio" id="mc" name="type" value="is-original" checked={radioType === "is-original"}
                             defaultChecked
                            />
                            <label for="mc"> Is original</label> 
                            
                            {/*
                            <input type="radio" id="vi" name="type" value="is-sibling" checked={radioType === "is-sibling"}
                              onClick={handleRadioChange}
                            />
                            <label for="vi"> Is sibling</label> 
                             */}
                            <br></br>                         
                            <span> of product on the left.</span>
                          </fieldset>
                          </div>
                        </div>
                       
                      </div>
                     
                      
                  
                  </div>
                </div>
                

            </div>
            <div className="modal-footer">

                {succeed && 
                <>
                  <p>Thank you. The credits have been submitted.</p><p>It can take some time to appear while we verify it.</p>
                </>
                }

              <button type="button" className="btn btn-primary" data-dismiss="modal">Close</button>              
              {!succeed &&
              <button type="submit" className="btn btn-primary" >Add relationship</button>             
              }
            </div>
          </div>
        </div>
      </div>
      </div>
      </form>
    )
}

export default ProductRelationship;
