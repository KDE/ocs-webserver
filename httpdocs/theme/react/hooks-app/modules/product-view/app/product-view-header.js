import React ,{ useContext, useState } from 'react';
import { Context } from './context/context-provider';
import ReactTooltip from 'react-tooltip';

import ScoreModule from '../../common/score-module';
import CustomModal from '../../common/modal';
import LoadingSpinner from '../../common/loading-spinners';
import TagsModule from '../../common/tags';

import 'react-circular-progressbar/dist/styles.css';
import LinkArrowIcon from '../../../layout/style/media/link-arrow-icon.svg';
import LoadingDot from '../../common/loading-dot';


function ProductHeader(props){

    const { productViewState } = useContext(Context);

    const product = productViewState.product;

    let productScoreBarWithSelectDisplay;
    /*if (window.isAdmin){
        productScoreBarWithSelectDisplay = (
            <ScoreModule 
                type="bar" 
                select={true} 
                product={product}
                user={productViewState.authMember}
                userRatings={productViewState.ratingOfUser}
            />
        )
    }*/

    return (
        <div id="product-title">
            <ProductHeaderTitle  {...props} />
            <div id="product-title-aside">
                <ProductHeaderTags />
                <ProductHeartButton />
                {productScoreBarWithSelectDisplay}
                <ScoreModule 
                    type="circle" 
                    select={true} 
                    product={product}
                    user={productViewState.authMember}
                    userRatings={productViewState.ratingOfUser}
                />
            </div>
        </div>
    )
}

function ProductHeaderTitle(props){
    
    const { productViewState } = useContext(Context);
    const product = productViewState.product;
    const user = productViewState.authMember;

    /*function onProductCategoryClick(e,catLink){
        e.preventDefault();
        props.onChangeUrl(catLink,product.cat_title,parseInt(product.project_category_id));
    }*/

    let productLogoImageUrl = product.image_small;
    if (product.image_small !== null && product.image_small.indexOf('https://') === -1){
        const piuEndsWith = window.location.hostname.endsWith('com') ? 'com' : 'cc';
        productLogoImageUrl = "https://cdn.pling."+piuEndsWith+"/cache/85x85-crop/img/"+product.image_small;
    }

    let productTopicsDisplay;
    if (productViewState.tagsuser && productViewState.tagsuser.length > 0){
        productTopicsDisplay = (
            <TagsModule 
                tags={productViewState.tagsuser}
                user={user}
                product={product}
            />
        )
    }

    let productSourceDisplay = (
        <a className="link-primary" style={{marginLeft:"3px"}} href={"https://opencode.net/"+product.username}>Add the source-code for this project on  opencode.net</a>
    )

    if (productViewState.authMember !== null && productViewState.authMember.isAdmin == true && parseInt(productViewState.cntSameSource) > 0 ){
        let listSameSourceUrlDisplay;
        if (parseInt(productViewState.cntSameSource) > 1){
            listSameSourceUrlDisplay = (
                <PrductHeaderSameSourceCotainer 
                    projectId={product.project_id}
                    cntSameSource={productViewState.cntSameSource}
                />
            )
        }

        productSourceDisplay = (
            <React.Fragment>
                <a className="link-primary" target="_blank" href={product.source_url} rel="nofollow">
                    {product.source_url}
                </a>
                {listSameSourceUrlDisplay}
            </React.Fragment>
        )
    }

    let titleDisplay = <h2>{product.title}</h2>
    if (product.link_1 && product.link_1.length > 0){
        titleDisplay = (
            <h2>
                <a href={product.link_1} target="_NEW" title={product.link_1} rel="nofollow">
                    {product.title} 
                    <span>
                        <img className="pui-external-link" src={LinkArrowIcon} alt=""/>
                    </span>
                </a>
            </h2>
        )
    }

    let productReportedDisplay;
    if (product.amount_reports !== null){
        productReportedDisplay = (
            <span style={{color:"red",fontSize:"small",position: "absolute",right: 0,top: "-15px"}}>
                ({product.amount_reports} times reported. Product will be validated from our moderators.)
            </span>
        )
    }

    let editProductLinkDisplay;
    if (user && user.member_id === parseInt(product.member_id)){
        editProductLinkDisplay = (
            <span className="pui-pill tag" style={{position:"absolute",top:0,right:0}}>
                <a href={"/p/"+product.project_id+"/edit"}><span className="glyphicon glyphicon-pencil"></span> Edit Product </a>
            </span>
        )
    }

    let productRankingDisplay;

    if (product.position && product.position.length > 0){

        let productRankings, labelDisplay;
        if (typeof product.position === "string"){
            let productPositions = product.position.split('&nbsp;&nbsp;')
            productRankings = productPositions.map((pos,index) => (
                <span style={{marginRight:"12px"}} key={index}>{pos}</span>
            ));    
        } else {
            labelDisplay = <span>Pling-Rank: </span>
            productRankings = product.position.map((pos,index) => (
                <span style={{marginRight:"12px"}} key={index}> No.{pos.rank} in {pos.title} </span>
            ));    
        }

        productRankingDisplay = (
            <div className="product-ranking-container">
                {labelDisplay}
                {productRankings}
            </div>
        );
    }

    const productCategoryLink = "/browse?cat="+product.project_category_id+"&order=latest";

    let productSourceContainerDisplay;
    if (productViewState.isCollectionView === false){
        productSourceContainerDisplay = (
            <div className="source-url">
                <p className="pui-popup font-bold inline-block">
                    Source <span className="info-icon">i</span> <span className="pui-popup-body"> (link to git-repo or to original if based on someone elses unmodified work): </span>
                </p>
                <p className="inline-block">
                    {productSourceDisplay}
                </p>
            </div>
        )
    }

    return (
        <React.Fragment>
            <div>
                <figure>
                    <img className="logo" src={productLogoImageUrl}/>                    
                </figure>
            </div>
            <div>
                <div id="product-header-title">
                    {titleDisplay}
                    {editProductLinkDisplay}
                    {productReportedDisplay}
                    {productRankingDisplay}
                    <p id="product-category">
                        <a href={productCategoryLink}> {product.cat_title} </a>
                        {productTopicsDisplay}
                    </p>
                </div>
                {productSourceContainerDisplay}
            </div>
        </React.Fragment>
    )
}

