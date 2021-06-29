import { useEffect, useState } from "react";
import ReactTooltip from 'react-tooltip';
import { GenerateToolTipTemplate } from '../../../common/common-helpers';
import SupporterSvg from '../../../../layout/style/media/supporter.svg';
import LoadingDot from "../../../common/loading-dot";


function ProductMakerModule(props){

    let xhr;

    const product = props.product; 

    const [ toolTipLoading, setToolTipLoading ] = useState(true);
    const [ toolTip, setToolTip ] = useState(null);

    useEffect(() => {
        return () => {
            if (xhr && xhr.abort) xhr.abort();
        }
    },[])

    function loadUserToolTip(){
        if (toolTip === null){
            xhr = $.ajax({url:'/member/' + product.member_id + '/tooltip/'}).done(function(res){
                setToolTip(res.data);
                setToolTipLoading(false);
            })
        }
    }

    let profileImageUrl = product.profile_image_url;
    if (product.profile_image_url.indexOf('https://') === -1){
        let hostnameEndsWith = window.location.hostname.endsWith('com') ? 'com' : 'cc';
        profileImageUrl = "https://cdn.pling."+hostnameEndsWith+"/cache/40x40/img/" + product.profile_image_url;
    }


    let supporterBadgeDisplay;
    if (props.maker.isSupporter){
        supporterBadgeDisplay = (
            <div className={"supporter-badge " +  ( props.maker.isSupporterActive ? "" : "inactive")} >
                <span>S{props.maker.isSupporter}</span>
                <img src={SupporterSvg} />
            </div>
        )
    }

    let toolTipDisplay, toolTipClassName = "mytooltip-container"
    if (toolTipLoading === true){
        // toolTipDisplay = <i className="fa fa-spinner" aria-hidden="true"></i>
        toolTipDisplay = <LoadingDot/>
    } else {
        toolTipDisplay = GenerateToolTipTemplate(toolTip);
        toolTipClassName = "mytooltip-container post-get-content"
    }


    return (
            <div className="prod-widget-box">
                    <div className="us-user-profile-grid">
                        <div className="us-user-40">
                            <a onMouseEnter={loadUserToolTip}  href={"/u/" + product.username} title={product.username} className="tooltipuserleft" data-tip="" data-for="product-make-profile-image-tooltip" data-user={product.member_id}>
                                <figure>
                                    {supporterBadgeDisplay}
                                    <img src={profileImageUrl} alt="product-maker" width="40" height="40" />
                                </figure>
                            </a>
                        </div>
                        <div className="us-user-summary uppercase">
                            <p>
                                <a onMouseEnter={loadUserToolTip} data-tip="" data-for="product-make-profile-image-tooltip" href={"/u/"+product.username} className="tooltipuserleft" data-user={product.member_id}>{product.username}</a>
                            </p>
                            <div className="tool-tip-container">
                                <ReactTooltip 
                                    id="product-make-profile-image-tooltip"
                                    place={props.isCollectionView === true ? "bottom" : "left"}
                                    effect="solid"
                                    type="light"
                                    className={toolTipClassName}
                                    backgroundColor="#ededed"
                                    borderColor="#ccc"
                                    border={true}
                                    getContent={[() => { return toolTipDisplay}]}
                                    >
                                </ReactTooltip>
                            </div>
                        </div>
                    </div>
            </div>
    )
}

export default ProductMakerModule;