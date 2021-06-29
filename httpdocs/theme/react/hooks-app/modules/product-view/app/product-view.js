import React, { useContext, useEffect } from 'react';
import { Context } from './context/context-provider';
import ProductHeader from './product-view-header';
import ProductTabs from './product-view-tabs';
import ProductMediaSlider from '../../product-media-slider/app/product-media-slider';

import '../style/product-page.css';
import '../style/product-view.css';

function ProductView(props){

    const { productViewState, productViewDispatch } = useContext(Context);

    useEffect(() => {
        if (productViewState.product !== null){
            $.ajax({url:'/p/'+productViewState.product.project_id+'/loadFiles'}).done(function(res){
                productViewDispatch({type:'SET_FILES',files:res});
            });
        }
    },[productViewState.product])

    let productViewDisplay;
    if (productViewState.loading === false){
        let productMediaSliderDisplay;
        if (productViewState.isCollectionView === false){
            productMediaSliderDisplay = (
                <div id="product-media-slider-container" className="imgsmall">
                    <ProductMediaSlider 
                        product={productViewState.product} 
                        filesJson={productViewState.filesTab}
                        galleryPics={productViewState.pics}
                        {...props}
                    />
                </div>
            )
        } else {
            productMediaSliderDisplay = (
                <div style={{paddingLeft: "25px"}}>
                    <article>
                        <b>Description:</b>
                        <br/><br/>
                        <div dangerouslySetInnerHTML={{__html: productViewState.product.description}}></div>
                    </article>
                </div>
            )
        }

        productViewDisplay = (
            <React.Fragment>
                <div id="product-main-image-container">
                    <div id="product-main-img">
                        <ProductHeader
                            {...props}
                        />
                        {productMediaSliderDisplay}
                    </div>
                </div>
                <hr className="m0"/>
                <ProductTabs
                    onChangeUrl={props.onChangeUrl}
                />
            </React.Fragment>
        )
    }

    return (
        <React.Fragment>
            {productViewDisplay}
        </React.Fragment>
    )
}

export default ProductView;