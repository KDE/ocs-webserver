import React, { useEffect, useState, useRef, useContext } from 'react';
import { Context } from './context/context-provider';
import { isMobile } from 'react-device-detect';
import { ProductBrowseItem } from './product-browse-item';
import { getNumberOfItemsPerRow, getImageHeight, chunkArray, getItemWidth, ConvertObjectToArray } from './product-browse-helpers';
import './../style/product-browse.css';
import { usePrevious } from '../../../layout/app-helpers';

function ProductBrowse(props){

    const { productBrowseState, productBrowseDispatch } = useContext(Context);

    /*function onPageChange(page){
        if (!productBrowseState.productsLoading && page !== productBrowseState.page){
            productBrowseDispatch({type:'SET_LOADED',value:false,loading:true})
            const nextPageUrl = "/browse/cat/"+productBrowseState.cat_id+"/page/"+page+"/order/"+productBrowseState.filters.order+"/";
            props.onPageChange(nextPageUrl,page);
        }
    }*/

    let productBrowseItemsDisplay;
    if (productBrowseState.productsLoading === false && productBrowseState.loading === false){
        if (productBrowseState.browseListType === "music" || productBrowseState.browseListType === "books"  ){
            productBrowseItemsDisplay = (
                <div className="container-wide">
                    <div className={"pling-cards-grid pui-cards-grid pling-music-grid " +( productBrowseState.browseListType === "books" ? "pling-books-grid" : "")}>
                        <ProductBrowseItemList
                            {...props}
                        />
                    </div>
                </div>
            )            
        } else if (productBrowseState.browseListType === "music"){

        } else {
            productBrowseItemsDisplay = (
                <ProductBrowseItemList
                    authMember={productBrowseState.authMember}
                    {...props}
                />
            )
        }
    }

    let productBrowseFilterDisplay;
    if (productBrowseState.tagBrowseFilter){
        productBrowseFilterDisplay = <ProductTagGroupFilterContainer/>
    }

    return (
        <div id="product-browse" className="container-wide p0">
            {productBrowseFilterDisplay}
            <ProductBrowseFilterContainer 
                onOrderChange={props.onOrderChange} 
            />
            {productBrowseItemsDisplay}
            <ProductBrowsePagination {...props} />
        </div>
    )
}

