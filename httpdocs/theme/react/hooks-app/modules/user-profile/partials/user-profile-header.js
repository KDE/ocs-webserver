import React from 'react';
import { useContext, useState } from 'react';
import { FormatDate, GenerateImageUrl } from '../../common/common-helpers';
import DateOrTimeAgoModule from '../../common/date-or-timeago';
import { Context } from '../context/context-provider';

import openCodeLogo from '../../../layout/style/media/opencode-icon.svg';
// import gitHubLogo from '../../../layout/style/media/github-icon.svg';
import webSiteLogo from '../../../layout/style/media/link-icon.svg';
// import fbLogo from '../../../layout/style/media/facebook-social-icon-circle.svg';
import twlogo from '../../../layout/style/media/twitter-social-icon-circle.svg';
import dmLogo from '../../../layout/style/media/od-dm-icon.svg';
import plingLogoSmall from '../../../layout/style/media/od-dm-icon.svg';
import SupporterSvg from '../../../layout/style/media/supporter.svg';

function UserProfileHeader(props){
    
    const { userProfileState } = useContext(Context);
    const aboutMe = userProfileState.data.aboutmeUserInfo
    const earnInfo = userProfileState.data.earnInfo
    const member = userProfileState.data.member
    const affiliates = userProfileState.data.affiliates
    const statistics = userProfileState.data.stat
    const mainProject = userProfileState.data.mainProject    
    const urlGitlab = userProfileState.data.url_gitlab
    const urlForum = userProfileState.data.url_forum
    const cntGitp = userProfileState.data.cntGitp

    const [ isPlingExcluded, setIsPlingExcluded ] = useState(member.pling_excluded);

    function onPlingExcludedCheckedClick(event){

        event.preventDefault();
        event.stopPropagation();

        let status = 1;
        if (isPlingExcluded) status = 0;

        const url = "/backend/project/doexclude?member_id="+member.member_id+"&pling_excluded=" + status;
        $.ajax({
            url: url,
            success: function (results) {
                if (isPlingExcluded == true) {
                    alert('Project was successfully included for plinging');
                    setIsPlingExcluded('0');

                } else {
                    alert('Project was successfully excluded for plinging');
                    setIsPlingExcluded('1');
                }
            },
            error: function () {
                alert('Service is temporarily unavailable.');
            }
        });
    }

    let imgUrl = member.profile_image_url ? member.profile_image_url : member.avatar;

    const userAvatarImgUrl = GenerateImageUrl(imgUrl,110,110);

    let fullnameDisplay;
    if (member.firstname || member.lastname){
        fullnameDisplay = (
            <React.Fragment>
                <span style={{marginRight:"3px"}} className="glyphicon glyphicon-user"></span>
                <span className="text">{member.firstname} {member.lastname} </span>
            </React.Fragment>
        )
    }

    let cityCountryDisplay;
    if (member.city || member.country ){
        cityCountryDisplay = (
            <React.Fragment>    
                <span style={{marginRight:"5px"}} className="glyphicon glyphicon-map-marker"></span>
                <span className="text">{member.city}, {member.country}</span>
            </React.Fragment>
        )
    }

    let supporterBadgeDisplay;
    if (statistics.donationIssupporterSection && parseInt(statistics.donationIssupporterSection) > 0){
        supporterBadgeDisplay = (
            <div className={"supporter-badge  supporter-badge-large " + (statistics.subscriptionIssupporter === true ? "active" : "inactive")}>
                <span>S{userProfileData.member.isSupporter}</span>
                <img src={SupporterSvg} />
            </div>
        )
    }

    let supportMeButtonDisplay;
    if (member.paypal_mail){
        supportMeButtonDisplay = (
            <a className="pui-btn pling" href={"/support?creator_id="+member.member_id} role="button">Support me</a>
        )
    }

    let activeSupporterSinceDisplay;
    if (statistics.donationActivemonths && parseInt(statistics.donationActivemonths.active_months) > 0){
        activeSupporterSinceDisplay = <span>{(statistics.donationIssupporter === "1" ? " I'm an active supporter" : " I have been a supporter")} for {statistics.donationActivemonths.active_months} months.</span>
    }

    let descriptionDisplay;
    if (mainProject && mainProject.description){
        descriptionDisplay = (
            <React.Fragment>
                <h2 className="title">About Me</h2>
                <p className="us-about-me" dangerouslySetInnerHTML={{__html:mainProject.description.replace(/(?:\r\n|\r|\n)/g, '<br/>')}}></p>
            </React.Fragment>
        )    
    }

    let webSiteDisplay;
    if (member.link_website){
        webSiteDisplay = (
            <p>
                <span className="us-icon-medium">
                    <img src={webSiteLogo}/>
                </span>
                <a href={member.link_website}>{member.link_website}</a>
            </p>
        )
    }

    let twitterDisplay;
    if (member.link_twitter){
        twitterDisplay = (
            <p>
                <span className="us-icon-medium">
                    <img src={twlogo}/>
                </span>                            
                <a href={member.link_twitter}>{member.link_twitter}</a>
            </p>
        )
    }

    let adminActionsDisplay;
    if (userProfileState.data.isAdmin === true){
        adminActionsDisplay = (
            <div style={{position:"absolute",right:"0px",bottom:"0px"}}>
                <span className="page-views">
                    <a style={{marginRight:"3px"}} id="linktohive" target="_blank" href={"http://cp1.hive01.com/usermanager/search.php?username="+member.username}><i>link to hive</i></a>
                    <a id="delete-this" href={"/backend/user/delete?member_id="+member.member_id}><i>delete user</i></a>
                </span>
                <br/>
                <span className="page-views" style={{color: "red"}}>
                    <input  onChange={(event) => onPlingExcludedCheckedClick(event)} type="checkbox" id="pling-excluded-checkbox" checked={isPlingExcluded === '1' ? 'checked' : ''}/>  user-pling-excluded
                </span>
            </div>        
        )
    }

    let adminInfoDisplay;
    let earnInfoDisplay;    
    if (userProfileState.data.isAdmin === true){
        adminInfoDisplay = (
            <p style={{fontStyle:"italic"}}>
               {userProfileState.data.memberScore ? " Score: " + userProfileState.data.memberScore.score : ""}<br/>
                Duplicates: {userProfileState.data.stat.cntDuplicateSourceurl}<br/>
                Member Id: {userProfileState.data.member.member_id}<br/>
                Email Address: {userProfileState.data.member.mail}<br/>
                PayPal Address: {userProfileState.data.member.paypal_mail}<br/>
                First IPv4: {userProfileData.firstLoginData ? userProfileData.firstLoginData.ipv4 : ""}<br/>
                First IPv6: {userProfileData.firstLoginData ? userProfileData.firstLoginData.ipv6 : ""}<br/>
                Last IPv4: {userProfileData.lastLoginData ? userProfileData.lastLoginData.ipv4 : ""}<br/>
                Last IPv6: {userProfileData.lastLoginData ? userProfileData.lastLoginData.ipv6 : ""}<br/>
            </p>
        )
        earnInfoDisplay = (
            <p style={{fontStyle:"italic"}} dangerouslySetInnerHTML={{__html:earnInfo}}></p>
        )
    }

    return (
        <React.Fragment>
            <div className="container-normal us-container">
                <div className="us-user-100">
                    <div className="us-user-profile-grid">
                        <figure>
                            <img src={userAvatarImgUrl}/>
                            {supporterBadgeDisplay}
                        </figure>
                        <div>
                            <p className="us-user-title">  {member.username} </p>
                            <ul>
                                <li>{fullnameDisplay}</li>
                                <li>{cityCountryDisplay}</li>
                            </ul>
                            {supportMeButtonDisplay}
                        </div>
                    </div>
                    {adminActionsDisplay}
                </div>
            </div>
            <div className="container-normal us-info-container">
                <div>
                    <div className="us-about-me mt5">
                        <p dangerouslySetInnerHTML={{__html:aboutMe}}></p>
                    </div>
                    <hr className="divider"/>
                    {adminInfoDisplay}
                    {descriptionDisplay}
                    {earnInfoDisplay}
                    <p> {activeSupporterSinceDisplay} </p>

                    <h2>Connected Accounts</h2>
                    <p>
                        <span className="us-icon-medium">
                            <img src={openCodeLogo}/>
                        </span>
                        <a href={urlGitlab + "/" + member.username }>{'opencode.net/'+member.username} {cntGitp ? "("+cntGitp+")" : "" }</a>
                    </p>
                    {webSiteDisplay}
                    {twitterDisplay}
                    <p>
                        <span className="us-icon-medium">
                            <img src={dmLogo}/>
                        </span>
                        <a href={urlForum + '/' + member.username}>Send DM</a>
                    </p>
                </div>
                <div>
                    <h2>Statistics</h2>
                    <UserProfileHeaderStatitics 
                        statistics={statistics}
                        affiliates={affiliates}
                        member={member}
                    />
                </div>
            </div>
        </React.Fragment>
    )
}

