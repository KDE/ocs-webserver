import React, {useState,useRef} from 'react';
import Axios from 'axios';
import ModalAddRelationship from './ModalAddRelationship';
import ModalFlagModification from './ModalFlagModification';
import CustomModal from '../../common/modal';

const ProductRelationship = (props) => {
    let initProduct = props.product;
    if (!props.product) initProduct = window.product;
    const [product, setProduct] = useState(initProduct);
    const [radioType, setRadioType] = useState('is-original');
    const [message, setMessage] = useState('');
    const [project_id, setProjectId] = useState('');
    const [succeed, setSucceed] = useState(false);
    const [externalurl, setExternalurl] = useState('');
    const [response, setResponse] = useState({status:''});

    const [ showRelationshipsModal , setShowRelationshipsModal ] = useState(false);
    const [ showFlagModModal, setShowFlagModModal ] = useState(false);

    const handleSubmit =(event)=>{
      event.preventDefault();
     
      //const url = window.config.baseUrlStore+'/report/productclone/';   
      const url = '/report/productclone/';   
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
       setRadioType('is-original');
       setShowRelationshipsModal(false);
       setShowFlagModModal(false);
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
      <React.Fragment>
      <a onClick={e => setShowRelationshipsModal(showRelationshipsModal === true ? false : true)} style={{display: 'inherit',fontStyle:'italic',cursor:'pointer'}}>
         Add Relationship
      </a>         
      <a onClick={e => setShowFlagModModal(showFlagModModal === true ? false : true)}  style={{display: 'inherit',fontStyle:'italic',cursor:'pointer'}}>
         Flag Modification
      </a> 
        <CustomModal 
            isOpen={showRelationshipsModal}
            header={"Add RelationShip"}
            closeModal={handleOnCloseModal}
            hasFooter={true}
            modalClassName={'rel-modal'}
        >
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
        </CustomModal>
        <CustomModal 
          isOpen={showFlagModModal}
          header={"Flag Modification"}
          closeModal={handleOnCloseModal}
          hasFooter={true}
          modalClassName={'rel-modal'}
        >
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
        </CustomModal>
     </React.Fragment>
    )
}

export default ProductRelationship;
