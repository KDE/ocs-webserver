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

    React.useEffect(() => {
        initGallery()
    },[])

    function initGallery(){
        let imgBaseUrl = "https://cn.";
        imgBaseUrl += window.location.host.endsWith('cc') === true ? "pling.cc" : "opendesktop.org";
    
        let photos = []
        products.forEach(function(p,index){
            const imgUrl = imgBaseUrl + "/img/" + p.image_small;
            const img = new Image();
            img.addEventListener("load", function(){
                photos.push({
                    key:{index},
                    src:imgUrl,
                    thumbnail:imgUrl,
                    thumbnailWidth:this.naturalWidth,
                    thumbnailHeight:this.naturalHeight
                })
                if ((index + 1) === products.length) setGallery(photos);
            });
            img.src = imgUrl;
        })
    }

    let galleryDisplay;
    if (gallery) {
        galleryDisplay = (
            <Gallery 
                images={gallery} 
                enableImageSelection={false}
                rowHeight={250}
            />
        )
    }

    return (
        <div id="product-browse-item-list">
            {galleryDisplay}
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