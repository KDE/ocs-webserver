import React from "react";
import { useState, useEffect } from "react"
import LoadingDot from "../../../common/loading-dot";
import CustomModal from "../../../common/modal";

function ProjectPlingsModule(props){

    /* STATE */

    let memberId = null;
    const member = props.user;
    if (member && member.member_id) memberId = member.member_id;

    const [ loading, setLoading ] = useState(false);

    let initIsPlinged = props.user && props.user.isPlinged ? props.user.isPlinged : false;
    const [ isPlinged, setIsPlinged ] = useState(initIsPlinged);
    
    let initCntPlings = props.data.cntProjectPlings;
    const [ cntPlings, setCntPlings ] = useState(initCntPlings);  

    const defaultImgSrc = "/images/system/pling-btn-normal.png";
    const hoverImgSrc = "/images/system/pling-btn-hover.png";
    const activeImgSrc =  "/images/system/pling-btn-active.png";
    let initImgUrl = props.data.isPlinged === true ? activeImgSrc : defaultImgSrc
    
    const [ imgUrl, setImgUrl ] = useState();
    const [ errorMessage, setErrorMessage ] = useState(null)

    const [ showModal, setShowModal ] = useState(false);
    const [ modalActive, setModalActive ] = useState(false);

    /* COMPONENT */

    useEffect(() => {
        let newImgUrlVal = defaultImgSrc;
        if (isPlinged === true) newImgUrlVal = activeImgSrc;
        setImgUrl(newImgUrlVal);
    },[isPlinged])


    // pling button

    function onImageMouseEnter(){
        setImgUrl(hoverImgSrc)
    }

    function onImageMouseLeave(){
        setImgUrl(isPlinged === true ? activeImgSrc : defaultImgSrc );
    }


    function onPlingButtonClick(){
            
        let isAuthorized = true;

        if (memberId === null || props.user.isSupporterActive === false || memberId === props.product.member_id) {
            setShowModal(true);
            isAuthorized = false;
        }

        if (isAuthorized === true){
            setLoading(true);
            $.ajax({url: "/p/"+props.product.project_id+"/plingproject/",cache: false}).done(function( response ) {
                
                if(response.status =='error'){
                    setErrorMessage(response.msg);
                }else{
                    if(response.action=='delete'){
                        //pling deleted
                        setIsPlinged(false);
                    }else{
                        //pling inserted
                        setIsPlinged(true);
                    }
                    setCntPlings(response.cnt);
                }
                setLoading(false)
            });   
        }
    }

    // pling button text display
    
    let textDisplay;
    if (loading === true){
        textDisplay = <LoadingDot/>
    }
    else {
        if (errorMessage !== null) textDisplay = errorMessage;
        else {
            if (cntPlings === "0") textDisplay = "Pling me";
            else textDisplay = (cntPlings > 0 ? cntPlings : "") + " Pling" + (cntPlings > 1 ? "s" : "");
        }
    }


    // modal display

    let modalHeaderDisplay = " ",
        modalContentDisplay;

    if (!memberId){
        modalContentDisplay = (
            <div className="please-login">
                <p>
                Please Login.
                </p>
                <a className="pui-btn primary" href={(json_loginurl ? json_loginurl : "/login/")}>Login</a>
            </div>
        )
    } else if (!props.data.isSupporter){
        modalHeaderDisplay = "Become a Supporter";
        modalContentDisplay = (
            <div className="please-login">
                <p>To pling a product and help the artist please consider becoming a supporter. Thanks!</p>
                <a href="/support">Become a supporter</a>
            </div>
        )    
    } else if (memberId === props.product.member_id) modalHeaderDisplay = "Project owner not allowed";

    /* RENDER */

    return (
        <React.Fragment>
            <div className="projectdtailHeart">
                <div id={"container-pling"+props.product.project_id} className="container-pling" onClick={() => onPlingButtonClick()} onMouseEnter={onImageMouseEnter} onMouseLeave={onImageMouseLeave}>
                    <div style={{cursor:"pointer"}} className="partialbuttonplingproject prod-widget-col-2">
                        <div className="plingbartext font-bold">
                            <span className="plingnum" style={{fontSize:cntPlings === "0" ? "12px" : ""}}>{textDisplay}</span> 
                        </div>
                        <img id="plingbtn" src={imgUrl}/>
                    </div>
                </div>
            </div> 
            <CustomModal 
                isOpen={showModal}
                header={modalHeaderDisplay}
                closeModal={() => setShowModal(false)}
                modalBodyClassName={"align-center"}
            >
                {modalContentDisplay}
            </CustomModal>
        </React.Fragment>
    )
}

export default ProjectPlingsModule;