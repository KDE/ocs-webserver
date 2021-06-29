
import Modal from 'react-modal';
import { useState } from 'react';
import './style/modal.css';

Modal.setAppElement('body');

const CustomModal = (props) => {

    const [ modalActive, setModalActive ] = useState(false)

    function onAfterOpenModal(){
        setModalActive(true);            
    }

    function onRequestClose(){
        if (props.onRequestClose) props.onRequestClose()
        else {
            setModalActive(false);
            props.closeModal();
        }
    }

    let headerDisplay;
    if (props.header){
        headerDisplay = (
            <div className="custom-modal-header">
                <label>{props.header}</label>
                <a onClick={props.closeModal} className="custom-modal-x-close-button"></a>
            </div>
        )
    }

    const modalBodyClassName = props.modalBodyClassName ? props.modalBodyClassName : " standard";
    const hasFooterClassName = props.hasFooter ? " has-footer" : "";

    let contentDisplay;
    if (props.children) contentDisplay = <div className={"custom-modal-body " + modalBodyClassName + hasFooterClassName}>{props.children}</div>

    let modalClassName = "custom-modal " + props.modalClassName,
        overlayClassName = "custom-modal-overlay";
    if (modalActive === true){
        modalClassName += " after-show";
        overlayClassName += " after-show"
    } 


    return (
        <Modal
            isOpen={props.isOpen}
            onAfterOpen={onAfterOpenModal}
            onRequestClose={onRequestClose}
            className={modalClassName}
            overlayClassName={overlayClassName}
        >
            {headerDisplay}
            {contentDisplay}
        </Modal>
    )
}

/* USEAGE:

    <CustomModal
        isOpen={true || false}
        header={STRING}
        hasFooter={true || false}
        closeModal={function}
        onRequestClose={function}
        modalBodyClassName={STRING}>
        <React.Fragment>
        {children}
        </React.Fragment>
    </CustomModal>

    * footer should be included inside { children } and look like this:
        <div className="custom-modal-footer"> { ... } </div>

*/

export default CustomModal;

