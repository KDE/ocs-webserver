import React, { useState } from 'react';
import ReactDOM from 'react-dom';
import {SortByCurrentFilter} from './product-browse-helpers';
console.log(window.config);
console.log(window.location);

function ProductBrowse(){
    return (
        <div id="product-browse">
            <ProductBrowseFilterContainer/>
            <ProductBrowseItemList/>
            <ProductBrowsePagination/>
        </div>
    )
}

function ProductBrowseFilterContainer(){

    React.useEffect(() => {
        console.log(filters);
    },[])

    let filtersBaseUrl = window.config.baseUrl + "/browse/";
    if (typeof filters.category === Number) filtersBaseUrl += "cat/" + filters.category + "/";

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
                        <input type="checkbox"/>
                        <label>Original</label>
                    </li>
                </ul>
            </div>
        </div>
    )
}

function ProductBrowseItemList(){
    
    const [ gallery, setGallery ] = useState();
    const [ containerWidth, setContainerWidth ] = useState( $('#product-browse-container').width());
    const [ rowHeight, setRowHeight ] = useState(250);

    let InitialImgBaseUrl = "https://cn.";
    InitialImgBaseUrl += window.location.host.endsWith('cc') === true ? "pling.cc" : "opendesktop.org";
    const [ imgBaseUrl, setImageBaseUrl ] = useState(InitialImgBaseUrl)

    React.useEffect(() => { initGallery() },[])

    function initGallery(){

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
                if ((imgLoadIndex + 1) === sortedProducts.length) setGallery(productsGallery);
            
            });

            img.src = imgUrl;
        
        })
    }

    console.log(gallery);

    let productRowsDisplay;
    if (gallery){
        productRowsDisplay = gallery.map((pr,index) => (
            <ProductBrowseItemListRow 
                key={index}
                rowNumber={index}
                rowWidth={pr.rowWidth}
                containerWidth={containerWidth}
                products={pr.products}
            />
        ))
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
    const productBrowseItemStyle = { backgroundImage:'url('+p.src+')' }

    let itemLink = window.config.baseUrl + "/";
    itemLink += p.type_id === "3" ? "c" : "p";
    itemLink += "/" + p.project_id;
    
    return (
        <div className="product-browse-item-wrapper" style={productBrowseItemContainerStyle}>
            <div className="product-browse-item" id={"product-" + p.project_id} style={productBrowseItemStyle}>
                <div className="product-browse-item-info">
                    <div className="product-browse-item-info-content">
                        <div>
                            <h2><a href={itemLink}>{p.title}</a></h2>
                            <span>{p.cat_title}</span>
                            <span>by <a href={window.config.baseUrl + "/u/" + p.member_id}>{p.username}</a></span>
                            <span>score {p.laplace_score}</span>
                            <span>{p.created_at}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    )
}

function ProductBrowsePagination(){
    return (
        <div id="product-browse-pagination"></div>
    )
}

const rootElement = document.getElementById("product-browse-container");
ReactDOM.render(<ProductBrowse />, rootElement);