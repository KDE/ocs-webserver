import React, { useContext, useState, useEffect, Suspense, lazy } from 'react';
import { Context } from './context/context-provider';
import TimeAgo from 'react-timeago';
import { ConvertObjectToArray } from './../../common/common-helpers';

import CommentsModule from '../../common/comments';
import Changelog from './partials/changelog';

import LoadingSpinner from '../../common/loading-spinners';
import CollectionProductsList from './partials/collection-products-list';

import { Base64 } from 'js-base64';

import './style/style-product-tabs.css';

const FilesTable = lazy(() => import('./partials/files-table'));
const RatingsReviewsModule = lazy(() => import( '../../common/ratings-reviews'));
const PlingsModule = lazy(() => import('../../common/plings'));
const RelationshipsModule = lazy(() => import('../../common/relationships-module'));

function ProductTabs(props){

    const { productViewState } = useContext(Context);

    const firstTabLabel = productViewState.isCollectionView === true ? "Products" : "Product";
    const [ currentTab, setCurrentTab ] = useState(firstTabLabel);

    const tabsMenuItems = [
        {label:firstTabLabel},
        {label:'Files'},
        {label:'Changelogs'},
        {label:'Ratings & Reviews'},
        {label:'Plings'},
        {label:'Affiliates'},
        {label:'Fans'},
        {
            label:'Relationship',
            isAdmin:true
        },
        {
            label:'Licensing'
        },
        {
            label:'Moderation',
            isAdmin:true
        }   
    ];

    const tabCount = productViewState.tabCnt;
    const tabsMenuDisplay = tabsMenuItems.map((mi,index) => {
        
        let displayTabMenuItem = true;

        if (mi.isAdmin){
            displayTabMenuItem = false;
            if (productViewState.authMember && productViewState.authMember.isAdmin === true) displayTabMenuItem = true;
        } else if (mi.label === "Plings"){
            displayTabMenuItem = false;
            if (tabCount.cntPlings > 0) displayTabMenuItem = true;
        } else if (mi.label === "Fans"){
            displayTabMenuItem = false;
            if (tabCount.cntLikes > 0) displayTabMenuItem = true;
        } else if (mi.label === "Changelogs"){
            displayTabMenuItem = false;
            if (tabCount.cntUpdates > 0) displayTabMenuItem = true;
        }

        if (productViewState.isCollectionView === true){
            if ( mi.label === "Files" || mi.label === "Affiliates" ||  mi.label === "Licensing" || mi.isAdmin === true ) displayTabMenuItem = false;
        }

        if (displayTabMenuItem === true){
            let titleDisplay = mi.label
            if (mi.label === "Files") titleDisplay = mi.label + " (" + tabCount.cntFiles + ")";
            else if (mi.label === "Products") titleDisplay = mi.label + " (" + productViewState.listing_projects.length + ")";
            else if (mi.label === "Changelogs") titleDisplay = mi.label + " (" + tabCount.cntUpdates + ")";
            else if (mi.label === "Ratings & Reviews") titleDisplay = mi.label + " (" + tabCount.cntRatings + ")";
            else if (mi.label === "Plings") titleDisplay = mi.label + " (" + tabCount.cntPlings + ")";
            else if (mi.label === "Affiliates") titleDisplay = mi.label + " (" + tabCount.cntAffiliates + ")";
            else if (mi.label === "Fans") titleDisplay = mi.label + " (" + tabCount.cntLikes + ")";
            else if (mi.label === "Relationship") titleDisplay = <i>{mi.label + " (" + ( productViewState.relationshipTab ? productViewState.relationshipTab.cntRelatedProducts : "0" ) + ")"}</i>;
            else if (mi.label === "Licensing") titleDisplay = <i>{mi.label + " (" + tabCount.cntCommentsLic + ")"}</i>;
            else if (mi.label === "Moderation") titleDisplay = <i>{mi.label + " (" + tabCount.cntCommentsMod + ")"}</i>;
            return (
                <li key={index}>
                    <a className={(mi.label === currentTab ? "active" : "")} style={{cursor:"pointer"}} onClick={() => setCurrentTab(mi.label)} >
                        {titleDisplay}
                    </a>
                </li>
            )
        }
    })

    return (
        <React.Fragment>
            <div id="product-tabs" className="container-wide p0 m0 container-scroll">
                <div className="container-wide pt0 pb0 container-indent">
                    <ul className="nav nav-pui-tabs link-primary-invert">{tabsMenuDisplay}</ul>
                </div>
            </div>
            <PanelsContainer onChangeUrl={props.onChangeUrl} currentTab={currentTab} />
        </React.Fragment>
    )
}

