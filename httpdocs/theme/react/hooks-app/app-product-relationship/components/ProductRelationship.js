import React, {useState} from 'react';
import SearchProductInput from './SearchProductInput';
const ProductRelationship = () => {
    const [product, setProduct] = useState(window.product);
    const [radioType, setRadioType] = useState('');
    
    const handleSubmit =(event)=>{
      event.preventDefault();
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
      <div>
       <button type="button" className="btn btn-primary btn-xs" data-toggle="modal" data-target="#productRelationshipPanel">
         Add Relationship
      </button>
        
        <div className="modal fade " id="productRelationshipPanel" tabindex="-1" data-keyboard="false" role="dialog" aria-labelledby="productRelationshipPanelModalLabel" aria-hidden="true">
        <div className="modal-dialog " role="document" style={{width:'1000px'}}>
          <div className="modal-content">
            <div className="modal-header">              
              <button type="button" className="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
              </button>
              <h5 className="modal-title" id="productRelationshipPanelModalLabel">Add Product Relationship</h5>
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
                    <form name="form-add-product-relationship" onSubmit={handleSubmit}>
                      <div className="container" style={{width:'600px'}}>                        
                        <SearchProductInput baseUrlStore={window.config.baseUrlStore} 
                             searchbaseurl = {window.config.searchbaseurl}
                             product ={product}
                             />  
                        <div className="row"> 
                        <div className="col-lg-12" style={{paddingTop:'20px'}}>
                          <span>The chosen product above </span>
                        <fieldset>
                            <input type="radio" id="mc" name="type" value="is-original" checked={radioType === "is-original"}
                              onClick={handleRadioChange}
                            />
                            <label for="mc"> Is original</label> 
                            
                            
                            <input type="radio" id="vi" name="type" value="is-sibling" checked={radioType === "is-sibling"}
                              onClick={handleRadioChange}
                            />
                            <label for="vi"> Is sibling</label> 
                            <br></br>                         
                            <span> of current product on the left.</span>
                          </fieldset>
                          </div>
                        </div>
                      </div>
                    </form>
                  </div>
                </div>
            </div>
            <div className="modal-footer">
              <button type="button" className="btn btn-secondary" data-dismiss="modal">Close</button>
              <button type="button" className="btn btn-primary">Save changes</button>
            </div>
          </div>
        </div>
      </div>
      </div>
    )
}

export default ProductRelationship;
