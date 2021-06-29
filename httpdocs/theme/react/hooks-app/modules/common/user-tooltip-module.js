import React from 'react';
import { useState, useEffect } from 'react';
import ReactTooltip from 'react-tooltip';
import { GenerateImageUrl, GenerateToolTipTemplate } from './common-helpers';
import LoadingDot from './loading-dot';

import './style/tooltip.css';

function UserToolTipModule(props){

    let xhr;

    const [ toolTipLoading, setToolTipLoading ] = useState(false);
    const [ toolTip, setToolTip ] = useState(null);

    useEffect(() => {
        return () => {
            if (xhr && xhr.abort) xhr.abort();
        }
    },[])

    useEffect(() => {
        if (props.refreshToolTips === true && toolTip !== null) setToolTip(null);
    },[props.refreshToolTips])

    function loadUserToolTip(){
        if (toolTip === null && toolTipLoading === false){
            setToolTipLoading(true);
            xhr = $.ajax({url:'/member/' + props.memberId + '/tooltip/'}).done(function(res){
                setToolTip(res.data);
                setToolTipLoading(false);
            })
        }
    }

    function onUserNameClick(e){
        if (props.onUserNameClick){
            e.preventDefault();
            props.onUserNameClick(e)
        }
    }

    let toolTipDisplay, toolTipClassName = "mytooltip-container";
    if (toolTip !== null) {
        toolTipDisplay = GenerateToolTipTemplate(toolTip);
        toolTipClassName = "mytooltip-container post-get-content"
    } else if (toolTipLoading === true) {
        toolTipDisplay = <LoadingDot/>
    }

    let elementDomDisplay;
    if (props.layout === "new"){
        elementDomDisplay = (
            <a className={"product-author-large " + props.userNameClassName} href={"/u/"+props.username} onMouseEnter={loadUserToolTip}  data-tip="" data-for={props.toolTipId} >
                <figure>
                    <img src={props.imgUrl} title="Supporter info"/>
                </figure>
            </a>
        )
    } else {
        elementDomDisplay = (
            <a style={props.style} className={props.userNameClassName} onMouseEnter={loadUserToolTip} onClick={e => onUserNameClick(e)} data-tip="" data-for={props.toolTipId} href={"/u/"+props.username}>
                {props.imgUrl ? <img width={props.imgSize ? props.imgSize + "px" : "40px"} height={props.imgSize ? props.imgSize + "px" : "40px"} src={GenerateImageUrl(props.imgUrl)}/> : ""} 
                {props.showUserName !== false ? <span>{props.username}</span> : ""} 
            </a>
        )
    }

    return (
        <React.Fragment>
            {props.showBy === true ? "by " : ""}
            {elementDomDisplay}
            <div style={{display:"inline-block"}} className="tool-tip-container">
                <ReactTooltip 
                    id={props.toolTipId}
                    className={toolTipClassName}
                    place={props.place ? props.place : "right"}
                    effect={props.effect ? props.effect : "solid"}
                    type={props.type ? props.type : "light"}
                    backgroundColor={props.backgroundColor ? props.backgroundColor : "#ededed"}
                    borderColor={props.borderColor ? props.borderColor : "#cccccc"}
                    border={props.border ? props.border : true}
                    getContent={[() => { return toolTipDisplay}]}
                />
            </div>
        </React.Fragment>
    )
}

export default UserToolTipModule;