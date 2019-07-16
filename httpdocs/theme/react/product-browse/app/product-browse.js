import React, { useState } from 'react';
import ReactDOM from 'react-dom';
import {isMobile} from 'react-device-detect';

function ProductBrowse(){

    React.useEffect(() => {
        console.log(filters);
        console.log(products);
        console.log(topProducts);
        console.log(pagination);
        console.log(catId);
    },[])

    return (
        <div id="product-browse">

        </div>
    )
}

const rootElement = document.getElementById("product-browse-container");
ReactDOM.render(<ProductBrowse />, rootElement);