function ProductBrowseFilterContainer(props){

    const { productBrowseState, productBrowseDispatch } = useContext(Context);

    const initTabs = productBrowseState.tabs;
    const [ tabs, setTabs ] = useState(initTabs);

    const initUser = productBrowseState.authMember;
    const [ user, setUser ] = useState(initUser);

    const initOrder = productBrowseState.filters ? productBrowseState.filters.order : null;
    const [ order, setOrder ] = useState(initOrder);
    
    const previousCategoryfiltersOrder = usePrevious(order)
    const previousTabs = usePrevious(productBrowseState.tabs)

    useEffect(() => {
        if (productBrowseState.tabs && productBrowseState.tabs !== previousTabs){
            setTabs(productBrowseState.tabs);
            setUser(productBrowseState.authMember);
        }
    },[productBrowseState.tabs])

    useEffect(() => {
        if (productBrowseState.filters !== null && productBrowseState.filters.order !== previousCategoryfiltersOrder){
            setOrder(productBrowseState.filters.order);
        }
    },[productBrowseState.filters])
    
    function onFilterClick(e,pbt){
        e.preventDefault();
        if (!productBrowseState.productsLoading){
            setOrder(pbt.order);
            productBrowseDispatch({type:'SET_LOADED',value:false,loading:true})
            const url = pbt.url.indexOf('order=') > -1 ? pbt.url.split('order=')[0] + 'order=' + pbt.url.split('order=')[1] : pbt.url;
            props.onOrderChange(url,pbt.order);                
        }
    }

    let displayTabs;
    if (tabs) displayTabs = tabs;
    else {
        displayTabs = [
            {order: "latest", label: "Latest"},
            {order: "rating", label: "Rating"},
            {order: "plinged", label: "Plinged"},
            {order: "top", label: "Score_old"},
            {order: "test", label: "Score_test"},
        ]
    }

    let productBrowseFilters;
    if (productBrowseState.isBrowseFavorite) {


        const heartStyle = {
            position: "relative",
            left: 0,
            bottom: "4px",
            marginRight: "7px"
        }

        productBrowseFilters = (
            <li>
                <a className="active">
                <span style={heartStyle} className={"pui-heart active"}>
                    <svg svg="" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 16" fill-rule="evenodd" clip-rule="evenodd" stroke-linejoin="round" stroke-miterlimit="2">
                        <g transform="matrix(1,0,0,1,-238,-365.493)">
                            <path 
                            d="M239.99,373.475L246,379.485L252.01,373.475C253.376,372.109 253.376,369.891 252.01,368.525C250.644,367.159 248.427,367.159 247.061,368.525L246,369.586L244.939,368.525C243.573,367.159 241.356,367.159 239.99,368.525C238.624,369.891 238.624,372.109 239.99,373.475Z">
                            </path>
                        </g>
                    </svg>
                </span>
                My favorites</a>
            </li>
        )
    }
    else {
        productBrowseFilters = displayTabs.map((pbt,index) => {
            let showTab = true;
            if (pbt.label === "Score_old" || pbt.label === "Score_test"){
                showTab = false;
                if (user && user.isAdmin == true) showTab = true;
            }
            if (showTab === true){
                return (                
                    <li key={index}>
                        <a className={pbt.order === order ? "active" : ""} href={pbt.url}>{pbt.label}</a>
                    </li>
                )
            }
        })
    }

    return (
        <div id="product-browse-top-menu">
            <div id="product-tabs" className="container-wide p0 m0 container-scroll">
                <div className="container-wide pt0 pb0">
                    <ul className="nav nav-pui-tabs link-primary-invert" id="sort">{productBrowseFilters}</ul>
                </div>
            </div>
        </div>
    )
}

function ProductTagGroupFilterContainer(){
 
    const { productBrowseState } = useContext(Context)
    const [ tagGroups, setTagGroups ] = useState([]);
    const [ tagGroupIds, setTagGroupIds ] = useState([]);
    const [ selectedTags, setSelectedTags ] = useState([]);

    React.useState(() => {
        renderTagGroups();
    },[])

    function renderTagGroups(){
        const tagBrowseFilter = productBrowseState.tagBrowseFilter;
        tagBrowseFilter.forEach((tg,index) => {
            
            const newTagGroupIds = tagGroupIds;
            newTagGroupIds.push(tg.tagGroup); 
            setTagGroupIds(newTagGroupIds);

            if (tg.select){
                const newSelectedTags = selectedTags;
                newSelectedTags.push(tg.select);
                setSelectedTags(newSelectedTags);
            }

            const newArray = ConvertObjectToArray(tg.values);
            let newTagGroupsArray = tagGroups;
            newTagGroupsArray.push(newArray);
            setTagGroups(newTagGroupsArray);

        })
        /*for ( var i in tagBrowseFilter){
            console.log(i);
            console.log(tagBrowseFilter[i]);
            const newTagGroupIds = tagGroupIds;
            newTagGroupIds.push(i); 
            setTagGroupIds(newTagGroupIds);
            const tagGroup = tagBrowseFilter[i];
            for (var ii in tagGroup){
                console.log(ii);
                if (ii === i.select){
                    const newSelectedTags = selectedTags;
                    newSelectedTags.push(tagGroup[ii]);
                    setSelectedTags(newSelectedTags);
                } else {
                    console.log(newArray);
                    let newTagGroupsArray = tagGroups;
                    newTagGroupsArray.push(newArray);
                    setTagGroups(newTagGroupsArray);
                }
            }
        }*/
    }

    let tagGroupsDropdownDisplay;
    if (tagGroups.length > 0){
        tagGroupsDropdownDisplay = tagGroups.map((tg,index) => (
            <TagGroupDropDownMenu 
                tagGroup={tg}
                tagGroupId={tagGroupIds[index]}
                selectedTag={selectedTags[index]}
                type={index === 0 ? 'Packagetype' : 'Architecture'}
            />
        ));
    }

    return (
        <div id="product-tag-filter-container">
            {tagGroupsDropdownDisplay}
        </div>
    )
}