function UserProfileHeaderStatitics(props){
    
    const s = props.statistics;
    
    let supporterSinceRowDisplay;
    if (s.donationIssupporter !== null){
        supporterSinceRowDisplay = (
            <div className="us-card us-card-space text-center">
                <p className="us-card-title-normal"><DateOrTimeAgoModule layout={true} date={s.donationMin} /></p>
                <p className="us-card-description">Supporter since ({(s.donationIssupporter === "1" ? "active" : "inactive")})</p>
            </div>
        )
    }

    return (
        <div className="pling-cards-grid-dense">
            <div className="us-card us-card-space text-center ">
                <p className="us-card-title">{s.cntProducts} </p> 
                <p className="us-card-description">Products</p>
            </div>
            <div className="us-card us-card-space text-center">
                <p className="us-card-title">{s.cntComments} </p>
                <p className="us-card-description">Comments</p>
            </div>
            <div className="us-card us-card-space text-center">
                <p className="us-card-title"> {s.cntPlingsHeGave} </p>
                <p className="us-card-description">
                    Plinged Products
                </p>
            </div>
            <div className="us-card us-card-space text-center">
                <p className="us-card-title"> {s.cntPlingsHeGot} </p> 
                <span className="pui-pling">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill-rule="evenodd" clip-rule="evenodd" stroke-linejoin="round" stroke-miterlimit="2">
                        <path d="M17.467348 7.9178719c-.347122-2.7564841-3.201417-4.3323187-5.789058-3.9194079-1.7356129.2821847-3.460181.6085251-5.1768601.9799707.2997877 1.2572492.6011532 2.5146567.9025188 3.7720641.0899363.3771432.1356934.5656356.2256297.942937.8283607 3.4577522 1.6551436 6.9153452 2.4835046 10.3730972 1.136037-.333937 2.278386-.645559 3.427047-.934707-.362901-1.870206-.725802-3.740411-1.088703-5.610617 0 0 .61062-.129935.929342-.194823 2.440903-.497266 4.406879-2.86078 4.086579-5.4085141zm-4.768202 1.6774402c-.389724.0720101-.583797.1090439-.971943.1854854-.121493-.6234019-.241408-1.2468038-.362901-1.8702057.397613-.074701.59642-.1107848.994033-.1808958.523839-.087995 1.065035.2329646 1.154971.7609334.08994.5200555-.298209 1.0046598-.81416 1.1046827z" fill="#fff"></path>
                        </svg>
                </span>
                <p className="us-card-description">
                    Plings
                </p>
            </div>
            <div className="us-card us-card-space text-center">
                <p className="us-card-title">{(props.affiliates ? props.affiliates.length : "0")}</p>
                <p className="us-card-description">
                    Affiliates
                </p>
            </div>
            <div className="us-card us-card-space text-center">
                <p className="us-card-title"> {s.cntLikesHeGave} </p>
                <p className="us-card-description">
                    Fan of Products
                </p>
            </div>
            <div className="us-card us-card-space text-center">
                <p className="us-card-title"> {s.cntLikesHeGot} </p>
                <span className="pui-heart">
                    <i className="fa fa-heart" style={{color:"#8e44ad"}} aria-hidden="true"></i>
                </span>
                <p className="us-card-description">
                    Fan-Likes 
                </p>
            </div>
            {supporterSinceRowDisplay}
            <div className="us-card us-card-space text-center">
                <p className="us-card-title-normal"><DateOrTimeAgoModule layout={true} date={props.member.created_at} /></p>
                <p className="us-card-description">Joined</p>
            </div>
            <div className="us-card us-card-space text-center">
                <p className="us-card-title-normal"><DateOrTimeAgoModule  layout={true} date={s.userLastActiveTime} /></p>
                <p className="us-card-description">
                    Last time active 
                </p>
            </div>
        </div>
    )
}

export default UserProfileHeader;