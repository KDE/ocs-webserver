import React from 'react';
import { useState, useEffect, useContext } from 'react';
import { RenderUserProductsArray, GenerateFetchProductsRequest, GenerateProductArraysByCategory } from '../user-profile-helpers';
import RelationshipsModule from '../../common/relationships-module';
import UserProductActivityModule from './user-product-activity-module';
import PlingsModule from '../../common/plings';
import { Context } from '../context/context-provider';
import LoadingSpinner from '../../common/loading-spinners';

function UserProfileTabs(props){
    
    const { userProfileState, userProfileDispatch } = useContext(Context);

    const currentTab = userProfileState.tabsState.currentTab;
    const data = userProfileState.data;
    const statistics = data.stat;

    function onSetCurrentTab(label){
        userProfileDispatch({type:'SET_TAB',tab:label});
    }

    const tabsMenuItems = [
        { 
            label:'Products',
            count:statistics.cntProducts > 0 ? statistics.cntProducts : null
        },
        { 
            label:'Originals',
            count: statistics.cntOrinalProducts > 0 ? statistics.cntOrinalProducts : null
        },
        { 
            label:'Featured',
            display:data.userFeaturedProducts ? true : false,
            count: data.userFeaturedProducts ? data.userFeaturedProducts.length : 0
        },
        { 
            label:'Listing',
            display:data.userCollections ? true : false,
            count: data.userCollections ? data.userCollections.length : 0
        },
        { 
            label:'Comments',
            count: statistics.cntComments ? statistics.cntComments : 0
        },
        { 
            label:'Rated',
            display: statistics.cntRated ? true : false,
            count: statistics.cntRated ? statistics.cntRated : 0
        },
        { 
            label:'Plinged',
            display:data.plings.length > 0 ? true : false,
            count: statistics.cntPlingsHeGave ? statistics.cntPlingsHeGave : 0
        },
        { 
            label:'Fan of',
            display:data.likes ? true : false,
            count: statistics.cntLikesHeGave ? statistics.cntLikesHeGave : 0
        },
        { 
            label:'Plinged By',
            display:data.supportersplings.length > 0 ? true : false,
            count: data.supportersplings.length ? data.supportersplings.length : 0
        },
        { 
            label:'Affiliates',
            count:data.affiliates.length
        },
        { 
            label:'Duplicates',
            display: data.isAdmin === true && statistics.cntDuplicateSourceurl > 0 ? true : false,
            count: statistics.cntDuplicateSourceurl ? statistics.cntDuplicateSourceurl : 0,
            isAdmin:true
        },{
            label:'Unpublished (A:' + statistics.cntUnpublished + ')',
            display: data.isAdmin === true ? true : false,
            count:null,
            isAdmin:true
        },{
            label:'Deleted (A:' + statistics.cntDeleted + ')',
            display: data.isAdmin === true ? true : false,
            count:null,
            isAdmin:true
        }
    ];

    const tabsMenuDisplay = tabsMenuItems.map((mi,index) => {
        if (mi.display !== false){
            let titleDisplay
            if (mi.isAdmin === true){
                titleDisplay = (
                    <i>
                        {mi.label + " "} <span> {mi.count !== null ? mi.count : ""}</span>
                    </i>
                )
            } else {
                titleDisplay = (
                    <React.Fragment>
                        {mi.label + " "} <span> {mi.count !== null ? mi.count : ""}</span>
                    </React.Fragment>
                )
            }
            return (
                <li key={index}>
                    <a className={mi.label === currentTab ? "active" : ""} style={{cursor:"pointer"}} onClick={() => onSetCurrentTab(mi.label)} >
                        {titleDisplay}
                    </a>
                </li>
            )
        }
    })

    return (
        <React.Fragment>
        <div className="container-normal container-scroll mt6 mb0">
            <ul className="nav nav-pui-tabs link-primary-invert">{tabsMenuDisplay}</ul>
        </div>
        <div className="container-normal">
            <UserProfilePanelsContainer 
                onChangeUrl={props.onChangeUrl} 
            />
        </div>
        </React.Fragment>
    )
}