function TagGroupDropDownMenu(props){
    function onSelectTag(e){
        const serverUrl = json_serverUrl.split('://')[1];
        const ajaxUrl = "https://"+ serverUrl + "/explore/savetaggroupfilter?group_id="+props.tagGroupId+"&tag_id="+e.target.value;
        $.ajax({url: ajaxUrl}).done(function(res) { 
            var url  = window.location.href;
            window.location.href = url;
        });
    }

    const tagsDisplay = props.tagGroup.map((tag,index) => (
        <option key={index} selected={tag.id === props.selectedTag} value={tag.id}>{tag.tag}</option>
    ));

    return (
        <div className="product-tag-group-dropdown">
            <label>{props.type}</label>
            <select onChange={e => onSelectTag(e)}>
                <option value=""></option>
                {tagsDisplay}
            </select>
        </div>
    )
}

function ProductBrowseItemList(props){

    const { productBrowseState } = useContext(Context);
    let browseListType = "standard";
    if (productBrowseState.browseListType) browseListType = productBrowseState.browseListType;
    if (window.location.search === "?index=7") browseListType = "favorites";

    let products = window.products ? window.products : [];
    if (productBrowseState.products) products = productBrowseState.products;

    const windowWidth = window.innerWidth;
    let initContainerWidth;
    if (isMobile === true){
        initContainerWidth = windowWidth + 14;
    } else {
        if (windowWidth < 768 ) initContainerWidth = windowWidth;
        else initContainerWidth = (( windowWidth / 100) * 67) - 13;
    }
    // console.log(initContainerWidth);
    const [ containerWidth, setContainerWidth ] = useState(initContainerWidth);
    // console.log(browseListType);
    const [ itemsInRow, setItemsInRow ] = useState(1);
    // console.log(itemsInRow);
    const [ itemWidth, setItemWidth ] = useState(getItemWidth(browseListType,containerWidth,itemsInRow));
    // console.log(itemWidth);
    const [ imgHeight, setImgHeight ] = useState(getImageHeight(browseListType,itemWidth));
    // console.log(imgHeight);
    
    React.useEffect(() => {
        window.addEventListener("resize", updateDimensions, true);
        window.addEventListener("orientationchange",updateDimensions,true);
        return () => {
            window.removeEventListener("resize", updateDimensions, true);
            window.removeEventListener("orientationchange",updateDimensions,true);
        }   
    },[])

    function updateDimensions(){
        const windowWidth = window.innerWidth;
        let newContainerWidth;
        if (windowWidth < 768 ) newContainerWidth = windowWidth;
        else newContainerWidth = (( windowWidth / 100) * 67) - 75;
        setContainerWidth(newContainerWidth);
        const newItemsInRow = getNumberOfItemsPerRow(browseListType,isMobile,newContainerWidth);
        setItemsInRow(newItemsInRow);
        const newItemWidth = getItemWidth(browseListType,newContainerWidth,newItemsInRow)
        setItemWidth(newItemWidth);
        const newImgHeight = getImageHeight(browseListType,newItemWidth);
        setImgHeight(newImgHeight);
    }

    let productsRowsDisplay;
    if (itemsInRow){
            productsRowsDisplay = chunkArray(products,itemsInRow).map((ac,index) => {
                const rowKey = ( productBrowseState.cat_id ? productBrowseState.cat_id : 0 ) + productBrowseState.page + index;
                if (!Number.isNaN(rowKey)){
                    return (
                        <ProductBrowseItemsRow
                            key={rowKey} 
                            rowIndex={index}
                            products={ac}
                            itemWidth={itemWidth}
                            imgHeight={imgHeight}
                            browseListType={browseListType}
                            {...props}
                        />
                    )
                }
            })
    }

    if (browseListType === "picture"){
        return (
            <div className="container-wide">
                <div className="picture-grid">
                    {productsRowsDisplay}
                </div>
            </div>
        )
    } else {
        return (
            <React.Fragment>
                {productsRowsDisplay}
            </React.Fragment>
        )
    }
}

