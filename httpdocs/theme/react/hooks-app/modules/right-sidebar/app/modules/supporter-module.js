
import React from 'react';
import UserToolTipModule from '../../../common/user-tooltip-module';
import HomePageView from '../../../homepage-view/homepage-view';

function SupportersModule(props){

    let supportersDisplay;
    if (props.supporters && props.supporters.length > 0){
        supportersDisplay = props.supporters.map((sup,index) => {
                return (
                    <SupportersModuleListItem 
                        key={index}
                        index={index}
                        supporter={sup}
                        onChangeUrl={props.onChangeUrl}
                    />
                )
        });
    }

    return (
        <div className="support-block text-center">
            <p style={{marginBottom:"8px",padding: "0 5px"}}>
                <b>{props.countSupporters}</b> people support those who create freedom. <a href="/supporters" className="link-primary">See all</a>
            </p>
            {supportersDisplay}
            <br/>
            <p>
                <a href="/support" className="pui-btn pling">{ props.authMember && props.authMember.isSupporter == 2 ? "Thank you for your support!" : "Become a supporter"}</a> <br/>
            </p>
        </div>
    )
}

function SupportersModuleListItem(props){

    const sup = props.supporter;

    return (
        <UserToolTipModule 
            toolTipId={"supporter-tool-tip-" + props.index + "-" + sup.member_id}
            toolTipClassName={"supporter-box-list-item-popover-container"}
            username={sup.username}
            memberId={sup.member_id}
            userNameClassName="tooltipuserleft supporter-box-list-item"
            showUserName={false}
            imgUrl={sup.profile_image_url}
            imgSize={25}
            style={{margin:"2px"}}
            onUserNameClick={e => onSupporterClick(e)}
            place={"left"}
            layout={"new"}
        />
    )
}

/*

*/

export default SupportersModule;