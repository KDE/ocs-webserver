import React, { useState, useEffect, useRef, lazy, Suspense } from "react"
import ScoreCircleModule from '../../common/score-circle-module';
import { getImageUrl } from './product-browse-helpers';
import { GenerateImageUrl, FormatDate } from '../../common/common-helpers';
import LoadingDot from '../../common/loading-dot';
const MusicPlayer = lazy(() => import('./music-player'));
import '../style/music-player.css';
import '../style/picture-grid.css';
import UserToolTipModule from '../../common/user-tooltip-module';

export function ProductBrowseItem(props){

    const browseListType = props.browseListType;
    const p = props.product;

    let showIndex = true;

    function onBrowseItemClick(e,p){
        e.preventDefault();
        props.onChangeUrl("/p/"+p.project_id,p.title,parseInt(p.project_category_id));
    }

    function onUserNameClick(){
        props.onChangeUrl('/u/'+p.username,p.username);
    }

    function onImageLoadError(){
        const ajaxUrl = window.location.origin + "/p/"+p.project_id+"/loadfilesjson";
        $.ajax({
            url: ajaxUrl
        }).done(function(res) {
            let newImgUrl;
            res.forEach(function(f,index){
                if ( f.type.split('/')[0] === "image"){
                    newImgUrl = f.url.replace(/%2F/g,'/').replace(/%3A/g,':');
                }
            });
            if (!newImgUrl){
                newImgUrl = "https://cn.opendesktop.";
                newImgUrl += window.location.host.endsWith('org') === true || window.location.host.endsWith('com') === true  ? "org" : "cc";
                newImgUrl += "/cache/" + Math.ceil(props.itemWidth * 2) + "x" + Math.ceil(props.imgHeight * 2) + "/img/default.png";                 
            }
            setImgUrl(newImgUrl);
        });
    }

    let initImgUrl;
    if (browseListType === "standard") initImgUrl = GenerateImageUrl(p.image_small,167,167,'-0');
    else initImgUrl = getImageUrl(p,props.itemWidth,props.imgHeight);
    const [ imgUrl, setImgUrl ] = useState(initImgUrl);

    let itemLink = window.json_serverUrl;
    itemLink = window.is_show_real_domain_as_url === 1 ? "/" : "/s/" + json_store_name + "/";
    itemLink += p.type_id === "3" ? "c" : "p";
    itemLink += "/" + p.project_id;

    let productBrowseItemDisplay = (
        <StandardBrowseListItem 
            {...props}
            product={p}
            imgUrl={imgUrl}
            itemLink={itemLink}
            showIndex={showIndex}
        />
    )
    if (props.browseListType === "music"){
        productBrowseItemDisplay = (
            <MusicBrowseListItem 
                product={p}
                imgUrl={imgUrl}
                itemLink={itemLink}
            />
        )
    } else if (props.browseListType === "books"){
        productBrowseItemDisplay = (
            <BooksBrowseListItem 
                product={p}
                imgUrl={imgUrl}
                itemLink={itemLink}
            />
        )
    } else if (props.browseListType === "picture"){
        productBrowseItemDisplay = (
            <PictureBrowseListItem 
                product={p}
                imgUrl={imgUrl}
                itemLink={itemLink}
            />
        )        
    }

    return (
        <React.Fragment>
            {productBrowseItemDisplay}
        </React.Fragment>
    )

}