function UserProfilePanelsContainer(props){
    
    const { userProfileState } = useContext(Context);
    const data = userProfileState.data;

    const statistics = data.stat;

    let panelsDisplay;
    switch(userProfileState.tabsState.currentTab){
        case "Products":
            panelsDisplay = <ProductsPanel onChangeUrl={props.onChangeUrl} />
            break;
        case "Originals":
            panelsDisplay = <OriginalsPanel onChangeUrl={props.onChangeUrl} />
            break;
        case "Featured":
            panelsDisplay = <FeaturedProductsPanel onChangeUrl={props.onChangeUrl}/>
            break;
        case "Listing":
            panelsDisplay = <CollectionsPanel  onChangeUrl={props.onChangeUrl}/>
            break;
        case "Comments":
            panelsDisplay = <CommentsPanel onChangeUrl={props.onChangeUrl}/>
            break;
        case "Rated":
            panelsDisplay = <RatedPanel onChangeUrl={props.onChangeUrl}/>
            break;
        case "Plinged":
            panelsDisplay = <PlingedPanel onChangeUrl={props.onChangeUrl} />
            break;
        case "Fan of":
            panelsDisplay = <LikesPanel onChangeUrl={props.onChangeUrl}/>
            break;
        case "Plinged By":
            panelsDisplay = <PlingedByPanel onChangeUrl={props.onChangeUrl} supportersplings={data.supportersplings} pageLimit={20} />
            break;
        case "Affiliates":
            panelsDisplay = <AffiliatesPanel onChangeUrl={props.onChangeUrl} affiliates={data.affiliates} cntAffiliates={data.affiliates.length} />
            break;
        case "Duplicates":
            panelsDisplay = <DuplicatesPanel onChangeUrl={props.onChangeUrl} />
            break;
        case "Unpublished (A:" + statistics.cntUnpublished + ")":
            panelsDisplay = <UnpublishedPanel onChangeUrl={props.onChangeUrl} />
            break;
        case "Deleted (A:" + statistics.cntDeleted + ")":
            panelsDisplay = <DeletedPanel  onChangeUrl={props.onChangeUrl} />
            break;
        default:
            panelsDisplay = <ProductsPanel onChangeUrl={props.onChangeUrl} />
            break;
    }

    return (
        <React.Fragment>
            {panelsDisplay}
        </React.Fragment>
    )
}

function ProductsPanel(props){

    const { userProfileState, userProfileDispatch } = useContext(Context);

    function setProductsTab(products,pageNum){
        userProfileDispatch({type:'UPDATE_PRODUCTS_TAB',products:products,page:pageNum})
    }

    let currentPage = 1
    if (userProfileState.tabsState.productsTabPage) currentPage = userProfileState.tabsState.productsTabPage

    return (
        <div className="tab-panel-container" id="usermoreproducts-panel">
            <ProductsContainer 
                products={userProfileState.data.userProducts}
                pageNum={currentPage}
                member={userProfileState.data.member}
                type={"userMoreproducts"}
                loaded={true}
                onChangeUrl={props.onChangeUrl}
                cntProducts={userProfileState.data.stat.cntProducts ? userProfileState.data.stat.cntProducts : 0}
                onSetProducts={setProductsTab}
            />
        </div>
    )
}

