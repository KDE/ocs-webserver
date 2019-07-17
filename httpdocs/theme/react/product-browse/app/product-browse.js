import React, { useState } from 'react';
import ReactDOM from 'react-dom';
import Masonry from 'react-masonry-component';
import {isMobile} from 'react-device-detect';

console.log(window.config);
console.log(window.location);

function ProductBrowse(){

    React.useEffect(() => {
        console.log('product browse')
        console.log(catId);
    },[])

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
        console.log('product browse filter container');
        console.log(filters);
    },[])

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
    
    React.useEffect(() => {
        console.log('product browse item list - with masonry')
    },[])


    const productsDisplay = products.map((p,index) => (
        <ProductBrowseItem
            key={index}
            product={p}
            index={index}
        />
    ))

    const masonryOptions = { };

    return (
        <div id="product-browse-item-list">
            <Masonry
                className={'masonry-gallery-container'} // default ''
                options={masonryOptions} // default {}
                disableImagesLoaded={false} // default false
                updateOnEachImageLoad={false} // default false and works only if disableImagesLoaded is false
            >
                {productsDisplay}
            </Masonry>
        </div>
    )
}

function ProductBrowseItem(props){
    
    const p = props.product;
    let imgBaseUrl = "https://cn.";
    imgBaseUrl += window.location.host.endsWith('cc') === true ? "pling.cc" : "opendesktop.org";
    console.log(imgBaseUrl);
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

    React.useEffect(() => {
        console.log('product browse pagination')
        console.log(pagination);
    },[])

    return (
        <div id="product-browse-pagination"></div>
    )
}

const rootElement = document.getElementById("product-browse-container");
ReactDOM.render(<ProductBrowse />, rootElement);