function StandardBrowseListItem(props){

    const p = props.product;
    const imgUrl = props.imgUrl;
    const itemLink = props.itemLink;

    const [ isFavorited, setIsFavorited ] = useState(true);
    const [ loading, setLoading ] = useState(false);

    function onFollowClick(e){
            setLoading(true);
            $.ajax({url: "/p/" + p.project_id + "/followproject/",cache: false}).done(function( response ) {
                setLoading(false);
                let newIsFollowerValue
                if (response.action === 'delete'){
                    newIsFollowerValue = false;
                } else {
                    newIsFollowerValue = true;
                }
                setIsFavorited(newIsFollowerValue);
            });   
    }

    /** STANDARD */
    let currentPage = 0, itemsPerPage = 10;
    if (props.currentPage) currentPage = parseInt(props.currentPage) - 1;
    if (props.itemsPerPage) itemsPerPage = parseInt(props.itemsPerPage); 
    const indexNumber = (currentPage * itemsPerPage) + (props.rowIndex + 1);

    let versionDisplay;
    if (p.version) versionDisplay =  <span className="version">{p.version}</span>
    
    let packageTypesDisplay;
    if (p.package_names){
        const packageTypes = p.package_names.split(',').map((pt,index) => (
            <span key={index} className="packagetypeos">{pt}</span>
        ))
        packageTypesDisplay = <div className="packagetypes"> {packageTypes} </div>
    }

    let availableForDisplay;
    if (p.tags_availablefor){
        const availablefor = p.tags_availablefor.split(',').map((af,index) => (
            <span key={index} className="tag-availablefor">{af}</span>
        ))
        availableForDisplay = (
            <div className="availablefor">
                <span className="tag-availablefor-label">Available as/for:</span>
                {availablefor} 
            </div>
        )
    }

    let productInfoDisplay;
    if (p.count_comments !== "0"){
        productInfoDisplay = (
            <div className="product-info">
                <span className="ctn-comments text-small font-bold">
                    {p.count_comments} comment{(p.count_comments === "1" ? "" : "s")}
                </span>
            </div>
        )
    }

    let plingsDisplay;
    if (p.count_plings && p.count_plings !== "0"){
        plingsDisplay = <p className="plinged-display">Plings {p.count_plings}</p>
    }

    let heartDisplay;
    if (props.browseListType === "myfav"){
        let heartBtnContent = (
            <span className={"pui-heart " + (isFavorited === true ? "active" : "")} onClick={onFollowClick}>
                <svg svg="" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 16" fill-rule="evenodd" clip-rule="evenodd" stroke-linejoin="round" stroke-miterlimit="2">
                    <g transform="matrix(1,0,0,1,-238,-365.493)">
                        <path 
                        d="M239.99,373.475L246,379.485L252.01,373.475C253.376,372.109 253.376,369.891 252.01,368.525C250.644,367.159 248.427,367.159 247.061,368.525L246,369.586L244.939,368.525C243.573,367.159 241.356,367.159 239.99,368.525C238.624,369.891 238.624,372.109 239.99,373.475Z">
                        </path>
                    </g>
                </svg>
            </span>
        )
        if (loading === true) heartBtnContent = <span style={{paddingLeft: "12px"}} className="pui-heart"><LoadingDot/></span>
        heartDisplay = (
            <div className="heart-button">
                {heartBtnContent}
            </div>
        )
    }

    let adminScoreDisplay;
    if (props.authMember && props.authMember.isAdmin === true){
        adminScoreDisplay = (
            <div className="old-score-info">
                Score old: {p.laplace_score_old}% / Score test: {p.laplace_score_test / 100}
            </div>
        )
    }

    let pbiicss = "product-browse-image";
    if (props.browseListType === "picture") pbiicss += "-w";
    
    console.log(props.browseListType);
    
    return (
        <div className={"product-browse-list-item container-wide " + props.browseListType} id={"product-" + p.project_id}>
            <div className="product-browse-item-row">
                <div className="rownum">
                    {heartDisplay}
                    <span className="index">{indexNumber}</span>
                </div>
                <div className="product-browse-item">
                    <a  href={itemLink} className="product-browse-item-wrapper explore-product-imgcolumn">
                        <div className={pbiicss}>
                            <img src={imgUrl} alt={p.title} />
                        </div>
                    </a>
                    <div className="item-info-main explore-product-details">
                        <div className="title">
                            <h2>
                                <a href={itemLink}>
                                    {p.title} {versionDisplay}
                                </a>
                            </h2>
                        </div>
                        <div className="subtitle font-bold">
                            <div className="subtitle-cat">{p.cat_title}</div>
                            <div className="subtitle-auth">
                                <UserToolTipModule 
                                    toolTipId={"user-tool-tip-" + p.project_id }
                                    toolTipClassName={"user-product-popover-container"}
                                    username={p.username}
                                    memberId={p.member_id}
                                    userNameClassName=""
                                    showUserName={true}
                                    showBy={true}
                                    place={"right"}
                                />    
                            </div>
                        </div>
                        <div className="description">
                            {p.description}
                        </div>
                        {packageTypesDisplay}
                        {availableForDisplay}
                        {productInfoDisplay}
                    </div>
                    <div className={"item-info-aside explore-product-plings" + (props.authMember && props.authMember.isAdmin === true ? " show-old-score" : "")}>
                        {adminScoreDisplay}
                        <div className="bmIcXH" style={{marginLeft:"10px"}}>
                        <ScoreCircleModule 
                            score={p.laplace_score}
                            size={48}
                        />
                        </div>
                        {plingsDisplay}
                        <p className="date-display">{FormatDate(p.changed_at)}</p>
                    </div>
                </div>
            </div>
        </div>
    )
}

