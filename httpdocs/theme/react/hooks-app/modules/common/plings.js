import { useState } from 'react';
import { GenerateImageUrl, FormatDate } from './common-helpers';
import './style/plings.css';
import UserToolTipModule from './user-tooltip-module';

function PlingsModule(props){

    const plingsListItems = props.items.map((item,index) => ( <PlingListItem onPlingItemClick={props.onPlingItemClick} key={index} pling={item} type={props.type} /> ));
    
    let propsTypeCssClass;
    if (props.type) propsTypeCssClass = props.type + "-list-container";

    return (
        <div className={"pling-list-container " + propsTypeCssClass}>
            {plingsListItems}
        </div>
    )
}

function PlingListItem(props){

    const p = props.pling;

    const initImgUrl = GenerateImageUrl(p.profile_image_url,200,200);
    const [ imgUrl, setImgUrl ] = useState(initImgUrl)

    function onImgLoadError(){
        const newImgUrl = GenerateImageUrl(p.profile_image_url,200,200,'-2');
        setImgUrl(newImgUrl)
    }

    function onPlingItemClick(e){
        e.preventDefault();
        props.onPlingItemClick("/u/"+p.username,p.username);
    }

    let iconDisplay;
    if (props.type === "plings" || props.type === "plinged-by"){
        iconDisplay =  (
            <span className="pui-pling">
                <svg xmlns="http://www.w3.org/2000/svg" fill-rule="evenodd" clip-rule="evenodd" stroke-linejoin="round" stroke-miterlimit="2" viewBox="0 0 24 24">
                    <path d="M17.467348 7.9178719c-.347122-2.7564841-3.201417-4.3323187-5.789058-3.9194079-1.7356129.2821847-3.460181.6085251-5.1768601.9799707.2997877 1.2572492.6011532 2.5146567.9025188 3.7720641.0899363.3771432.1356934.5656356.2256297.942937.8283607 3.4577522 1.6551436 6.9153452 2.4835046 10.3730972 1.136037-.333937 2.278386-.645559 3.427047-.934707-.362901-1.870206-.725802-3.740411-1.088703-5.610617 0 0 .61062-.129935.929342-.194823 2.440903-.497266 4.406879-2.86078 4.086579-5.4085141zm-4.768202 1.6774402c-.389724.0720101-.583797.1090439-.971943.1854854-.121493-.6234019-.241408-1.2468038-.362901-1.8702057.397613-.074701.59642-.1107848.994033-.1808958.523839-.087995 1.065035.2329646 1.154971.7609334.08994.5200555-.298209 1.0046598-.81416 1.1046827z" fill="#fff"></path>
                </svg>
            </span>
        )    
    } else if (props.type === "likes"){
        iconDisplay = (
            <span className="pui-heart active">
                <svg svg="" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 16" fill-rule="evenodd" clip-rule="evenodd" stroke-linejoin="round" stroke-miterlimit="2">
                    <g transform="matrix(1,0,0,1,-238,-365.493)">
                        <path d="M239.99,373.475L246,379.485L252.01,373.475C253.376,372.109 253.376,369.891 252.01,368.525C250.644,367.159 248.427,367.159 247.061,368.525L246,369.586L244.939,368.525C243.573,367.159 241.356,367.159 239.99,368.525C238.624,369.891 238.624,372.109 239.99,373.475Z">
                        </path>
                    </g>
                </svg>
            </span>
        )
    }



    let createdByDisplay;
    if (props.type === "plings" || props.type === "likes"){
        if (p.created_at) createdByDisplay = FormatDate(p.created_at.split(' ')[0]);
    }
    else if (props.type === "affiliates") createdByDisplay = FormatDate(p.member_created_at.split(' ')[0]);

    let plingsCounter;
    let userNameDisplay;
    if (props.type === "plinged-by"){
        if (p.cntplings) plingsCounter = <span className="cntplings">{p.cntplings}</span>
        userNameDisplay = (
            <div className="pling-item-username username">
                <UserToolTipModule 
                    showBy={false}
                    username={p.username} 
                    memberId={p.member_id} 
                    toolTipId={"pling-tool-tip-user-"+p.member_id}
                />
            </div>
        )
    } else {
        userNameDisplay = <div className="pling-item-username username">{p.username}</div>
    }


    return (
        <div className="pling-list-item-container" id={"pling-item-"+p.username}>
            <div className={"pling-item-wrapper" + (props.type === "plinged-by" ? " plinged-by" : "")}>
                    <a className="pling-item-profile-image" href={"/u/"+p.username}>
                        <figure>
                            <img onError={onImgLoadError} src={imgUrl}/>
                        </figure>
                    </a>
                    {userNameDisplay}
                    <div className="pling-item-bottom bottom-title">
                        {plingsCounter}
                        {iconDisplay}
                        <span className="pling-item-created-at">{createdByDisplay}</span>
                    </div>
            </div>
        </div>
    )
}

export default PlingsModule;