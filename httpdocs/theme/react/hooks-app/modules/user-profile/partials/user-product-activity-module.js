import { useEffect, useState } from "react";
import { GenerateImageUrl, GenerateColorBasedOnRatings, FormatDate } from "../../common/common-helpers";
import DateOrTimeAgoModule from '../../common/date-or-timeago';
import ScoreCircleModule from '../../common/score-circle-module';
import UserToolTipModule from '../../common/user-tooltip-module';
import Pagination from '../../common/pagination';
import { isMobile } from 'react-device-detect';
import React from "react";
import LoadingDot from "../../common/loading-dot";

function UserProductActivityModule(props){
    let xhr;

    let initItems = [];
    if (props.items) initItems = props.items;
    const [ items, setItems ] = useState(initItems);

    let initCurrentPage = 1;
    if (props.currentPage) initCurrentPage = props.currentPage;
    const [ currentPage, setCurrentPage ] = useState(initCurrentPage);

    const [ loading, setLoading ] = useState(false);
 
    useEffect(() => {
        return () => {
            if (xhr && xhr.abort){
                xhr.abort();
            }
        }
    },[])

    function onPageChange(val){
        if (loading === false){
            setLoading(true);
            const type = props.type === "plinged" ? "plings" : props.type === "rated" ? "rates" : props.type;
            const url = "/u/" + props.member.username + "/" +  type + "?page=" + val;
            xhr = $.ajax({url:url}).done(function(res){
                let attr = "comments";
                if (props.type === "userMorerates") attr = "rated";
                else if (props.type === "userMoreplings") attr = "plings";
                else if (props.type === "userMorelikes") attr = "likes"
                setCurrentPage(val);
                setItems(res[attr]);
                setLoading(false);
                props.onPageChange(res[attr],val);
            });
        }
    }

    let itemsDisplay;
    if (items.length > 0){
        itemsDisplay = items.map((item,index) => (
            <UserProductActivityListItem 
                key={index}
                index={index}
                item={item}
                type={props.type}
            />
        ))
    }

    let paginationDisplay;
    if (Math.ceil(props.totalItems / props.pageLimit) > 1){
        let loadingDisplay;
        if (loading) {
            loadingDisplay = <LoadingDot/>
        }
        paginationDisplay = (
            <div className="pagination-container" style={{display:"flex"}}>
                <div style={{float:"left"}}>
                <Pagination 
                        numberOfPages={Math.ceil(props.totalItems / props.pageLimit)}
                        onPageChange={onPageChange}
                        currentPage={currentPage}
                    />  
                </div>
                {loadingDisplay}
            </div>
        )
    }
    
    const type = props.type === "comments" ? "comments" : props.type === "likes" ? "likes" : "rated";

    return (
        <div id={"my-"+type+"-tabs"} className={"user-product-activity-list-item container-normal"}>
            <div id={"my-"+type+"-tabs-content"}>
                {paginationDisplay}
                {itemsDisplay}
                {paginationDisplay}
            </div>
        </div>
    )
}