function MusicBrowseListItem(props){
    
    let xhr = null;

    const p = props.product;
    const [ productFilesFetched, setProductFilesFetched ] = useState(false);
    const [ productFiles, setProductFiles ] = useState();
    const [ loading, setLoading ] = useState(false);

    React.useEffect(() => {
        // onMusicProductLoad();
        return () => {
            if (xhr && xhr.abort) xhr.abort(); 
        }
    },[])

    function fetchMusicFiles(){
        setLoading(true);
        setProductFilesFetched(true);
        xhr = $.ajax({
            url: window.location.origin + "/p/"+p.project_id+"/loadfilesjson"
        }).done(function(res) {
            let newProductFiles = [];
            res.forEach(function(f,index){
                if ( f.type.split('/')[0] === "audio" ||  f.type.split('/')[1] === "ogg"){
                    let nf = f;
                    nf.musicSrc = f.url.replace(/%2F/g,'/').replace(/%3A/g,':');
                    nf.cover = props.imgUrl;
                    newProductFiles.push(nf);
                }
            });
            setProductFiles(newProductFiles);
            setLoading(false);
        });
    }

    let musicPlayerDisplay;
    if (productFilesFetched === false || loading === true){
        let iconDispaly = (
            <span style={{cursor:"pointer"}} onClick={fetchMusicFiles} className="play-icon-wrapper">
                <svg fill="currentColor" preserveAspectRatio="xMidYMid meet" height="1em" width="1em" viewBox="0 0 40 40" className="play-icon">
                    <g><path d="m20.1 2.9q4.7 0 8.6 2.3t6.3 6.2 2.3 8.6-2.3 8.6-6.3 6.2-8.6 2.3-8.6-2.3-6.2-6.2-2.3-8.6 2.3-8.6 6.2-6.2 8.6-2.3z m8.6 18.3q0.7-0.4 0.7-1.2t-0.7-1.2l-12.1-7.2q-0.7-0.4-1.5 0-0.7 0.4-0.7 1.3v14.2q0 0.9 0.7 1.3 0.4 0.2 0.8 0.2 0.3 0 0.7-0.2z"></path></g>
                </svg>                                    
            </span>
        )
        if (loading === true) iconDispaly = <span style={{padding:"9px 7px 11px 14px"}}><LoadingDot/></span>
        musicPlayerDisplay = (
            <div className="product-browse-music-player-wrapper">
                <div id="music-player-control-panel">
                    <div className={"music-player-controls-bar"}>
                        <div className="music-player-controls-wrapper">
                            <div className="music-player-audio-control">
                                <span></span>
                                {iconDispaly}
                                <span></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        )
    }
    
    if (productFiles && productFiles.length > 0){
        musicPlayerDisplay = <MusicPlayerWrapper {...props} items={productFiles}  />
    }

    return (
        <div className="pui-card">
            {musicPlayerDisplay}
            <a href={props.itemLink} title="link to product page">
            <figure>
                <img src={props.imgUrl}/>
            </figure>
            <div className="pui-card-title">
                <h3>{p.title}</h3>
                <div className="likes-counter">
                    <div className="hearts-container">
                        <p>
                            <span className="pui-heart pui-heart-rel">
                                <svg svg="" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 16" fill-rule="evenodd" clip-rule="evenodd" stroke-linejoin="round" stroke-miterlimit="2">
                                    <g transform="matrix(1,0,0,1,-238,-365.493)">
                                        <path d="M239.99,373.475L246,379.485L252.01,373.475C253.376,372.109 253.376,369.891 252.01,368.525C250.644,367.159 248.427,367.159 247.061,368.525L246,369.586L244.939,368.525C243.573,367.159 241.356,367.159 239.99,368.525C238.624,369.891 238.624,372.109 239.99,373.475Z"></path>
                                    </g>
                                </svg>
                            </span>
                            {p.cntLikes} Likes
                        </p>
                    </div>
                </div>
            </div>
            <div className="pui-card-info">
                <p>{p.cat_title}</p>
                <p>
                <UserToolTipModule 
                        toolTipId={"user-tool-tip-" + p.project_id }
                        toolTipClassName={"user-product-popover-container"}
                        username={p.username}
                        memberId={p.member_id}
                        userNameClassName=""
                        showUserName={true}
                        showBy={true}
                        place={"right"}
                    />

                </p>
            </div>
            </a>
        </div>
    )
}

