import React, { useState } from 'react';
import ReactDOM from 'react-dom';
import Gallery from 'react-grid-gallery';
import {isMobile} from 'react-device-detect';

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

    return (
        <div id="product-browse-top-menu">
            <div className="pling-nav-tabs">

            </div>
            <ul className="nav nav-tabs pling-nav-tabs" id="sort">
                <li><a>Latest</a></li>
                <li><a>Score</a></li>
                <li><a>Plinged</a></li>
                <li>
                    <input type="checkbox"/>
                    <label>Original</label>
                </li>
            </ul>
        </div>
    )
}

function ProductBrowseItemList(){
    
    const [ gallery, setGallery ] = useState();

    console.log(gallery);

    React.useEffect(() => {
        initGallery()
    },[])

    function initGallery(){

        const rowHeight = 250;
        const containerWidth = $('#product-browse-container').width();
        let imgBaseUrl = "https://cn.";
        imgBaseUrl += window.location.host.endsWith('cc') === true ? "pling.cc" : "opendesktop.org";
        let productsGallery = []
        let rowNumber = 0;
        let rowWidth = 0;

        function sortByCurrentFilter(a,b){
            const aDate = typeof a.changed_at !== undefined ? a.changed_at : a.created_at
            const aCreatedAt = new Date(aDate);
            // const aTimeStamp = aCreatedAt.getTime();
            const bDate = typeof b.changed_at !== undefined ? b.changed_at : b.created_at
            const bCreatedAt = new Date(bDate);
            // const bTimeStamp = bCreatedAt.getTime();
            return aCreatedAt - bCreatedAt;
        }

        const sortedProducts = products.sort(sortByCurrentFilter);
        console.log(sortedProducts);

        sortedProducts.forEach(function(p,index){
            
            const imgUrl = imgBaseUrl + "/img/" + p.image_small;
            const img = new Image();

            img.addEventListener("load", function(){

                const decrease = this.naturalHeight - rowHeight;
                const decreasePercentage = rowHeight / this.naturalHeight;
                let adjustedWidth = this.naturalWidth * decreasePercentage;
                if (adjustedWidth > this.naturalWidth) adjustedWidth = this.naturalWidth;
                const newRowWidth = rowWidth + adjustedWidth;

                if (newRowWidth > containerWidth){
                    rowNumber += 1;
                    rowWidth = adjustedWidth;
                } else rowWidth = newRowWidth;

                if (!productsGallery[rowNumber]) productsGallery[rowNumber] = []
                productsGallery[rowNumber].push({
                    src:imgUrl,
                    width:adjustedWidth,
                    height:rowHeight,
                    row:rowNumber,
                    ...p
                })

                if ((index + 1) === products.length) setGallery(productsGallery);
            });

            img.src = imgUrl;
        
        })
    }

    let productRowsDisplay;
    if (gallery){
        productRowsDisplay = gallery.map((pr,index) => (
            <ProductBrowseItemListRow 
                key={index}
                rowNumber={index}
                products={pr}
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
    const productsDisplay = props.products.map((p,index) => (
        <ProductBrowseItem 
            key={index}
            product={p}
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

    const productBrowseItemStyle = {
        height:p.height,
        width:p.width
    }
    
    return (
        <div className="product-browse-item-wrapper" style={productBrowseItemStyle}>
            <div className="product-browse-item" id={"product-" + p.project_id}>
                <img src={p.src} />
                <div className="product-browse-item-info">
                    <div className="product-browse-item-info-content">
                        <div>
                            <h2><a href={window.config.baseUrl + "/" + p.type_id === "3" ? "c" : "p" + "/" + p.project_id}>{p.title}</a></h2>
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