function PanelsContainer(props){
    
    const { productViewState } = useContext(Context);
    
    let isAdmin = false;
    if (productViewState.authMember !== null && productViewState.authMember.isAdmin === true){
        isAdmin = productViewState.authMember.isAdmin;
    }

    let panelDisplay;
    switch(props.currentTab) {
        case 'Products':
            panelDisplay = <ProductsPanel onChangeUrl={props.onChangeUrl} />
            break;
        case 'Files':
            panelDisplay = (
                <FilesPanel 
                    files={productViewState.filesTab}
                    countFiles={productViewState.tabCnt.cntFiles}
                    isAdmin={isAdmin}
                    product={productViewState.product}
                />
            )
            break;
        case 'Changelogs':
            panelDisplay = <ChangelogPanel />
            break;
        case 'Ratings & Reviews':
            panelDisplay = <RatingsReviewsPanel onChangeUrl={props.onChangeUrl} ratings={productViewState.ratings} />
            break;
        case 'Plings':
            panelDisplay = <PlingsPanel  onChangeUrl={props.onChangeUrl} />
            break;
        case 'Fans':
            panelDisplay = <FansPanel  onChangeUrl={props.onChangeUrl} />
            break;
        case 'Affiliates':
            panelDisplay = <AffiliatesPanel  onChangeUrl={props.onChangeUrl} />
            break;
        case 'Relationship':
            panelDisplay = <RelationshipPanel  onChangeUrl={props.onChangeUrl} />
            break;
        case 'Licensing':
            panelDisplay = <LicensingPanel  onChangeUrl={props.onChangeUrl} />
            break;
        case 'Moderation':
            panelDisplay = <ModerationPanel  onChangeUrl={props.onChangeUrl} />
            break;
        default:
            panelDisplay = <ProductPanel  onChangeUrl={props.onChangeUrl} />
            
    }
    
    return (
        <React.Fragment>
            {panelDisplay}
        </React.Fragment>
    )
}

function ProductsPanel(props){
    return (
        <React.Fragment>
            <div className="container-wide container-indent">
                <article>
                    <CollectionProductsList 
                        onChangeUrl={props.onChangeUrl}
                    />
                </article>
            </div>
            <ProductDiscussion
                onChangeUrl={props.onChangeUrl}
            />
        </React.Fragment>
    )
}

function ProductPanel(props){
    
    const { productViewState } = useContext(Context);

    let productDescriptionDisplay;

    const pvd = JSON.parse(Base64.decode(productViewDataEncoded));
    if (pvd.product.is_gitlab_project === "1" && pvd.readme){
        productDescriptionDisplay = (
            <React.Fragment>
                <b>Description:</b>
                <br/>
                <article dangerouslySetInnerHTML={{__html: pvd.readme}}></article>
            </React.Fragment>
        )
    } else if (productViewState.product.description ){
        productDescriptionDisplay = (
            <React.Fragment>
                <b>Description:</b>
                <br/>
                <article dangerouslySetInnerHTML={{__html: productViewState.product.description}}></article>
            </React.Fragment>
        )
    }

    let lastChangelogDisplay;
    if (productViewState.updatesLast){

        let updateTextDisplay = productViewState.updatesLast.text;
        if (productViewState.updatesLast.text.indexOf('<br>') > -1){
            const updateTextArray = productViewState.updatesLast.text.split('<br>');
            updateTextDisplay = updateTextArray.map((ut,index) => (
                <React.Fragment>
                    <span key={index} dangerouslySetInnerHTML={{__html:ut}}></span>
                    <br/>
                </React.Fragment>
            ))
        }
        
        lastChangelogDisplay = (
            <article>
                <b>Last changelog:</b>
                <br/>
                <h3 className="product-heading product-update-heading">
                    {productViewState.updatesLast.title} 
                    <span className="small light lightgrey product-update-date">
                        <TimeAgo date={productViewState.updatesLast.created_at}></TimeAgo>
                    </span>
                </h3>
                <p>{updateTextDisplay}</p>
            </article>
        ) 
    }
    let availableforDisplay;
    if (productViewState.tagsCategoryTagGroup){
        const availableForTags = productViewState.tagsCategoryTagGroup.map((tc, index) => (
            <span className="tag-availablefor">{tc.tag_name}</span>
        ))
        availableforDisplay = (
            <div className="availablefor">
                <span className="tag-availablefor-label">Available as/for:</span>
                {availableForTags}
            </div>
        )
    }

    return (
        <React.Fragment>
            <div className="container-wide container-indent">
                <div className="product-main-description">
                    {availableforDisplay}
                    {productDescriptionDisplay}
                    {lastChangelogDisplay}
                </div>
            </div>
            <hr className="m0"/>
            <ProductDiscussion
                onChangeUrl={props.onChangeUrl}
            />
        </React.Fragment>
    )
}

