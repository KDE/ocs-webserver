import React, {useState,useRef} from 'react';
import Axios from 'axios';
import ModalAddRelationship from './ModalAddRelationship';
import ModalFlagModification from './ModalFlagModification';
const ProductRelationship = () => {
    const [product, setProduct] = useState(window.product);
    const [radioType, setRadioType] = useState('is-original');
    const [message, setMessage] = useState('');
    const [project_id, setProjectId] = useState('');
    const [succeed, setSucceed] = useState(false);
    const [externalurl, setExternalurl] = useState('');
    const [response, setResponse] = useState({status:''});
    
    const handleSubmit =(event)=>{
      event.preventDefault();
     
      const url = window.config.baseUrlStore+'/report/productclone/';   
      const params = new URLSearchParams();
      params.append('pc', project_id);
      params.append('p', product.project_id);
      params.append('t', message);
      params.append('i', radioType);
      
      Axios.post(url,params)
      .then(function (response) {
        setResponse(response.data);        
        if(response.data.status=='ok')
        {
          setSucceed(true);        
        }
      })
      .catch(function (error) {
        console.log(error);
      });     
    }
    const handleOnCloseModal = event=>{
       setMessage('');
       setSucceed(false);
       setResponse({status:''});
       setRadioType = 'is-original';
    }

    const handleSubmitFlagMod = event =>{
      event.preventDefault();
      const url = window.config.baseUrlStore+'/report/flagmod/';   
      const params = new URLSearchParams();
      params.append('l', externalurl);
      params.append('p', product.project_id);
      params.append('t', message);     
      Axios.post(url,params)
      .then(function (response) {
        setResponse(response.data);
        if(response.data.status=='ok')
        {
          setSucceed(true);        
        }        
      })
      .catch(function (error) {
        console.log(error);
      });
    }

    const handleChangeMessage = event =>{
      setMessage(event.target.value);
    }

    const handleChangeExternalurl=event =>{
      setExternalurl(event.target.value);
    }
   
    const handleRadioChange = event => {
     
      setRadioType(event.target.value);   
    };
    
    const triggerAddRelationship=event=>{      
      $('#productFlagModificationPanel').modal('hide');
      $('#productRelationshipPanel').modal('show');
    }
    
    const handleInputProjectIdChange = event =>{
      setProjectId(event.target.value);  
    }
    return (
      <>
      <a data-toggle="modal" data-target="#productRelationshipPanel" style={{display: 'inherit',fontStyle:'italic',cursor:'pointer'}}>
         Add Relationship
      </a>         
      <a data-toggle="modal" data-target="#productFlagModificationPanel" style={{display: 'inherit',fontStyle:'italic',cursor:'pointer'}}>
         Flag Modification
      </a> 
      <ModalAddRelationship 
          product={product} 
          project_id={project_id} 
          setProjectId={setProjectId} 
          message={message}
          handleChangeMessage={handleChangeMessage}
          handleRadioChange={handleRadioChange}
          radioType={radioType}
          succeed={succeed}
          handleSubmit={handleSubmit}
          handleInputProjectIdChange = {handleInputProjectIdChange}
          response ={response}
          handleOnCloseModal = {handleOnCloseModal}
          /> 
        <ModalFlagModification 
          product={product} 
          project_id={project_id} 
          setProjectId={setProjectId} 
          message={message}
          handleChangeMessage={handleChangeMessage}          
          succeed={succeed}
          handleSubmit={handleSubmitFlagMod}
          triggerAddRelationship={triggerAddRelationship}
          externalurl={externalurl}  
          handleChangeExternalurl={handleChangeExternalurl}   
          response ={response}              
          handleOnCloseModal = {handleOnCloseModal} 
          /> 
            
           
     </>
     
    )
}

export default ProductRelationship;
