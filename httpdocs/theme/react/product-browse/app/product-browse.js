import React, { useState } from 'react';
import ReactDOM from 'react-dom';
import {SortByCurrentFilter} from './product-browse-helpers';

function ProductBrowse(){
    return (
        <div id="product-browse">
            <ProductBrowseFilterContainer/>
            <ProductBrowseItemList />
            <ProductBrowsePagination/>
        </div>
    )
}

function ProductBrowseFilterContainer(){

    let filtersBaseUrl = window.config.baseUrl + "/browse/";
    if (typeof filters.category === Number) filtersBaseUrl += "cat/" + filters.category + "/";

    function onOriginalCheckboxClick(){
        let val = filters.original !== null ? 0 : 1;
        window.location.href = window.config.baseUrl + "/" + window.location.pathname + "filteroriginal/" + val;
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
    
    const productsDisplay = products.sort(SortByCurrentFilter).map((p,index) => (
        <ProductBrowseItem
            key={index} 
            product={p}
        />
    ))

    return (
        <div id="product-browse-item-list">
            {productsDisplay}
        </div>
    )
}

function ProductBrowseItem(props){
    
    const p = props.product;

    const containerWidth = $('#product-browse-container').width();
    const itemWidth = containerWidth / 3;
    const imgHeight = itemWidth / 1.85;


    let imgUrl = "https://cn.opendesktop.";
    imgUrl += window.location.host.endsWith('org') === true || window.location.host.endsWith('com') === true  ? "org" : "cc";
    imgUrl += "/img/" + p.image_small;

    let itemLink = window.config.baseUrl + "/";
    itemLink += p.type_id === "3" ? "c" : "p";
    itemLink += "/" + p.project_id;
    
    return (
        <div className="product-browse-item" id={"product-" + p.project_id} style={{"width":itemWidth}}>
            <a href={itemLink} className="product-browse-item-wrapper">
                <div className="product-browse-image" style={{"height":imgHeight}}>
                    <img src={imgUrl}/>
                </div>
                <div className="product-browse-item-info">
                    <h2>{p.title}</h2>
                    <span>{p.cat_title}</span>
                    <span>by <a>{p.username}</a></span>
                </div>
            </a>
        </div>
    )
}

function ProductBrowsePagination(){

    const [ totalItems, setTotalItems ] = useState(pagination.totalcount);
    const [ itemsPerPage, setItemsPerPage ] = useState(50);
    const [ currentPage, setCurrentPage ] = useState(pagination.page);
    const [ totalPages, setTotalPages ] = useState(Math.ceil(totalItems / itemsPerPage));

    console.log(currentPage)
    let minPage = 0, maxPage = 10;
    if (currentPage > 5){
        minPage = currentPage - 5;
        maxPage = currentPage + 5;
    }

    let paginationArray = [];
    for (var i = minPage; i < maxPage; i++){ paginationArray.push(i + 1); }
    
    let pageLinkBase = window.config.baseUrl + "/browse/page/";
    if (typeof filters.category === Number) pageLinkBase += "cat/" + filters.category + "/";
    let pageLinkSuffix = "/ord/" + filters.order;
    if (filters.original !== null) pageLinkSuffix += "/filteroriginal/" + filters.original + window.location.search;

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