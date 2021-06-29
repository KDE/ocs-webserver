import { useState } from 'react';
import ProductRelationship from '../../../app-product-relationship/components/ProductRelationship';
import { MoreProductsListItem } from './more-product-module';
import CustomModal from '../../../common/modal';

function UserActionModule(props){

    const product = props.product;

    let userActionsAdminOptionsDisplay;
    if (props.isAdmin === true){
        userActionsAdminOptionsDisplay = (
            <div id="add-project-relationship">
                <ProductRelationship
                    product={product}
                />
            </div>
        )
    }

    let productBasedOnDisplay;
    if (props.data.moreProductsBasedon.length > 0){

        const productsBasedOn = props.data.moreProductsBasedon.map((item,index) => (
            <MoreProductsListItem
                key={index}
                index={index}
                item={item}
                product={product}
                onChangeUrl={props.onChangeUrl}
            />
        ));

        productBasedOnDisplay = (
            <div className="sidebar-content" id="more-product-basedon">
                <span>Based on:</span>
                <div className="pling-card-group">
                    {productsBasedOn}
                </div>
            </div>
        )
    }

    let productVariantsDisplay;
    if (props.data.moreProductsVariants.length > 0){
        const productsVariants = props.data.moreProductsVariants.map((item,index) => (
            <MoreProductsListItem
                key={index}
                index={index}
                item={item}
                product={product}
                onChangeUrl={props.onChangeUrl}
            />
        ));
        productVariantsDisplay = (
            <div className="sidebar-content" id="more-product-variants">
                <span>Variants:</span>
                <div style={{paddingTop:"20px"}} className="pling-card-group">
                    {productsVariants}
                </div>
            </div>
        )
    }

    return (
        <div className="prod-widget-box right">
            <div>
                {userActionsAdminOptionsDisplay}
                <MarkProductAsCloneModule {...props}/>
                {productBasedOnDisplay}
                {productVariantsDisplay}
            </div>
        </div>
    )
}

function MarkProductAsCloneModule(props){

    const product = props.product;

    const [ showModal, setShowModal ] = useState(false);
    
    let modalDisplay, markAsCloneButtonDisplay;
    if (props.user !== null){

        markAsCloneButtonDisplay = <a className="pui-btn pui-btn-small" style={{cursor:"pointer"}} onClick={e => setShowModal(true)}> mark this product as clone</a>

        const headerDisplay = (
            <React.Fragment>
                If you know another product which is original of <b>{product.title}</b>, please fill in the ID:
            </React.Fragment>
        )

        modalDisplay = (
            <CustomModal
                isOpen={showModal}
                header={headerDisplay}
                hasFooter={true}
                closeModal={e => setShowModal(false)}
                onRequestClose={e => setShowModal(false)}
                modalClassName={'light-header'}>
                <ProductCloneForm 
                    {...props}
                    onClose={e => setShowModal(false)}
                />
            </CustomModal>
        )

    } else {
        markAsCloneButtonDisplay = (
            <a className="pui-btn pui-btn-small" data-target="#like-product-modal" data-toggle="modal" role="button">
                mark this product as clone
            </a>
        )
    }

    return (
        <React.Fragment>
            {markAsCloneButtonDisplay}
            {modalDisplay}
        </React.Fragment>
    )
}

function ProductCloneForm(props){
    
    const [ loading, setLoading ] = useState(false);
    const [ clonedProductId, setClonedProductId ] = useState('');
    const [ message, setMessage ] = useState('');
    const [ error, setError ] = useState('');
    const [ isReported, setIsReported ] = useState(false);
    const [ successMessage, setSuccessMessage ] = useState('');

    function onClonedProductIdChange(e){
        setClonedProductId(e.target.value);
    }

    function onMessageChange(e){
        setMessage(e.target.value);
    }

    function onSubmitProductCloneForm(){
        if (!loading && clonedProductId.length !== 0){
            setLoading(true)
            setError('');
            const serializedData =  "p="+props.product.project_id+"&"+
                                    "pc="+clonedProductId+"&"+
                                    "t="+message;
            $.ajax({
                url:"/report/productclone/",
                methode:"POST",
                data:serializedData
            }).done(function(res){
                setLoading(false);
                if (res.status === "err") setError(res.message);
                else {
                    setIsReported(true);
                    setSuccessMessage(res.message)
                }
            })
        } else {
            if (clonedProductId.length === 0){
                setError('Please enter product ID')
            }
        }
    }
    
    let errorDisplay;
    if (error.length > 0) errorDisplay = <p style={{color:"red"}}>{error}</p>

    let buttonsDisplay,
        successMessageDisplay,
        formDisplay;
    
    if (isReported === false){

        const formRowstyle = {    
            "marginBottom":"4px",
            "display": "block"
        }

        formDisplay = (
            <React.Fragment>
                <p className="form-row">
                    <b style={formRowstyle}>ID of the Original on opendesktop:</b>
                    <input type="text" onChange={onClonedProductIdChange} value={clonedProductId} />
                </p>
                {errorDisplay}
                <p className="form-row">
                    <b style={formRowstyle}>Additional message:</b>
                    <textarea onChange={onMessageChange} value={message}></textarea>
                </p>
            </React.Fragment>
        )

        let buttonIconDisplay = <span className="glyphicon glyphicon-share-alt"></span>
        if (loading === true) buttonIconDisplay = <span className="glyphicon glyphicon-refresh spinning"></span>
        buttonsDisplay = (
            <React.Fragment>
                <button onClick={onSubmitProductCloneForm} className="footer-button">
                    {buttonIconDisplay} add credits
                </button>
                <button onClick={props.onClose} className="footer-button">cancel</button>
            </React.Fragment>
        )
    } else {
        successMessageDisplay = <div dangerouslySetInnerHTML={{__html:successMessage}}></div>
        buttonsDisplay = (
            <button onClick={props.onClose} className="footer-button">close</button>
        )
    }

    return (
        <React.Fragment>
            <div className="custom-modal-body">
                {formDisplay}
                {successMessageDisplay}
            </div>
            <div className="custom-modal-footer">
                {buttonsDisplay}
            </div>
        </React.Fragment>
    )
}

export default UserActionModule;