function ProductBrowseItemsRow(props){

    const { productBrowseState } = useContext(Context);

    const productsDisplay = props.products.map((p,index) => (
        <ProductBrowseItem
            showDescription={productBrowseState.cat_id === 0 ? false : productBrowseState.cat_showDescription}
            key={index + props.rowIndex} 
            index={index}
            rowIndex={props.rowIndex}
            product={p}
            itemWidth={props.itemWidth}
            imgHeight={props.imgHeight}
            currentPage={productBrowseState.page}
            itemsPerPage={productBrowseState.pageLimit}
            browseListType={productBrowseData.browseListType}
            {...props}
        />
    ));

    /*if (props.products.length > 1){
        return (
            <div className={"product-browse-item-row " + ( props.browseListType ? props.browseListType + "-row" : "")}>
                {productsDisplay}
            </div>
        )
    } else {*/
        return (
            <React.Fragment>
                {productsDisplay}
            </React.Fragment>
        )
    //}
}

function ProductBrowsePagination(props){
    
    const { productBrowseState, productBrowseDispatch } = useContext(Context);

    let pagination = [];
    if (productBrowseState.products){
        if (parseInt(productBrowseData.totalcount) > productBrowseData.pageLimit){
            pagination = {
                totalcount:parseInt(productBrowseData.totalcount),
                page:productBrowseData.page,
                pagelimit:productBrowseData.pageLimit
            }
        }
    } else pagination = window.pagination;

    const filters = productBrowseState.filters;

    const [ totalItems, setTotalItems ] = useState(pagination.totalcount);
    const [ itemsPerPage, setItemsPerPage ] = useState(productBrowseData.pageLimit);
    const [ currentPage, setCurrentPage ] = useState(pagination.page);
    const [ totalPages, setTotalPages ] = useState(Math.ceil(totalItems / itemsPerPage));

    const minPage = currentPage - 5 > 0 ? currentPage - 5 : 0;
    const maxPage = minPage + 10 < totalPages ? minPage + 10 : totalPages;

    let paginationArray = [];
    for (var i = minPage; i < maxPage; i++){ paginationArray.push(i + 1); }
    
    let pageLinkBase = json_serverUrl;
    pageLinkBase += window.is_show_real_domain_as_url === 1 ? "/" : "/s/" + json_store_name + "/";
    //pageLinkBase += "browse/page/";
    pageLinkBase += "browse?";
    let pageLinkSuffix = ""; 
    let pageLinkSuffixOrd = "";
    if (filters){
        if (typeof filters.category === 'number') pageLinkSuffix += "&cat=" + filters.category;
        pageLinkSuffixOrd = "&order=" + filters.order;
    }

    let previousButtonDisplay;
    if (currentPage > 1) previousButtonDisplay = <li className="previous"><a href={pageLinkBase +pageLinkSuffix+ '&page='+(currentPage - 1)+ pageLinkSuffixOrd}> Previous</a></li>

    let nextButtonDisplay;
    if (currentPage < totalPages) nextButtonDisplay = <li><a href={pageLinkBase +pageLinkSuffix+'&page='+ (currentPage + 1)+pageLinkSuffixOrd}>Next</a></li>

    const paginationDisplay = paginationArray.map((p,index) => {
        let pageLinkDisplay;
        const pageLink = pageLinkBase  + pageLinkSuffix + '&page='+ p+pageLinkSuffixOrd + (productBrowseState.isBrowseFavorite === true ? "&fav=1" : "");
        if (currentPage === p) pageLinkDisplay = <li className="active" key={index}><a>{p}</a></li>
        else pageLinkDisplay = <li key={index}><a href={pageLink}>{p}</a></li>
        return <React.Fragment>{pageLinkDisplay}</React.Fragment>
    });

    return (
        <div className={"browse-pagination pagination-container"}>
            <ul className="pagination">
                {previousButtonDisplay}
                {paginationDisplay}
                {nextButtonDisplay}
            </ul>
        </div>
    )
}

export default ProductBrowse;