function PrductHeaderSameSourceCotainer(props){

    const [ isOpen, setIsOpen ] = useState(false);
    const [ loading, setLoading ] = useState(true);
    const [ sameSourceUrlList, setSameSourceUrlList ] = useState(null);

    function onOpenSameSourceUrlModalClick(){
        setIsOpen(true);
        $.ajax({url:'/p/'+props.projectId+'/listsamesourceurl'}).done(function(res){
            setSameSourceUrlList(res);
            setLoading(false);
        });
    }

    function closeModal(){
        setIsOpen(false);
    }

    let modalClassName = "custom-fancybox-modal loading-iframe",
        loadingSpinnerDisplay = <LoadingSpinner type="ripples"/>,
        closeModalButton,
        sameSourceUrlListDisplay;

    if (loading === false){
        modalClassName = "custom-fancybox-modal table-view";
        loadingSpinnerDisplay = "";
        closeModalButton = <a className="custom-fancybox-close" onClick={closeModal}></a>;
        sameSourceUrlListDisplay = <div dangerouslySetInnerHTML={{__html:sameSourceUrlList}}></div>
    }

    return (
        <span className="source-number">
            <a onClick={onOpenSameSourceUrlModalClick} style={{color:"red",cursor:"pointer"}} className="same-source-url-modal-button"> 
                [{props.cntSameSource}]
            </a>
            <CustomModal
            isOpen={isOpen}
            hasHeader={false}
            hasFooter={false}
            closeModal={closeModal}
            onRequestClose={closeModal}
            modalClassName={modalClassName}>
                <React.Fragment>
                {closeModalButton}
                {loadingSpinnerDisplay}
                {sameSourceUrlListDisplay}
                </React.Fragment>
            </CustomModal>

        </span>
    )
}

function ProductHeaderTags(props){

    const { productViewState } = useContext(Context);
    const product = productViewState.product;

    let productIsOriginalDisplay;
    if (productViewState.isProductOriginal === true) productIsOriginalDisplay = <span className="pui-pill product-pill"> Original</span>

    let productIsFeaturedDisplay;
    if (product.featured === "1") productIsFeaturedDisplay = <span className="pui-pill product-pill"> Featured</span>

    return (
        <div className="product-header-tags" style={{display:"inline"}}>
            {productIsOriginalDisplay}
            {productIsFeaturedDisplay}
        </div>
    )
}