function ProductDiscussion(props){

    const { productViewState, productViewDispatch } = useContext(Context);
    const [ commentsLoading, setCommentsLoading ] = useState(false);

    let charToSplit =  window.location.pathname.indexOf('/p/') > -1 ? '/p/' : '/c/';
    let featchCommentsUrl = window.location.pathname.split(charToSplit)[1].split('/')[0];
    featchCommentsUrl = "/p/" + featchCommentsUrl + "/load-comments";

    function onCommentsPageChange(page){
        getComments(page);
    }

    function getProductComments(page){
        productViewDispatch({type:'WATCH_COMMENTS'});
        getComments(page,true);
    }

    function getComments(page,addComment){
        if (!commentsLoading){
            setCommentsLoading(true);
            let url = featchCommentsUrl;
            if (page) url += "?page=" + page;
            $.ajax({url:url}).done(function(res){
                productViewDispatch({type:'SET_COMMENTS',comments:res,page:page});
                if (addComment === true) productViewDispatch({type:'INCREMENT_COMMENT_COUNT'})
                setCommentsLoading(false);
            });
        }        
    }

    let commentsDisplay;
    if (productViewState.commentsTab) commentsDisplay = ConvertObjectToArray(productViewState.commentsTab);

    return (
        <div className="container-wide container-indent">
            <CommentsModule 
                type={"comments"}
                isAdmin={productViewState.authMember  && productViewState.authMember.isAdmin ? productViewState.authMember.isAdmin : false}
                product={productViewState.product}
                commentCount={productViewState.commentsTabCnt}
                comments={commentsDisplay}
                user={productViewState.authMember}
                userRatings={productViewState.ratingOfUser}
                featchCommentsUrl={featchCommentsUrl}
                onCommentsPageChange={onCommentsPageChange}
                onPostComment={getProductComments}
                currentPage={(productViewState.commentsTabPage ? productViewState.commentsTabPage : 1)}
                loading={commentsLoading}
                onChangeUrl={props.onChangeUrl}
            />
        </div>
    )
}

function FilesPanel(props){

    let filesTableDisplay;
    if (props.files.length > 0){
        filesTableDisplay = (
            <Suspense fallback={''}>
                <FilesTable {...props} />
            </Suspense>
        )
    }

    return (
        <div className="container-wide container-indent">
            <div id="product-files" className="col-lg-12 product-tabs-panel">
                <div className="product-files-table-container row">
                    {filesTableDisplay}
                </div>
            </div>
        </div>
    )
}

function ChangelogPanel(props){

    const { productViewState, productViewDispatch } = useContext(Context);
    const [ loading, setLoading ] = useState(false);

    /*useEffect(() => {
        initChangelogPanel();
    },[])

    function initChangelogPanel(){
        if (!productViewState.changelogsTab){
            let productUrl = window.location.pathname;
            productUrl = "/p/" + productUrl.split('/')[2] + "/";
            $.ajax({url:productUrl + "load-changelogs"}).done(function(cl){
                productViewDispatch({type:'SET_CHANGELOGS',changelogs:cl})
            });
        } else setLoading(false);
    }

    useEffect(() => {
        if (productViewState.changelogsTab !== null) setLoading(false)
    },[productViewState.changelogsTab])*/

    let updatesDisplay =  <LoadingSpinner msg="Loading Changelogs" type="ripples"/>
    if (loading === false){
        updatesDisplay = productViewState.changelogsTab.map((update,index) => (
            <Changelog update={update} key={index} />
        ))
    }

    return (
        <div className="container-wide container-indent">
            <div id="product-changelog" className="product-tabs-panel product-updates-panel">
                <div className="col-lg-9 col-md-9 col-sm-9 col-xs-9">
                    <article>
                        {updatesDisplay}
                    </article>
                </div>
            </div>
        </div>
    )
}

function RatingsReviewsPanel(props){

    const { productViewState } = useContext(Context);

    function onRatingsItemClick(url,title){
        props.onChangeUrl(url,title)    
    }

    return (
        <div className="container-wide container-indent">
            <div id="product-ratings" className="product-tabs-panel product-ratings-panel">
                <Suspense fallback={''}>
                    <RatingsReviewsModule onRatingsItemClick={onRatingsItemClick} ratings={productViewState.ratingsTab}/>
                </Suspense>
            </div>
        </div>
    )
}

