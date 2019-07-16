import React, { useState } from 'react';
import ReactDOM from 'react-dom';
import {isMobile} from 'react-device-detect';

function ProductBrowse(){

    React.useEffect(() => {
        console.log('product browse');
    },[])

    return (
        <div id="product-browse">

        </div>
    )
}