function ProductsContainer(props){
    
    let initProducts = [];
    if (props.loaded === true && props.pageNum > 1 || props.loaded === false && props.pageNum > 0) initProducts = [ ...props.products ];
    else if (props.products) initProducts.push(props.products);
    
    // console.log('init - ' + props.type + ' products');
    // console.log(initProducts);

    const [ products, setProducts ] = useState(initProducts);
    const [ pageNum, setPageNum ] = useState(props.pageNum);
    const [ loading, setLoading ] = useState(false);

    const buttonId = props.type.toLowerCase()+'-panel-more-products-button';

    let initLoadProducts = true;
    if (props.cntProducts < 50 && props.loaded === true) initLoadProducts = false;
    const [ loadProducts , setLoadProducts ] = useState(initLoadProducts);

    let xhr;

    useEffect(() => {
        window.addEventListener('scroll', onWindowScroll, true);
        if (props.loaded === false) loadMoreProducts();
        return () => {
            setProducts([]);
            window.removeEventListener('scroll', onWindowScroll, true);
            if (xhr && xhr.abort) xhr.abort;
        }
    },[])

    function onWindowScroll(){
        const panelId = '#'+props.type.toLowerCase()+'-panel';
        const domElement = document.querySelector(panelId);        
        if (domElement){
            const domElementPositionTop = window.scrollY + domElement.getBoundingClientRect().top;
            const domElementHeight = domElement.getBoundingClientRect().height;
            const scollIsInPosition = (( window.scrollY + window.innerHeight ) > ( domElementHeight + domElementPositionTop ));
            if (scollIsInPosition === true) document.querySelector('#'+buttonId).click();
        }
    }

    function loadMoreProducts(){
        if (loading === false && loadProducts === true){
                var doc = document.documentElement;
                var top = (window.pageYOffset || doc.scrollTop)  - (doc.clientTop || 0);
                setLoading(true);
                setLoadProducts(false);
                window.removeEventListener('scroll', onWindowScroll, true);
                const newPageNum = pageNum + 1;
                const ajaxRequest = GenerateFetchProductsRequest(props.member.username,props.type,props.loaded,newPageNum);
                //console.log(ajaxRequest);
                xhr = $.ajax(ajaxRequest).done(function(res){
                    if (res && res.userProducts){
                        const newProducts = GenerateProductArraysByCategory(products,res.userProducts,props.groupResults);
                        //console.log(newProducts);
                        setPageNum(newPageNum)
                        setProducts(newProducts);
                        if (res.userProducts.length < 50) setLoadProducts(false);
                        else window.addEventListener('scroll', onWindowScroll, true);
                        props.onSetProducts(newProducts,newPageNum);                        
                    }
                    window.scrollTo({top:top - 50});
                    setLoading(false);
                    setTimeout(() => {
                        setLoadProducts(true);
                    }, 100);
                }).fail(function(err){
                    console.log(err.statusText)
                    setLoading(false);
                    setLoadProducts(false);
                });
        }
    }

    const relationShipsDisplay = products.map((pArray,index) => {
        const sortedArray = RenderUserProductsArray(pArray);
        if (sortedArray && sortedArray.length > 0){
            return (
                <RelationshipsModule 
                    key={index}
                    type={'user-profile'}
                    onChangeUrl={props.onChangeUrl} 
                    relationships={sortedArray}
                    showCatTitle={props.showCatTitle}
                    showUser={false}
                />
            )
        }
    })

    let loadingDisplay;
    if (loading === true) loadingDisplay = <LoadingSpinner type="ripples"/>


    return (
        <React.Fragment>
            {relationShipsDisplay}
            {loadingDisplay}
            <button style={{opacity:"0"}} id={buttonId} onClick={loadMoreProducts}>LOAD MORE PRODUCTS</button>
        </React.Fragment>    
    )
}

function OriginalsPanel(props){

    const { userProfileState, userProfileDispatch } = useContext(Context);

    function setProductsTab(products,pageNum){
        userProfileDispatch({type:'UPDATE_ORIGINALS_TAB',products:products,page:pageNum})
    }

    let currentPage = 0;
    if (userProfileState.tabsState.originalsTabPage) currentPage = userProfileState.tabsState.originalsTabPage;

    let products;
    if (userProfileState.data.userOriginalProducts) products = userProfileState.data.userOriginalProducts;

    return (
        <div className="tab-panel-container" id="usershoworiginal-panel">
            <ProductsContainer
                {...props}
                products={products}
                pageNum={currentPage}
                member={userProfileState.data.member}
                type={"userShoworiginal"}
                onChangeUrl={props.onChangeUrl}
                cntProducts={userProfileState.data.stat.cntOrinalProducts}
                onSetProducts={setProductsTab}
                loaded={false}
                showCatTitle={true}
                groupResults={true}
            />
        </div>
    )
}

function FeaturedProductsPanel(props){

    const { userProfileState, userProfileDispatch } = useContext(Context);

    function setProductsTab(products,pageNum){
        userProfileDispatch({type:'UPDATE_FEATURED_TAB',products:products,page:pageNum})
    }

    let currentPage = 1
    if (userProfileState.tabsState.featuredTabPage) currentPage = userProfileState.tabsState.featuredTabPage

    return (
        <div className="tab-panel-container" id="featured-panel">
            <ProductsContainer 
                type={"featured"}
                pageNum={currentPage}
                products={userProfileState.data.userFeaturedProducts}
                member={userProfileState.data.member}
                loaded={true}
                cntProducts={userProfileState.data.userFeaturedProducts ? userProfileState.data.userFeaturedProducts.length : 0}
                onChangeUrl={props.onChangeUrl}
                setProductsTab={setProductsTab}
            />
        </div>
    )
}