function BooksBrowseListItem(props){
    const p = props.product;
    return (
        <div className="pui-card">
            <a href={props.itemLink} title="link to product page">
            <figure>
                <img src={props.imgUrl}/>
            </figure>
            <div className="pui-card-title">
                <h3>{p.title}</h3>
                <p>
                    <UserToolTipModule 
                        toolTipId={"user-tool-tip-" + p.project_id }
                        toolTipClassName={"user-product-popover-container"}
                        username={p.username}
                        memberId={p.member_id}
                        userNameClassName=""
                        showUserName={true}
                        showBy={true}
                        place={"right"}
                    />    
                </p>
                <div className="likes-counter">
                    <div className="hearts-container">
                        <p>
                            <span className="pui-heart pui-heart-rel">
                                <svg svg="" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 16" fill-rule="evenodd" clip-rule="evenodd" stroke-linejoin="round" stroke-miterlimit="2">
                                    <g transform="matrix(1,0,0,1,-238,-365.493)">
                                        <path d="M239.99,373.475L246,379.485L252.01,373.475C253.376,372.109 253.376,369.891 252.01,368.525C250.644,367.159 248.427,367.159 247.061,368.525L246,369.586L244.939,368.525C243.573,367.159 241.356,367.159 239.99,368.525C238.624,369.891 238.624,372.109 239.99,373.475Z"></path>
                                    </g>
                                </svg>
                            </span>
                            {p.cntLikes} Likes
                        </p>
                    </div>
                </div>
            </div>
            </a>
        </div>
    )
}

function MusicPlayerWrapper(props){
    return (
      <div>
        <Suspense fallback={''}>
            <MusicPlayer 
                {...props}
            />
        </Suspense>
      </div>
    )
}

function PictureBrowseListItem(props){
    const p = props.product;
    return (
            <div className="product-browse-item-picture">
                <a href={props.itemLink} title="link to product page">
                    <div className="item-picture-cover">
                        <img src={props.imgUrl} alt=""/>
                    </div>
                    <div className="item-picture-info">
                        <h2>{p.title}</h2>
                        <p className="item-picture-left">{p.cat_title}</p>
                        <p className="item-picture-right">
                            <UserToolTipModule 
                                toolTipId={"user-tool-tip-" + p.project_id }
                                toolTipClassName={"user-product-popover-container"}
                                username={p.username}
                                memberId={p.member_id}
                                userNameClassName=""
                                showUserName={true}
                                showBy={true}
                                place={"right"}
                            />  
                        </p>
                    </div>
                </a>
            </div>
        )
}


