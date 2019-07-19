import React, { useState } from 'react';
import ReactDOM from 'react-dom';
import {SortByCurrentFilter} from './product-browse-helpers';

function ProductBrowse(){

    const [ itemBackgroundSize, setItemBackgroundSize ] = useState('contain');

    console.log(filters);
    console.log(window.location);

    const divStyle = {
        textAlign: "right",
        margin: "20px 0 0 0",
        fontSize: "15px"
    }

    return (
        <div id="product-browse">
            <ProductBrowseFilterContainer/>
            <div style={divStyle}>
                <span>set background size: </span> 
                <a onClick={() => setItemBackgroundSize('cover')}>bg cover</a> | <a onClick={() => setItemBackgroundSize('contain')}>bg contain</a>
            </div>
            <ProductBrowseItemList 
                itemBackgroundSize={itemBackgroundSize}
            />
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
    
    const [ loading, setLoading ] = useState(true)

    const [ gallery, setGallery ] = useState();
    const [ containerWidth, setContainerWidth ] = useState( $('#product-browse-container').width());
    const [ rowHeight, setRowHeight ] = useState(250);

    let InitialImgBaseUrl = "https://cn.";
    InitialImgBaseUrl += window.location.host.endsWith('cc') === true ? "pling.cc" : "opendesktop.org";
    const [ imgBaseUrl, setImageBaseUrl ] = useState(InitialImgBaseUrl)

    React.useEffect(() => { 
        window.addEventListener("resize", function(event){initGallery()});
        window.addEventListener("orientationchange",  function(event){initGallery()});    
        initGallery() 
    },[])
    

    function initGallery(){

        setLoading(true);

        const sortedProducts = products.sort(SortByCurrentFilter);
        let productsGallery = [], rowNumber = 0,rowWidth = 0, imgLoadIndex = 0;

        sortedProducts.forEach(function(p,index){
            
            const imgUrl = imgBaseUrl + "/img/" + p.image_small;
            const img = new Image();

            img.addEventListener("load", function(){
                
                const decreasePercentage = rowHeight / this.naturalHeight;
                let adjustedWidth = this.naturalWidth * decreasePercentage;
                if (adjustedWidth > this.naturalWidth) adjustedWidth = this.naturalWidth;
                if (typeof adjustedWidth === undefined) adjustedWidth = 250;
                
                const newRowWidth = rowWidth + adjustedWidth;
                if (newRowWidth > containerWidth){
                    rowNumber += 1;
                    rowWidth = adjustedWidth;
                } else rowWidth = newRowWidth;

                if (!productsGallery[rowNumber]) productsGallery[rowNumber] = {products:[],rowWidth:rowWidth}
                productsGallery[rowNumber].products.push({
                    src:imgUrl,
                    width:adjustedWidth,
                    height:rowHeight,
                    row:rowNumber,
                    ...p
                })
                productsGallery[rowNumber].rowWidth = rowWidth;
                imgLoadIndex += 1;
                if ((imgLoadIndex + 1) === sortedProducts.length) finishLoadingProducts(productsGallery);
            });

            img.addEventListener("error", function(){
                imgLoadIndex += 1;
                if ((imgLoadIndex + 1) === sortedProducts.length) finishLoadingProducts(productsGallery);              
            });
            
            img.src = imgUrl;
        
        })
    }

    function finishLoadingProducts(productsGallery){
        setLoading(false)
        setGallery(productsGallery);
    }

    let productRowsDisplay;
    if (loading){
        productRowsDisplay = <span>Loading...</span>
    } else {
        if (gallery){
            productRowsDisplay = gallery.map((pr,index) => (
                <ProductBrowseItemListRow 
                    key={index}
                    rowNumber={index}
                    rowWidth={pr.rowWidth}
                    containerWidth={containerWidth}
                    products={pr.products}
                    itemBackgroundSize={props.itemBackgroundSize}
                />
            ))
        }
    }

    return (
        <div id="product-browse-item-list">
            {productRowsDisplay}
        </div>
    )
}

function ProductBrowseItemListRow(props){
    const percentageIncrease = props.containerWidth / props.rowWidth;
    const sortedRowProducts = props.products.sort(SortByCurrentFilter);
    const productsDisplay = sortedRowProducts.map((p,index) => (
        <ProductBrowseItem 
            key={index}
            product={p}
            percentageIncrease={percentageIncrease}
            itemBackgroundSize={props.itemBackgroundSize}
        />
    ))
    return (
        <div className="product-browse-item-list-row">
            {productsDisplay}
        </div>
    )
}

function ProductBrowseItem(props){
    
    const p = props.product;
    const productBrowseItemContainerStyle = { height:p.height, width:p.width * props.percentageIncrease }
    const productBrowseItemStyle = { 
        backgroundImage:'url('+p.src+')',
        backgroundSize:props.itemBackgroundSize 
    }

    let itemLink = window.config.baseUrl + "/";
    itemLink += p.type_id === "3" ? "c" : "p";
    itemLink += "/" + p.project_id;
    
    return (
        <div className="product-browse-item-wrapper" style={productBrowseItemContainerStyle}>
            <div className="product-browse-item" id={"product-" + p.project_id} style={productBrowseItemStyle}>
                <a href={itemLink} className="product-browse-item-info">
                    <div className="product-browse-item-info-content">
                        <div>
                            <h2>{p.title}</h2>
                            <span>{p.cat_title}</span>
                            <span>by <a>{p.username}</a></span>
                            <span>score {p.laplace_score}</span>
                            <span>{p.created_at}</span>
                        </div>
                    </div>
                </a>
            </div>
        </div>
    )
}

function ProductBrowsePagination(){

    const [ totalItems, setTotalItems ] = useState(pagination.totalcount);
    const [ itemsPerPage, setItemsPerPage ] = useState(50);
    const [ currentPage, setCurrentPage ] = useState(pagination.page);
    const [ totalPages, setTotalPages ] = useState(Math.ceil(totalItems / itemsPerPage));

    let paginationArray = [];
    for (var i = 0; i < totalPages; i++){ paginationArray.push(i + 1); }
    
    let pageLinkBase = window.config.baseUrl + "/browse/page/";
    if (typeof filters.category === Number) pageLinkBase += "cat/" + filters.category + "/";
    let pageLinkSuffix = "/ord/" + filters.order;
    if (filters.original !== null) pageLinkSuffix += "/filteroriginal/" + filters.original + window.location.search;

    const paginationDisplay = paginationArray.map((p,index) => (
        <li key={index}>
            <a href={pageLinkBase + p + pageLinkSuffix}>{p}</a>
        </li>
    ))

    let previousButtonDisplay;
    if (currentPage > 1){
        previousButtonDisplay = (
            <li>
                <a href={pageLinkBase + (currentPage - 1) + pageLinkSuffix}><span className="glyphicon glyphicon-chevron-left"></span> Previous</a>
            </li>
        )
    }

    let nextButtonDisplay;
    if (currentPage < totalPages){
        nextButtonDisplay = (
            <li>
                <a href={pageLinkBase + (currentPage + 1) + pageLinkSuffix}>Next <span className="glyphicon glyphicon-chevron-right"></span></a>
            </li>
        )        
    }

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