function UserProductActivityListItem(props){

    const item = props.item;
    
    function onProductLinkClick(e){
        e.preventDefault();
        props.onChangeUrl("/p/"+item.project_id,item.title,parseInt(item.project_category_id));
    }

    const imgUrl = GenerateImageUrl(item.image_small,80,80);

    const scoreCircleDisplay = (
        <div className="col-lg-2 col-md-2 col-sm-12" style={{paddingLeft:(isMobile ? "15px" : "5px"),height:(isMobile ? "60px" : "auto")}}>
            <ScoreCircleModule 
                score={item.laplace_score}
                size={42}
            />
        </div>

    )

    let containerClass  = "product-browse-item user-profile-activity-item", 
        username = item.project_username, 
        memberId = item.project_member_id,
        itemInfoDisplay,
        commentsDisplay;

    if (props.type.indexOf("comments") > -1){
        
        username = item.username;
        memberId = item.project_member_id;

        itemInfoDisplay = (
            <div className="item-info">  
                <div dangerouslySetInnerHTML={{__html:item.comment_text}}></div>
                <br/>
                <span className="createat">
                    <DateOrTimeAgoModule date={item.comment_created_at} numDays={3} />
                </span>
            </div>
        )
        if (item.count_comments !== "0") commentsDisplay = item.count_comments + " comment" + (parseInt(item.count_comments) === 1 ? "s" : "");
    
    } else if (props.type.indexOf("userMorerates") > -1){
        containerClass += " rated-item"
        itemInfoDisplay = (
            <React.Fragment>
                <div className="score-wrapper">
                    {scoreCircleDisplay}
                </div>
                <div className="item-info">
                    <span style={{marginRight:"5px"}} className={"pui-badge pui-score-tag-"+parseInt(item.score)}>{item.score}</span>
                    <DateOrTimeAgoModule date={item.rating_created_at} numDays={3} />
                    <p className="rating-text">{item.comment_text}</p>
                </div>
            </React.Fragment>
        )
    
    } else if (props.type.indexOf("userMoreplings") > -1){
        containerClass += " plinged-item"
        itemInfoDisplay = (
            <React.Fragment>
                <div className="score-wrapper">
                    {scoreCircleDisplay}
                </div>
                <div className="item-info">
                    <span className="pui-pling">
                        <svg xmlns="http://www.w3.org/2000/svg" fill-rule="evenodd" clip-rule="evenodd" stroke-linejoin="round" stroke-miterlimit="2" viewBox="0 0 24 24">
                            <path d="M17.467348 7.9178719c-.347122-2.7564841-3.201417-4.3323187-5.789058-3.9194079-1.7356129.2821847-3.460181.6085251-5.1768601.9799707.2997877 1.2572492.6011532 2.5146567.9025188 3.7720641.0899363.3771432.1356934.5656356.2256297.942937.8283607 3.4577522 1.6551436 6.9153452 2.4835046 10.3730972 1.136037-.333937 2.278386-.645559 3.427047-.934707-.362901-1.870206-.725802-3.740411-1.088703-5.610617 0 0 .61062-.129935.929342-.194823 2.440903-.497266 4.406879-2.86078 4.086579-5.4085141zm-4.768202 1.6774402c-.389724.0720101-.583797.1090439-.971943.1854854-.121493-.6234019-.241408-1.2468038-.362901-1.8702057.397613-.074701.59642-.1107848.994033-.1808958.523839-.087995 1.065035.2329646 1.154971.7609334.08994.5200555-.298209 1.0046598-.81416 1.1046827z" fill="#fff"></path>
                        </svg>
                    </span>
                    <DateOrTimeAgoModule date={item.created_at} numDays={3}/>
                </div>
            </React.Fragment>
        )
    
    } else if (props.type.indexOf("userMorelikes") > -1){
        containerClass += " liked-item";
        itemInfoDisplay = (
            <React.Fragment>
                {scoreCircleDisplay}
                <div className="item-info">
                    <span className="pui-heart active">
                        <svg svg="" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 16" fill-rule="evenodd" clip-rule="evenodd" stroke-linejoin="round" stroke-miterlimit="2">
                            <g transform="matrix(1,0,0,1,-238,-365.493)">
                                <path d="M239.99,373.475L246,379.485L252.01,373.475C253.376,372.109 253.376,369.891 252.01,368.525C250.644,367.159 248.427,367.159 247.061,368.525L246,369.586L244.939,368.525C243.573,367.159 241.356,367.159 239.99,368.525C238.624,369.891 238.624,372.109 239.99,373.475Z">
                                </path>
                            </g>
                        </svg>
                    </span>
                    <DateOrTimeAgoModule date={item.created_at} numDays={3}/>
                </div>
            </React.Fragment>
        )

    }

    return (
        <div className="product-browse-list-item container-wide standard">
            <div className={containerClass}>

                <a href={"/p/"+item.project_id} className="product-browse-item-wrapper explore-product-imgcolumn">
                    <div className="product-browse-image">
                        <img alt={item.title} src={imgUrl}/>
                    </div>
                </a>

                <div className="item-info-main explore-product-details">
                    <div className="title font-bold">
                        <h2>
                            <a  href={"/p/"+item.project_id}>
                                {item.title}
                            </a>
                        </h2>
                    </div>
                    <div className="subtitle font-bold">
                        {item.cat_title}
                    </div>
                    <div className="subtitle">
                        <UserToolTipModule 
                            showBy={true} 
                            username={username} 
                            memberId={memberId}
                            toolTipId={"user-activity-"+props.type+"-"+props.index+"-list-item-"+item.project_id+"-user-"+memberId}
                            className={"user-activity-list-item"}
                        />
                    </div>
                    <div className="subtitle">
                        {commentsDisplay}
                    </div>
                </div>

                {itemInfoDisplay}   
            </div>
        </div>
        
    )
}

export default UserProductActivityModule;