/* OLD PRODUCT BROWSE ITEM CODE */

    /*const productBrowseItemLikesDislpay = (
        <div className="likes-counter">
            <div className="hearts-container">
                <span className="glyphicon glyphicon-heart"></span>
                <span className="glyphicon glyphicon-heart-empty"></span>
            </div>
            ({p.count_follower}) Likes
        </div>
    )
        
    let itemInfoDisplay,
        musicItemInfoDisplay, 
        musicPlayerDisplay,
        showIndex,
        itemInfoHeight,
        wrapperCssClass,
        imgContainerCssClass;
    
    if (browseListType === "picture") {
        itemInfoDisplay = (
            <div className="product-browse-item-info">
                <h2>{p.title}</h2>
                <span>{p.cat_title}</span>
                <span>by <b>{p.username}</b></span>
            </div>
        )
    } else if (browseListType === "apps") {
        itemInfoDisplay = (
            <div className="product-browse-item-info">
                <h2>{p.title}</h2>
                <span>{p.cat_title}</span>
                <span>by <b>{p.username}</b></span>
            </div>
        )
    } else if (browseListType === "phone-pictures"){
        itemInfoDisplay = (
            <div className="product-browse-item-info">
                <h2>{p.title}</h2>
            </div>
        )        
    } else if (browseListType === "comics"){
        itemInfoDisplay = (
            <div className="product-browse-item-info">
                <h2>{p.title}</h2>
                {productBrowseItemLikesDislpay}
            </div>
        )
    } else if (browseListType === "books"){
        itemInfoDisplay = (
            <div className="product-browse-item-info">
                <h2>{p.title}</h2>
                <span>{p.username}</span>
                {productBrowseItemLikesDislpay}
            </div>
        )
    } else if (browseListType === "music" || browseListType === "music-test"){
        musicItemInfoDisplay = (
            <div className="product-browse-music-item-info">
                <h2>{p.title}</h2>
                {productBrowseItemLikesDislpay}
                <span>{p.cat_title}</span>
                <span>by <b>{p.username}</b></span>
            </div>            
        );
        if (productFiles && productFiles.length > 0){
                musicPlayerDisplay = (
                    <MusicPlayerWrapper 
                        product={p}
                        items={productFiles} 
                        imgUrl={imgUrl}
                        viewMode={props.viewMode}
                        onMediaItemUpdate={props.onMediaItemUpdate}
                    />
                )
        }
    } else if (browseListType === "videos"){
        itemInfoDisplay = (
            <div className="product-browse-item-info">
                <h2>{p.title}</h2>
                {productBrowseItemLikesDislpay}
                <div className="info-container">
                    <span>{p.cat_title}</span>
                    <span>by <b>{p.username}</b></span>
                </div>
            </div>
        )
    } else if (browseListType === "skills") {
        itemInfoDisplay = (
            <div className="product-browse-item-info browse-type-skills">
                <h2>{p.title}</h2>
                <span>by <b>{p.username}</b></span>
                <span>{p.description}</span>
            </div>
        )
    } else if (browseListType === "favorites"){

        itemInfoHeight = props.imgHeight;
        showIndex = true;
        itemInfoDisplay = (
            <div className="product-browse-item-info">
                <div className="info-container">
                    <h2>{p.title}</h2>
                    <span>{p.cat_title}</span>
                    <span>by <b>{p.username}</b></span>
                </div>
                <div className="score-container">
                    <div className="explore-product-plings">
                        <div className="rating">
                            <div className="rating-text">
                                <small className="center-block text-center">
                                   Score {p.laplace_score / 10}%
                                </small>
                            </div>
                            <div className="progress">
                                <div className="progress-bar" style={{"backgroundColor":"#c8c8c8","width":(p.laplace_score / 10) + "%"}}>
                                </div>
                                <div className="progress-bar" style={{"backgroundColor":"#eeeeee","opacity":"0.5","width":( 100 - (p.laplace_score / 10)) + "%"}}>           
                                </div>
                            </div>
                        </div>
                        <div className="collected">
                            <span>{p.created_at}</span>
                        </div>
                    </div>
                </div>
            </div>
        )
    } else if (browseListType === "standard"){
        showIndex = true;
        wrapperCssClass = "explore-product col-lg-12 col-md-12 col-sm-12 col-xs-12";
        imgContainerCssClass = "col-lg-2 col-md-2 col-sm-2 col-xs-2 explore-product-imgcolumn";
        itemInfoDisplay = <StandardBrowseListItem showDescription={props.showDescription} product={p}/>
    }
    
    let itemInnerTemplateDisplay;
    if (browseListType === "standard"){
        itemInnerTemplateDisplay = (
            <div className={"wrapper " + wrapperCssClass}>
                {indexDisplay}
                <a href={itemLink} style={{cursor:"pointer"}} className={"product-browse-item-wrapper " + imgContainerCssClass}>
                    <div className="product-browse-image">
                        <img src={imgUrl} height={props.imgHeight} onError={onImageLoadError}/>
                    </div>
                </a>
                {itemInfoDisplay}
            </div>            
        )
    } else {
        itemInnerTemplateDisplay = (
            <div className={"wrapper " + wrapperCssClass}>
                {indexDisplay}
                {musicPlayerDisplay}
                <a href={itemLink} style={{cursor:"pointer"}} className={"product-browse-item-wrapper " + imgContainerCssClass}>
                    <div className="product-browse-image">
                        <img src={imgUrl} height={props.imgHeight} onError={onImageLoadError}/>
                        {musicItemInfoDisplay}
                    </div>
                    {itemInfoDisplay}
                </a>
            </div>
        )
    }

    let productItemWidth = props.itemWidth;
    if (browseListType === "standard") productItemWidth = "100%";

    return (
        <div className={"product-browse-item " + browseListType} id={"product-" + p.project_id} style={{"width":productItemWidth}}>
            {itemInnerTemplateDisplay}
        </div>
    ) */

export default ProductBrowseItem;