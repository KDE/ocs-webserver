import React, { useState } from 'react';
import ReactDOM from 'react-dom';
import {isMobile} from 'react-device-detect';

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
        </div>
    )
}

function ProductBrowseItemList(){
    
    React.useEffect(() => {
        console.log('product browse item list')
        console.log(products);
        console.log(topProducts);
        console.log(pagination);
    },[])


    const productsDisplay = products.map((p,index) => (
        <ProductBrowseItem
            key={index}
            product={p}
            index={index}
        />
    ))

    return (
        <div id="product-browse-item-list">
            {productsDisplay}
        </div>
    )
}

function ProductBrowseItem(props){

    React.useEffect(() => {
        console.log(props.product);
    },[])

    const p = props.product;

    return (
        <div className="product-browse-item" id={"product-"+p.project_id}>
            <img src={"https://cn.pling.cc/cache/167x167-0/img/"+p.image_small}/>
            <h2><a href={"https://"+window.location.host+"/" + p.type_id === "3" ? "c" : "p" + "/" + p.project_id}>{p.title}</a></h2>
            <span>{p.cat_title}</span>
            <span>by <a href={"https://"+window.location.host+"/u/" + p.member_id}>{p.username}</a></span>
            <span>score {p.laplace_score}</span>
            <span>{p.created_at}</span>
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