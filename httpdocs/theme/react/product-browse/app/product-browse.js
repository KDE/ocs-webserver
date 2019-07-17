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
    return (
        <div id="product-browse-top-menu">
            <ul>
                <li><a>Latest</a></li>
                <li><a>Score</a></li>
            </ul>
            <span>
                <label>Original</label>
                <input type="checkbox"/>
            </span>
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
        products.forEach(function(p,index){
            const imgUrl = imgBaseUrl + "/img/" + p.image_small;
            const img = new Image();
            img.addEventListener("load", function(){
                // find the percentage decrease of the naturalHeight / 250px and decrease the width by that
                const decrease = this.naturalHeight - rowHeight;
                const decreasePercentage = rowHeight / this.naturalHeight;
                const adjustedWidth = this.naturalWidth * decreasePercentage;
                const newRowWidth = rowWidth + adjustedWidth;

                if (newRowWidth > containerWidth){
                    rowNumber += 1;
                    rowWidth = adjustedWidth;
                } else {
                    rowWidth = newRowWidth;
                }
                // add adjusted width to totalRowWidth, if below zero
                if (!productsGallery[rowNumber]) productsGallery[rowNumber] = []
                productsGallery[rowNumber].push({
                    src:imgUrl,
                    width:adjustedWidth,
                    height:rowHeight,
                    row:rowNumber,
                    product:p
                })
                if ((index + 1) === products.length) setGallery(productsGallery);
            });
            img.src = imgUrl;
        })
    }

    return (
        <div id="product-browse-item-list">
        </div>
    )
}

function ProductBrowseItem(props){

    const p = props.product;
    let imgBaseUrl = "https://cn.";
    imgBaseUrl += window.location.host.endsWith('cc') === true ? "pling.cc" : "opendesktop.org";
    return (
        <div className="product-browse-item" id={"product-" + p.project_id}>
            <img src={imgBaseUrl + "/img/" + p.image_small}/>
            <div className="product-browse-item-info" style={{"display":"none"}}>
                <h2><a href={window.config.baseUrl + "/" + p.type_id === "3" ? "c" : "p" + "/" + p.project_id}>{p.title}</a></h2>
                <span>{p.cat_title}</span>
                <span>by <a href={window.config.baseUrl + "/u/" + p.member_id}>{p.username}</a></span>
                <span>score {p.laplace_score}</span>
                <span>{p.created_at}</span>
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