function ProductHeartButton(props){
    
    const { productViewState } = useContext(Context);
    const product = productViewState.product;

    let memberId = null,
        initIsFollowerValue;
    if (productViewState.authMember){
        if (productViewState.authMember.member_id) memberId = productViewState.authMember.member_id;
        if (productViewState.authMember.isFollower) initIsFollowerValue = productViewState.authMember.isFollower;
    }

    const [ isFollower, setIsFollower ] = useState(initIsFollowerValue);

    let initCntLikesValue = 0;
    if (productViewState.tabCnt && productViewState.tabCnt.cntLikes) initCntLikesValue = productViewState.tabCnt.cntLikes;
    const [ cntLikes, setCntLikes ] = useState(initCntLikesValue);

    const [ loading, setLoading ] = useState(false);
    const [ error, setError ] = useState(null);
    const [ showModal, setShowModal ] = useState(false);

    function onFollowClick(e){        

        e.preventDefault();

        let isAuthorized = true;
        
        if (memberId === null || memberId === undefined || memberId === parseInt(product.member_id)) {
            setShowModal(true);
            isAuthorized = false;
        }

        if (isAuthorized){

            setLoading(true);

            $.ajax({url: "/p/" + product.project_id + "/followproject/",cache: false}).done(function( response ) {

                setLoading(false);
                
                if (response.status === 'error'){
                    
                    setError(response.msg);

                } else {

                    let newIsFollowerValue,
                        newCntFollowersValue = cntLikes;

                    if (response.action === 'delete'){
                        newIsFollowerValue = false;
                        newCntFollowersValue -= 1;
                    } else {
                        newIsFollowerValue = true;
                        newCntFollowersValue += 1;                    
                    }
        
                    setIsFollower(newIsFollowerValue);
                    setCntLikes(newCntFollowersValue);

                }
            });
        }      
    }

    let followsTextCssClass = "plingtext heartnumber heartnumberpurple small"
    let heartCssClass = "plingheart fa fa-heart-o heartgrey ";
    if (isFollower == true){
        followsTextCssClass = "plingtext heartnumber  small";
        heartCssClass = "plingheart fa fa-heart heartproject "
    }

    let textDisplay;
    if (loading === true) {
        textDisplay = <LoadingDot/>
    }
    else textDisplay = cntLikes;

    let modalHeaderDisplay, modalContentDisplay, modalBodyClassName;
    if (error === null){
        if (memberId === null || memberId === undefined){
            modalContentDisplay = (
                <React.Fragment>
                    <div className="please-login">
                        <p>
                        Please Login.
                        </p>
                        <a className="pui-btn primary" href={(json_loginurl ? json_loginurl : "/login/")}>Login</a>
                    </div>
                </React.Fragment>
            )
        } else if (memberId === parseInt(product.member_id)){
            modalHeaderDisplay = "Project owner not allowed";
        }
    } else modalContentDisplay = <React.Fragment>{error}</React.Fragment>

    let heartColorFill = "rgb(223,217,226)"
    if (isFollower === true) heartColorFill = "rgb(142,68,173)";

    return (
        <div className="projectdtailHeart tooltipheart tooltipstered product-heart-button-container" style={{display:"inline-block"}}>
            <div id={"container-follow"+product.project_id} className="container-pling">
                <div data-tip="" style={{cursor:"pointer"}} data-for={"product-heart-button"} onClick={onFollowClick} className="heart-button">
                    <div className="plingtext heartnumber heartnumberpurple small"> {textDisplay} </div>
                    <span className={"pui-heart " + (isFollower === true ? "active" : "")}>
                        <svg svg="" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 16" fill-rule="evenodd" clip-rule="evenodd" stroke-linejoin="round" stroke-miterlimit="2">
                            <g transform="matrix(1,0,0,1,-238,-365.493)">
                                <path 
                                d="M239.99,373.475L246,379.485L252.01,373.475C253.376,372.109 253.376,369.891 252.01,368.525C250.644,367.159 248.427,367.159 247.061,368.525L246,369.586L244.939,368.525C243.573,367.159 241.356,367.159 239.99,368.525C238.624,369.891 238.624,372.109 239.99,373.475Z">
                                </path>
                            </g>
                        </svg>
                    </span>
                </div>
                <ReactTooltip 
                    id={"product-heart-button"}
                    place="top"
                    effect="solid"
                    type="light"
                    backgroundColor="#ededed"
                    borderColor="#ccc"
                    border={true}
                    getContent={[() => {
                        return "Become a Fan"
                    }]}
                    >
                </ReactTooltip>   
            </div>
            <CustomModal 
                isOpen={showModal}
                header={modalHeaderDisplay}
                closeModal={() => setShowModal(false)}
                modalBodyClassName={"align-center"}
            >
                {modalContentDisplay}
            </CustomModal>
        </div>
    )
}

export default ProductHeader;