function CollectionsPanel(props){

    const { userProfileState, userProfileDispatch } = useContext(Context);

    function setProductsTab(products,pageNum){
        userProfileDispatch({type:'UPDATE_COLLECTIONS_TAB',products:products,page:pageNum})
    }

    let currentPage = 1
    if (userProfileState.tabsState.collectionsTabPage) currentPage = userProfileState.tabsState.collectionsTabPage;

    return (
        <div className="tab-panel-container" id="featured-panel">
            <ProductsContainer 
                type={"collections"}
                pageNum={currentPage}
                products={userProfileState.data.userCollections}
                member={userProfileState.data.member}
                loaded={true}
                cntProducts={userProfileState.data.userCollections ? userProfileState.data.userCollections.length : 0}
                onChangeUrl={props.onChangeUrl}
                setProductsTab={setProductsTab}
            />
        </div>
    )
}

function CommentsPanel(props){

    const { userProfileState, userProfileDispatch } = useContext(Context);

    function onPageChange(items,pageNum){
        userProfileDispatch({type:'UPDATE_COMMENTS_TAB',items:items,page:pageNum})
    }

    let currentPage = 1;
    if (userProfileState.tabsState.commentsTabPage) currentPage = userProfileState.tabsState.commentsTabPage;

    return (
        <div className="tab-panel-container" id="usermorecomments-panel">
            <UserProductActivityModule 
                type={"userMorecomments"}
                items={userProfileState.data.comments}
                totalItems={userProfileState.data.stat.cntComments}
                currentPage={currentPage}
                pageLimit={userProfileState.data.pageLimit}
                onPageChange={onPageChange}
                onChangeUrl={props.onChangeUrl}
                member={userProfileState.data.member}
            />
        </div>
    )
}

function RatedPanel(props){

    const { userProfileState, userProfileDispatch } = useContext(Context);
    function onPageChange(items,pageNum){
        userProfileDispatch({type:'UPDATE_RATED_TAB',items:items,page:pageNum})
    }
    let currentPage = 1;
    if (userProfileState.tabsState.ratedTabPage) currentPage = userProfileState.tabsState.ratedTabPage;
    return (
        <div className="tab-panel-container" id="usermorerates-panel">
            <div className="my-fav-list">
            <UserProductActivityModule 
                type={"userMorerates"}
                items={userProfileState.data.rated}
                totalItems={userProfileState.data.stat.cntRated}
                currentPage={currentPage}
                pageLimit={20}
                onPageChange={onPageChange}
                onChangeUrl={props.onChangeUrl}
                member={userProfileState.data.member}
            />
            </div>
        </div>
    )

}

function PlingedPanel(props){
    const { userProfileState, userProfileDispatch } = useContext(Context);
    function onPageChange(items,pageNum){
        userProfileDispatch({type:'UPDATE_PLINGED_TAB',items:items,page:pageNum})
    }
    let currentPage = 1;
    if (userProfileState.tabsState.plingsTabPage) currentPage = userProfileState.tabsState.plingsTabPage;
    return (
        <div className="tab-panel-container" id="usermoreplinged-panel">
            <div className="my-fav-list">
            <UserProductActivityModule 
                type={"userMoreplings"}
                items={userProfileState.data.plings}
                totalItems={userProfileState.data.stat.cntPlingsHeGave}
                currentPage={currentPage}
                pageLimit={20}
                onPageChange={onPageChange}
                onChangeUrl={props.onChangeUrl}
                member={userProfileState.data.member}
            />
            </div>
        </div>
    )

}

