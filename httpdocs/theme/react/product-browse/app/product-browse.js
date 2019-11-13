import React, { useState } from 'react';
import ReactDOM from 'react-dom';
import {isMobile} from 'react-device-detect';
import {ProductBrowseItem} from './product-browse-item';
import {getNumberOfItemsPerRow, getImageHeight, chunkArray, getItemWidth} from './product-browse-helpers';

function ProductBrowse(){
    
    return (
        <div id="product-browse">
            <ProductBrowseFilterContainer/>
            <ProductBrowseItemList />
        </div>
    )
}

function ProductBrowseFilterContainer(){

    let filtersBaseUrl = json_serverUrl;
    filtersBaseUrl += json_store_name === "ALL" ? "/" : "/s/" + json_store_name + "/";
    filtersBaseUrl += "browse/";
    if (typeof filters.category === 'number') filtersBaseUrl += "cat/" + filters.category + "/";

    function onOriginalCheckboxClick(){
        let val = filters.original !== null ? 0 : 1;
        window.location.href = filtersBaseUrl + "filteroriginal/" + val;
    }

    return (
        <div id="product-browse-top-menu">
            <div className="pling-nav-tabs">
                <ul className="nav nav-tabs pling-nav-tabs" id="sort">
                    <li className={filters.order === "latest" ? "active" : ""}>
                        <a href={filtersBaseUrl + "ord/latest/" + window.location.search}>Latest</a>
                    </li>
                    <li className={filters.order === "rating" ? "active" : ""}>
                        <a href={filtersBaseUrl + "ord/rating/" + window.location.search}>Score</a>
                    </li>
                    <li className={filters.order === "plinged" ? "active" : ""}>
                        <a href={filtersBaseUrl + "ord/plinged/" + window.location.search}>Plinged</a>
                    </li>
                    <li style={{"float":"right","paddingTop":"10px"}}>
                        <input onChange={onOriginalCheckboxClick} defaultChecked={filters.original} type="checkbox"/>
                        <label>Original</label>
                    </li>
                </ul>
            </div>
        </div>
    )
}

function ProductBrowseItemList(props){


    if (window.location.search === "?index=7") {
        window.browseListType === "favorites";
        browseListType = "favorites";
    }

    const [ containerWidth, setContainerWidth ] = useState($('#product-browse-container').width() + 14);
    const [ itemsInRow, setItemsInRow ] = useState(getNumberOfItemsPerRow(browseListType,isMobile,containerWidth));
    const [ itemWidth, setItemWidth ] = useState(getItemWidth(browseListType,containerWidth,itemsInRow));
    const [ imgHeight, setImgHeight ] = useState(getImageHeight(browseListType,itemWidth));

    
    React.useEffect(() => {
        window.addEventListener("resize", function(event){ updateDimensions() });
        window.addEventListener("orientationchange",  function(event){ updateDimensions() });
    },[])

    function updateDimensions(){
        const newContainerWidth = $('#product-browse-container').width() + 14;
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
        productsRowsDisplay = chunkArray(products,itemsInRow).map((ac,index) => (
            <ProductBrowseItemsRow
                key={index} 
                rowIndex={index}
                products={ac}
                itemWidth={itemWidth}
                imgHeight={imgHeight}
            />
        ))
    }

    return (
        <div id="product-browse-item-list" className={isMobile ? "mobile" : ""}>
            <div id="product-browse-list-container">
                {productsRowsDisplay}
                <ProductBrowsePagination/>
            </div>
        </div>
    )
}

function ProductBrowseItemsRow(props){

    const productsDisplay = props.products.map((p,index) => (
        <ProductBrowseItem
            key={index} 
            index={index}
            rowIndex={props.rowIndex}
            product={p}
            itemWidth={props.itemWidth}
            imgHeight={props.imgHeight}
        />
    ));

    return (
        <div className={"product-browse-item-row " + ( browseListType ? browseListType + "-row" : "")}>
            {productsDisplay}
        </div>
    )
}

function ProductBrowsePagination(){
    
    const [ totalItems, setTotalItems ] = useState(pagination.totalcount);
    const [ itemsPerPage, setItemsPerPage ] = useState(50);
    const [ currentPage, setCurrentPage ] = useState(pagination.page);
    const [ totalPages, setTotalPages ] = useState(Math.ceil(totalItems / itemsPerPage));

    const minPage = currentPage - 5 > 0 ? currentPage - 5 : 0;
    const maxPage = minPage + 10 < totalPages ? minPage + 10 : totalPages;

    let paginationArray = [];
    for (var i = minPage; i < maxPage; i++){ paginationArray.push(i + 1); }
    
    let pageLinkBase = json_serverUrl;
    pageLinkBase += json_store_name === "ALL" ? "/" : "/s/" + json_store_name + "/";
    pageLinkBase += "browse/page/";

    let pageLinkSuffix = "/" 
    if (typeof filters.category === 'number') pageLinkSuffix += "cat/" + filters.category + "/";
    pageLinkSuffix += "ord/" + filters.order + "/";
    if (filters.original !== null) pageLinkSuffix += "filteroriginal/" + filters.original + window.location.search;

    let previousButtonDisplay;
    if (currentPage > 1) previousButtonDisplay = <li><a href={pageLinkBase + (currentPage - 1) + pageLinkSuffix}><span className="glyphicon glyphicon-chevron-left"></span> Previous</a></li>

    let nextButtonDisplay;
    if (currentPage < totalPages) nextButtonDisplay = <li><a href={pageLinkBase + (currentPage + 1) + pageLinkSuffix}>Next <span className="glyphicon glyphicon-chevron-right"></span></a></li>

    const paginationDisplay = paginationArray.map((p,index) => {
        let pageLinkDisplay;
        if (currentPage === p) pageLinkDisplay = <span className="no-link">{p}</span>
        else pageLinkDisplay = <a href={pageLinkBase + p + pageLinkSuffix}>{p}</a>
        return (                
            <li key={index}>
                {pageLinkDisplay}
            </li>
        )
    });

    return (
        <div id="product-browse-pagination">
            <ul>
                {previousButtonDisplay}
                {paginationDisplay}
                {nextButtonDisplay}
            </ul>
        </div>
    )
}

const rootElement = document.getElementById("product-browse-container");
ReactDOM.render(<ProductBrowse />, rootElement);