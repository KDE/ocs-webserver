import React, { useState } from 'react';
import ReactDOM from 'react-dom';
import {isMobile} from 'react-device-detect';

function ProductBrowse(){

    React.useEffect(() => {
        console.log(window.filters);
        console.log(window.products);
        console.log(window.topProducts);
        console.log(window.pagination);
        console.log(window.baseUrl);
        console.log(window.catId);
    },[])

    return (
        <div id="product-browse">

        </div>
    )
}

const rootElement = document.getElementById("product-browse-container");
ReactDOM.render(<ProductBrowse />, rootElement);