function LikesPanel(props){
    const { userProfileState, userProfileDispatch } = useContext(Context);
    function onPageChange(items,pageNum){
        userProfileDispatch({type:'UPDATE_LIKES_TAB',items:items,page:pageNum})
    }
    let currentPage = 1;
    if (userProfileState.tabsState.likesTabPage) currentPage = userProfileState.tabsState.likesTabPage;
    return (
        <div className="tab-panel-container" id="user-rated">
            <div className="my-fav-list">
            <UserProductActivityModule 
                type="userMorelikes"
                items={userProfileState.data.likes}
                totalItems={userProfileState.data.stat.cntLikesHeGave}
                currentPage={currentPage}
                pageLimit={20}
                onPageChange={onPageChange}
                onChangeUrl={props.onChangeUrl}
                member={userProfileState.data.member}
            />
            </div>
        </div>
    )
}

function PlingedByPanel(props){
    
    function onPlingItemClick(url,title){
        props.onChangeUrl(url,title)
    }
    
    return (
        <div className="tab-panel-container" id="supporters-plings">
            <div className="my-fav-list">
                <PlingsModule onPlingItemClick={onPlingItemClick} items={props.supportersplings} type="plinged-by"/>
            </div>
        </div>
    )
}

function AffiliatesPanel(props){
    
    function onPlingItemClick(url,title){
        props.onChangeUrl(url,title)
    }

    let affiliatesModuleDisplay;
    if (props.affiliates){
        <PlingsModule type={'affiliates'} items={props.affiliates}  onPlingItemClick={onPlingItemClick}/>
    }
    
    return (
        <div id="product-fans" className="product-tabs-panel product-fans-panel">
            {affiliatesModuleDisplay}
        </div>
    )
}

function DuplicatesPanel(props){
    const { userProfileState, userProfileDispatch } = useContext(Context);
    function setProductsTab(products,pageNum){
        userProfileDispatch({type:'UPDATE_DUPLICATES_TAB',products:products,page:pageNum})
    }
    let currentPage = 0;
    if (userProfileState.tabsState.duplicatesTabPage) currentPage = userProfileState.tabsState.duplicatesTabPage;
    let products;
    if (userProfileState.data.userDuplicatesProducts) products = userProfileState.data.userDuplicatesProducts;
    return (
        <div className="tab-panel-container" id="userduplicates-panel">
            <ProductsContainer 
                products={products}
                pageNum={currentPage}
                member={userProfileState.data.member}
                cntProducts={userProfileState.data.stat.cntDuplicateSourceurl}
                type={"userDuplicates"}
                loaded={false}
                onChangeUrl={props.onChangeUrl}
                onSetProducts={setProductsTab}
            />
        </div>
    )

}

function UnpublishedPanel(props){
    const { userProfileState, userProfileDispatch } = useContext(Context);
    function setProductsTab(products,pageNum){
        userProfileDispatch({type:'UPDATE_UNPUBLISHED_TAB',products:products,page:pageNum})
    }
    let currentPage = 0;
    if (userProfileState.tabsState.unpublishedTabPage) currentPage = userProfileState.tabsState.unpublishedTabPage;
    let products;
    if (userProfileState.data.userUnpublishedProducts) products = userProfileState.data.userUnpublishedProducts;
    return (
        <div className="tab-panel-container" id="userunpublished-panel">
            <ProductsContainer 
                products={products}
                pageNum={currentPage}
                member={userProfileState.data.member}
                type={"userUnpublished"}
                loaded={false}
                onChangeUrl={props.onChangeUrl}
                onSetProducts={setProductsTab}
                cntProducts={userProfileState.data.stat.cntUnpublished}
            />
        </div>
    )
}

function DeletedPanel(props){
    const { userProfileState, userProfileDispatch } = useContext(Context);
    function setProductsTab(products,pageNum){
        userProfileDispatch({type:'UPDATE_DELETED_TAB',products:products,page:pageNum})
    }
    let currentPage = 0;
    if (userProfileState.tabsState.deletedTabPage) currentPage = userProfileState.tabsState.deletedTabPage;
    let products;
    if (userProfileState.data.userDeletedProducts) products = userProfileState.data.userDeletedProducts;
    return (
        <div className="tab-panel-container" id="userdeleted-panel">
            <ProductsContainer 
                products={products}
                pageNum={currentPage}
                member={userProfileState.data.member}
                type={"userDeleted"}
                loaded={false}
                onChangeUrl={props.onChangeUrl}
                onSetProducts={setProductsTab}
                cntProducts={userProfileState.data.stat.cntDeleted}
            />
        </div>
    )
}

export default UserProfileTabs;