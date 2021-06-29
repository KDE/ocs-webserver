import CustomModal from './modal';
import Iframe from 'react-iframe'
import LoadingSpinner from './loading-spinners';
import { useState } from 'react';

function DownloadInstallModal(props){

    const [ loading, setLoading ] = useState(true);

    function onIframeLoad(){
        setLoading(false);
    }

    function onCloseModalClick(){
        setLoading(true);
        props.closeModal();
    }

    let modalClassName = "custom-fancybox-modal loading-iframe",
        loadingSpinnerDisplay = (
            <div style={{
                position: "absolute",
                top: "50%",
                left: "50%",
                zIndex: 100,
                marginLeft: "-50%",
                marginTop: "-50px",
                width:"100%",
                height:"100%"
            }}>
                <LoadingSpinner type="ripples"/>
            </div>
        ),
        closeModalButton;
    if (loading === false){
        modalClassName = "custom-fancybox-modal";
        loadingSpinnerDisplay = "";
        closeModalButton = <a className="custom-fancybox-close" onClick={onCloseModalClick}></a>;
    }

    return (
        <CustomModal
            isOpen={props.isOpen}
            hasHeader={false}
            hasFooter={false}
            closeModal={props.closeModal}
            onRequestClose={props.onRequestClose}
            modalClassName={modalClassName}>
                <React.Fragment>
                {closeModalButton}
                {loadingSpinnerDisplay}
                <Iframe url={props.url}
                    onLoad={onIframeLoad}
                    width="600px"
                    height="450px"
                    id={"fancybox-frame"+props.file_id}
                    className="fancybox-iframe"
                    display="initial"
                    scrolling="no"
                    frameBorder="0"
                    position="relative"/>
                </React.Fragment>                    
        </CustomModal>
    )
}

export default DownloadInstallModal;

/*

            /dl?file_id=1553905546&file_type=application/x-executable&file_name=ocs-store-4.1.1-1-x86_64.AppImage&file_size=98828008&has_torrent=0&project_id=1175480&link_type=download&is_external=false&external_link=null
            /p/1175480/startdownload?file_id=1553905546&file_name=ocs-store-4.1.1-1-x86_64.AppImage&file_type=application/x-executable&file_size=98828008

                <div className="heading">Download prepared successfully, click the button below to start.</div>
                <section className="download-install-modal-content empty">
                    <h1 className="empty-title">OCS-Store</h1>
                    <div className="empty-action" style={{height: "50px"}}>
                        <form id="Form1" name="Form1" action="" method="POST">
                            <a download href={props.url} className="btn btn-success btn-lg">
                                {props.type} ({props.fileSize})
                            </a>        
                            <p style={{margin: 0}}>
                                For Appimages we recommend <a href="https://www.opendesktop.org/p/1228228" target="_NEW" style={{color: "#0088d7",textDecoration: "underline"}}> AppImageLauncher</a> for system integration
                            </p>                        
                        </form>
                    </div>
                    <div className="supporter-section">
                        <span className="supporter-section-heading">This download is made possible by supporters like</span>
                        <div className="random-supporter-user">
                            <a target="_NEW" href="pling.local2/u/dummy/" className="tooltipuser" data-tooltip-content="#tooltip_content" data-user="24">
                                {imgDisplay}
                                <p>dummy</p>                                                   
                            </a>
                        </div>
                    </div>
                    <div className="become-a-supporter">
                        Become a <a href={"https://" + window.location.hostname + "/support-predefined"}>Supporter</a>.
                    </div>
                </section>



                <section className="empty">

                    <div className="supporter-section" style="margin-top: 70px; font-size:small">
                            <span>
                                This download is made possible by supporters like
                                <div className="user">
                                    <a target="_NEW" href="pling.local2/u/dummy/" className="tooltipuser" data-tooltip-content="#tooltip_content" data-user="24">
                                        <figure>
                                            <img width="" src="https://cdn.pling.cc/cache/200x200-2/img/b/3/7/a/842cb8ec489812c4a4bd4ce7e78c4978b609.jpg">
                                        </figure>
                                        <p>dummy</p>                                                   
                                    </a>
                                </div>
                            </span>
                    </div>
                    <div className="empty-action" style="height: 100px;">
                        <p style="font-size: 18px; position: absolute; bottom: 0px; width: 100%; left: 0%;">
                        </p>
                        <form action="pling.local2/support-predefined" method="POST" id="support_form_predefined" name="support_form_predefined" target="_parent">
                            <input type="hidden" name="section_id" value="2">
                            <input type="hidden" name="project_id" value="1175480">
                            Become a <a onclick="support_form_predefined.submit();" target="_NEW" style="color: #0088d7;text-decoration: underline; cursor: pointer;">Supporter</a>.
                        </form>
                        <p></p>
                    </div>
                </section>

*/