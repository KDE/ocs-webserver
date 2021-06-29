import '../style/dummy-product-view.css';

function DummyProductView(){
    return (
        <div className="flex-row dummy-product-view imgsmall" id="product-main-img-container">
            <div className="product-main-img flex-item-24" id="product-main-img">
            <div id="product-title-div" className="product-title-div">

            <div className="product-title">
                <div className="product-logo-container">
                    <figure className="logo dummy-fill dummy-fill-to-white">
                    </figure>
                </div>
                <div id="product-header-info-container">
                    <div id="product-header-title">
                        <div className="dummy-fill dummy-fill-to-white"></div>
                    </div>
                    <div className="product_category">
                        <div className="dummy-fill dummy-fill-to-white"></div>
                        <div className="dummy-fill dummy-fill-to-white"></div>
                    </div>
                    <p className="source-container light small">
                        <span className="dummy-fill dummy-fill-to-white"></span>
                    </p>
                </div>
            </div>
                <div className="product-title-right">
                    <div className="product-title-right-wrapper">
                        <div className="projectdtailHeart tooltipheart tooltipstered product-heart-button-container">
                            <div id="container-follow" className="container-pling">
                                <div className="heart-button">
                                    <i className="plingheart fa heartgrey fa-heart-o" aria-hidden="true"></i>
                                </div>
                            </div>
                        </div>
                        <div style={{width:"52px",height:"52px",borderRadius:"100%",margin:"65px auto 0"}} className="dummy-fill dummy-fill-to-white circle"></div>
                    </div>
                </div>
            </div>

                <div id="product-media-slider-container" className="imgsmall">
                    <div className="dummy-fill dummy-fill-to-white"></div>
                </div>
            </div>
        </div>
    )
}

export default DummyProductView;