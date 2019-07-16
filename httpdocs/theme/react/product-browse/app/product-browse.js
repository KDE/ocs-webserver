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


    return (
        <div className="product-browse-item"></div>
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