function PlingsPanel(props){

    const { productViewState } = useContext(Context);

    function onPlingItemClick(url,title){
        props.onChangeUrl(url,title)
    }

    return (
        <div className="container-wide container-indent">
            <div id="product-plings" className="product-tabs-panel product-plings-panel">
                <Suspense fallback={''}>
                    <PlingsModule type="plings" items={productViewState.plingsTab} onPlingItemClick={onPlingItemClick}/>
                </Suspense>
            </div>
        </div>
    )
}

function AffiliatesPanel(props){

    const { productViewState } = useContext(Context);
    
    function onPlingItemClick(url,title){
        props.onChangeUrl(url,title)
    }

    let affiliatesModuleDisplay;
    if (productViewState.affiliatesTab !== null && productViewState.affiliatesTab && productViewState.affiliatesTab.length > 0){
        affiliatesModuleDisplay = (
            <Suspense fallback={''}>
                <PlingsModule type={'affiliates'} items={productViewState.affiliatesTab}  onPlingItemClick={onPlingItemClick}/>
            </Suspense>
        )
    }

    return (
        <div className="container-wide container-indent">
            <div id="product-fans" className="product-tabs-panel product-fans-panel">
                {affiliatesModuleDisplay}
            </div>
        </div>
    )
}

function FansPanel(props){

    const { productViewState } = useContext(Context);

    function onPlingItemClick(url,title){
        props.onChangeUrl(url,title)
    }

    let likesModulesDisplay;
    if (productViewState.fansTab !== null && productViewState.fansTab && productViewState.fansTab.length > 0){
        likesModulesDisplay = (
            <Suspense fallback={''}>
                <PlingsModule type={"likes"} items={productViewState.fansTab} onPlingItemClick={onPlingItemClick}/>
            </Suspense>
        )
    }

    return (
        <div className="container-wide container-indent">
            <div id="product-fans" className="product-tabs-panel product-fans-panel">
                {likesModulesDisplay}
            </div>
        </div>
    )
}

function RelationshipPanel(props){
    const { productViewState } = useContext(Context);
    return (
        <div className="container-wide container-indent">
            <div id="product-relationships" className="product-tabs-panel product-relationship-panel">
                <Suspense fallback={''}>
                    <RelationshipsModule type={'product'} onChangeUrl={props.onChangeUrl} relationships={productViewState.relationshipTab}/>
                </Suspense>
            </div>
        </div>
    )
}

function LicensingPanel(props){

    const { productViewState } = useContext(Context);

    let featchCommentsUrl = window.location.pathname;
    featchCommentsUrl = "/p/" + featchCommentsUrl.split('/')[2] + "/load-licensing";
    let commentsCount = 0
    if (productViewState.commentsLicTab) commentsCount = productViewState.commentsLicTab.length;

    return (

        <div id="product-licensing" className="product-tabs-panel product-licensing-panel">
                <CommentsModule 
                    type={"licensing"}
                    title={"Comments"}
                    isAdmin={productViewState.isAdmin}
                    product={productViewState.product}
                    comments={productViewState.commentsLicTab}
                    commentCount={commentsCount}
                    user={productViewState.authMember}
                    featchCommentsUrl={featchCommentsUrl}
                    onChangeUrl={props.onChangeUrl}
                />
        </div>    

            
    )
}

function ModerationPanel(props){

    const { productViewState,productViewDispatch } = useContext(Context);

    function onUpdateProductIsDeprecated(val){
        productViewDispatch({type:'SET_PRODUCT_IS_DEPRECATED',value:val})
    }

    let featchCommentsUrl = window.location.pathname;
    featchCommentsUrl = "/p/" + featchCommentsUrl.split('/')[2] + "/load-moderation";

    let commentsCount = 0
    if (productViewState.commentsModTab) commentsCount = productViewState.commentsModTab.length;

    return (
        <div className="container-wide container-indent">
        <div id="product-moderation" className="product-tabs-panel product-moderation-panel">
            <CommentsModule 
                type={"moderation"}
                title={"Comments"}
                isAdmin={productViewState.isAdmin}
                product={productViewState.product}
                comments={productViewState.commentsModTab}
                commentCount={commentsCount}
                user={productViewState.authMember}
                featchCommentsUrl={featchCommentsUrl}
                isDeprecated={productViewState.isProductDeprecatedModerator}
                onUpdateProductIsDeprecated={onUpdateProductIsDeprecated}
                onChangeUrl={props.onChangeUrl}
            />
        </div>
        </div>        
    )
}

export default ProductTabs;